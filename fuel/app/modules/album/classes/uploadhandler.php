<?php
namespace Album;

class LimitUploadFileSizeException extends \FuelException {}

class UploadHandler extends \JqueryFileUpload
{
	public $is_limit_upload_file_size = false;
	public $accepted_upload_filesize = 0;
	public $member_filesize_total = 0;
	public $is_save_exif_data = false;

	public function get($album_id)
	{
		$file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
		if ($file_name)
		{
			$info = $this->get_file_object($file_name);
		}
		else
		{
			$info = $this->get_file_objects($album_id);
		}

		return json_encode($info);
	}

	protected function get_file_objects($album_id = 0)
	{
		if ($album_id)
		{
			$info = array();
			$album_images = Model_AlbumImage::find()->where('album_id', $album_id)->related('album')->related('file')->order_by('created_at')->get();
			foreach ($album_images as $album_image)
			{
				$info[] = $this->get_file_object($album_image->file->name, $album_image->id, $album_image->file->original_filename);
			}

			return $info;
		}

		return array_values(array_filter(array_map(
				array($this, 'get_file_object'),
				scandir($this->options['upload_dir'])
		)));

	}

	protected function get_file_object($file_name, $album_image_id = 0, $original_filename = '')
	{
		$file_path = $this->options['upload_dir'].$file_name;
		if (is_file($file_path) && $file_name[0] !== '.')
		{
			$file = new \stdClass();
			$file->name = ($original_filename)? $original_filename : $file_name;
			$file->size = filesize($file_path);
			$file->url = $this->options['upload_url'].rawurlencode($file_name);

			foreach($this->options['image_versions'] as $version => $options)
			{
				if (is_file($options['upload_dir'].$file_name))
				{
					$file->{$version.'_url'} = $options['upload_url'].rawurlencode($file_name);
				}
			}

			if ($album_image_id) $file->album_image_id = $album_image_id;
			$this->set_file_delete_url($file);

			return $file;
		}

		return null;
	}

	protected function set_file_delete_url($file)
	{
		$file->delete_url = $this->options['script_url'];
		if (isset($file->album_image_id)) $file->delete_url .= '?id='.rawurlencode($file->album_image_id);
		$file->delete_type = $this->options['delete_type'];
		if ($file->delete_type !== 'DELETE') {
			$file->delete_url .= '&_method=DELETE';
		}
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $album_image_id = 0, $original_filename = '', $max_size = 0)
	{
		$file = new \stdClass();
		$file->name = $this->trim_file_name($name, $type, $index);
		$file->size = intval($size);
		$file->type = $type;
		if ($this->validate($uploaded_file, $file, $error, $index))
		{
			$this->handle_form_data($file, $index);
			$file_path = $this->options['upload_dir'].$file->name;
			$append_file = !$this->options['discard_aborted_uploads'] && is_file($file_path) && $file->size > filesize($file_path);
			clearstatcache();
			if ($uploaded_file && is_uploaded_file($uploaded_file))
			{
				// multipart/formdata uploads (POST method uploads)
				if ($append_file)
				{
					file_put_contents(
						$file_path,
						fopen($uploaded_file, 'r'),
						FILE_APPEND
					);
				}
				else
				{
					move_uploaded_file($uploaded_file, $file_path);
				}
			}
			else
			{
				// Non-multipart uploads (PUT method support)
				file_put_contents(
					$file_path,
					fopen('php://input', 'r'),
					$append_file ? FILE_APPEND : 0
				);
			}
			$file_size = filesize($file_path);
			if ($file_size === $file->size)
			{
				if ($this->options['orient_image'])
				{
					$this->orient_image($file_path);
				}
				$file->url = $this->options['upload_url'].rawurlencode($file->name);

				$sizes = \Image::sizes($file_path);
				$file->size = \Site_util::check_max_size_and_resize($file_path, $max_size, $sizes->width, $sizes->height);

				foreach($this->options['image_versions'] as $version => $options)
				{
					if ($this->create_scaled_image($file->name, $options))
					{
						if ($this->options['upload_dir'] !== $options['upload_dir'])
						{
							$file->{$version.'_url'} = $options['upload_url'].rawurlencode($file->name);
						}
						else
						{
							clearstatcache();
							$file_size = filesize($file_path);
						}
					}
				}
			}
			elseif ($this->options['discard_aborted_uploads'])
			{
				unlink($file_path);
				$file->error = 'abort';
			}

			if ($original_filename) $file->name = $original_filename;

			$file->album_image_id = $album_image_id;
			$this->set_file_delete_url($file);
		}

		return $file;
	}

	public function post($album_id, $member_id, $max_size = 0)
	{
		$_method = \Input::post('_method');
		if (isset($_method) && $_method === 'DELETE')
		{
			return $this->delete();
		}
		$upload = \Input::file($this->options['param_name'], null);

		$HTTP_X_FILE_NAME = \Input::server('HTTP_X_FILE_NAME');
		$prefix = 'ai_'.$album_id;
		$info = array();
		if ($upload && is_array($upload['tmp_name']))
		{
			// param_name is an array identifier like "files[]",
			// $_FILES is a multi-dimensional array:
			foreach ($upload['tmp_name'] as $index => $value)
			{
				if (!$extention = \Util_file::check_image_type($upload['tmp_name'][$index], array('jpeg', 'jpg', 'png', 'gif'), $upload['type'][$index]))
				{
					continue;
				}
				$info[] = $this->save_file($upload, $album_id, $member_id, $extention, $prefix, $max_size, $index);
			}
		}
		elseif ($upload || isset($HTTP_X_FILE_NAME))
		{
			if (!$extention = \Util_file::check_image_type($upload['tmp_name'], array('jpeg', 'jpg', 'png', 'gif'), $upload['type']))
			{
				return;
			}
			$info[] = $this->save_file($upload, $album_id, $member_id, $extention, $prefix, $max_size);
		}

		\Model_Member::recalculate_filesize_total($member_id);

		header('Vary: Accept');
		$json = json_encode($info);
		$redirect = \Input::post(stripslashes('redirect'), null);
		if ($redirect)
		{
			\Response::redirect(sprintf($redirect, rawurlencode($json)));
			return;
		}

		return $json;
	}

	public function delete($member_id = null)
	{
		$album_image_id = (int)\Input::get('id');
		if (!$album_image = Model_AlbumImage::check_authority($album_image_id, $member_id))
		{
			throw new \HttpNotFoundException;
		}

		\DB::start_transaction();
		$deleted_filesize = Model_AlbumImage::delete_with_file($album_image_id);
		\Model_Member::add_filesize($member_id, -$deleted_filesize);

//		$album_image->file = \Model_File::find($album_image->file_id);
//		$file_name = $album_image->file->name;
//
//		\Model_Member::add_filesize($album_image->album->member_id, -$album_image->file->filesize);
//		$album_image->file->delete();
//		$album_image->delete();
//
//		if (isset($file_name)) $file_name = basename(stripslashes($file_name));
//		$file_path = $this->options['upload_dir'].$file_name;
//		$success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
//		if ($success)
//		{
//			foreach($this->options['image_versions'] as $version => $options)
//			{
//				$file = $options['upload_dir'].$file_name;
//				if (is_file($file))
//				{
//					unlink($file);
//				}
//			}
//		}

		\DB::commit_transaction();

		return json_encode($success);
	}

	protected function save_file($upload, $album_id, $member_id, $extention = '', $prefix = '', $max_size = 0, $index = null)
	{
		try
		{
			\DB::start_transaction();

			// album_image の保存
			$album_image = Model_AlbumImage::forge(array(
				'album_id' => (int)$album_id,
				'file_id'  => 0,
			));
			$album_image->save();

			// param_name is a single object identifier like "file",
			// $_FILES is a one-dimensional array:
			if (isset($index))
			{
				$original_filename = $upload['name'][$index];
				$filename = \Util_file::make_filename(\Input::server('HTTP_X_FILE_NAME', $upload['name'][$index]), $extention, $prefix);
				$filesize  = \Input::server('HTTP_X_FILE_SIZE', $upload['size'][$index]);
				$file_type = \Input::server('HTTP_X_FILE_TYPE', $upload['type'][$index]);

				// Exif データの取得
				$exif = ($this->is_save_exif_data) ? exif_read_data($upload['tmp_name'][$index]) : array();

				$this->check_filesize_per_member($filesize);
				$result = $this->handle_file_upload(
					isset($upload['tmp_name'][$index]) ? $upload['tmp_name'][$index] : null,
					$filename,
					\Input::server('HTTP_X_FILE_SIZE', isset($upload['size'][$index])? $upload['size'][$index] : null),
					\Input::server('HTTP_X_FILE_TYPE', isset($upload['type'][$index])? $upload['type'][$index] : null),
					isset($upload['error'][$index]) ? $upload['error'][$index] : null,
					$index,
					$album_image->id,
					$original_filename,
					$max_size
				);
			}
			else
			{
				$original_filename = $upload['name'];
				$filename = \Util_file::make_filename(\Input::server('HTTP_X_FILE_NAME', $upload['name']), $extention, $prefix);
				$filesize  = \Input::server('HTTP_X_FILE_SIZE', $upload['size']);
				$file_type = \Input::server('HTTP_X_FILE_TYPE', $upload['type']);

				// Exif データの取得
				$exif = ($this->is_save_exif_data) ? exif_read_data($upload['tmp_name']) : array();

				$this->check_filesize_per_member($filesize);
				$result = $this->handle_file_upload(
					isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
					$filename,
					\Input::server('HTTP_X_FILE_SIZE', isset($upload['size'])? $upload['size'] : null),
					\Input::server('HTTP_X_FILE_TYPE', isset($upload['type'])? $upload['type'] : null),
					isset($upload['error']) ? $upload['error'] : null,
					null,
					$album_image->id,
					$original_filename,
					$max_size
				);
			}

			if (isset($result->error)) throw new \Exception($result->error);

			// file の保存
			$model_file = new \Model_File;
			$model_file->name = $filename;
			$model_file->filesize = $result->size;
			$model_file->type = $file_type;
			$model_file->original_filename = $original_filename;
			if ($member_id) $model_file->member_id = $member_id;
			if ($exif)
			{
				if (!empty($exif['DateTimeOriginal'])) $model_file->shot_at = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
				$model_file->exif = serialize($exif);
			}
			$model_file->save();

			// album_image の保存
			$album_image->file_id = $model_file->id;
			$album_image->save();

			$this->member_filesize_total += $filesize;

			\DB::commit_transaction();
		}
		catch(LimitUploadFileSizeException $e)
		{
			\DB::rollback_transaction();
			$result->name = $original_filename;
			$result->error = $e->getMessage();
		}
		catch(\Exception $e)
		{
			\DB::rollback_transaction();
			$result->name = $original_filename;
		}

		return $result;
	}

	private function check_filesize_per_member($size)
	{
		if (!$this->accepted_upload_filesize) return;

		$accept_size = $this->accepted_upload_filesize - $this->member_filesize_total;
		if ($size > $accept_size) throw new LimitUploadFileSizeException('File size is over the limit of the member.');
	}
}
