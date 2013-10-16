<?php
namespace Album;

class LimitUploadFileSizeException extends \FuelException {}

class UploadHandler extends \JqueryFileUpload
{
	public $is_limit_upload_file_size = false;
	public $accepted_upload_filesize = 0;
	public $member_filesize_total = 0;
	public $is_save_exif_data = false;

	public function get($album_id = 0)
	{
		$file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
		$info = null;
		if ($file_name)
		{
			$info = $this->get_file_object($file_name);
		}
		//elseif ($album_id && \Config::get('album.display_setting.upload.display_uploaded_files'))
		//{
		//	$info = $this->get_file_objects($album_id);
		//}

		return json_encode($info);
	}

	protected function get_file_objects($album_id = 0)
	{
		if ($album_id)
		{
			$info = array();
			$album_images = Model_AlbumImage::query()->where('album_id', $album_id)->related('album')->related('file')->order_by('created_at')->get();
			foreach ($album_images as $album_image)
			{
				if (empty($album_image->file)) continue;

				$info[] = $this->get_file_object($album_image->file->name, $album_image->id, $album_image->file->original_filename, $album_image->file->path);
			}

			return $info;
		}

		return array_values(array_filter(array_map(
				array($this, 'get_file_object'),
				scandir($this->options['upload_dir'])
		)));

	}

	protected function get_file_object($file_name, $album_image_id = 0, $original_filename = '', $filepath = '')
	{
		$file_path = $this->options['upload_dir'].$filepath.$file_name;
		if (is_file($file_path) && $file_name[0] !== '.')
		{
			$file = new \stdClass();
			$file->name = $original_filename ?: $file_name;
			$file->size = filesize($file_path);
			$file->url = sprintf('%sraw/%s%s', $this->options['upload_url'], $filepath, rawurlencode($file_name));

			foreach($this->options['image_versions'] as $version => $options)
			{
				$file_path_version = sprintf('%s%s/%s%s', $this->options['upload_dir_cache'], $options['size'], $filepath, rawurlencode($file_name));
				$file_url_version  = sprintf('%s%s/%s%s', $this->options['upload_url'], $options['size'], $filepath, rawurlencode($file_name));
				if (is_file($file_path_version)) $file->{$version.'_url'} = $file_url_version;
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
		if ($this->options['is_tmp'])
		{
			$file->delete_url .= sprintf('?id=%d&type=file_tmp', rawurlencode($file->file_tmp_id));
		}
		elseif (!empty($file->album_image_id))
		{
			$file->delete_url .= sprintf('?id=%d&type=album_image', rawurlencode($file->album_image_id));
		}

		$file->delete_type = $this->options['delete_type'];
		if ($file->delete_type !== 'DELETE') {
			$file->delete_url .= '&_method=DELETE';
		}

		return $file;
	}

	protected function handle_file_upload_site($uploaded_file, $name, $filepath, $size, $type, $error, $index = null, $original_filename = '', $max_size = 0)
	{
		$file = new \stdClass();
		$file->name = $this->trim_file_name($name, $type, $index);
		$file->path = $filepath;
		$file->size = intval($size);
		$file->type = $type;
		if ($this->validate($uploaded_file, $file, $error, $index))
		{
			$this->handle_form_data($file, $index);
			$file_path = $this->options['upload_dir'].$file->path.$file->name;
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
				$file->url = $this->options['upload_url'].'raw/'.$file->path.rawurlencode($file->name);

				$file->size = \Site_Upload::check_max_size_and_resize($file_path, $max_size);

				foreach($this->options['image_versions'] as $version => $options)
				{
					if ($this->make_thumbnail($file_path, $this->options['upload_dir_cache'], $options['size'], $file->path, rawurlencode($file->name)))
					{
						if ($this->options['upload_dir'] !== $options['upload_dir'])
						{
							$file->{$version.'_url'} = sprintf('%s%s/%s%s', $this->options['upload_url'], $options['size'], $file->path, rawurlencode($file->name));
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
		}

		return $file;
	}

	public function post($album_id = 0, $member_id = 0, $max_size = 0)
	{
		if (!$album_id || !$member_id) return;

		$_method     = \Input::post('_method');
		$public_flag = \Input::post('public_flag', \Config::get('site.public_flag.default'));

		$contents = \Input::post('contents');
		$tmp_hash = \Input::post($this->options['tmp_hash_key']);
		if (!$this->options['is_tmp'])
		{
			$contents = '';
			$tmp_hash = '';
		}

		if (isset($_method) && $_method === 'DELETE')
		{
			return $this->delete();
		}
		$upload = \Input::file($this->options['param_name'], null);

		$HTTP_X_FILE_NAME = \Input::server('HTTP_X_FILE_NAME');
		$prefix = '';
		$info = array();
		if ($upload && is_array($upload['tmp_name']))
		{
			// param_name is an array identifier like "files[]",
			// $_FILES is a multi-dimensional array:
			foreach ($upload['tmp_name'] as $index => $value)
			{
				if (!$extention = \Util_file::check_image_type($upload['tmp_name'][$index], \Site_Upload::get_accept_format(), $upload['type'][$index]))
				{
					continue;
				}
				$info[] = $this->save_file($upload, $album_id, $member_id, $extention, $prefix, $max_size, $index, $public_flag, $contents, $tmp_hash);
			}
		}
		elseif ($upload || isset($HTTP_X_FILE_NAME))
		{
			if (!$extention = \Util_file::check_image_type($upload['tmp_name'], \Site_Upload::get_accept_format(), $upload['type']))
			{
				return;
			}
			$info[] = $this->save_file($upload, $album_id, $member_id, $extention, $prefix, $max_size, null, $public_flag, $contents, $tmp_hash);
		}

		if (!$this->options['is_tmp']) \Model_Member::recalculate_filesize_total($member_id);

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
		$response = array();
		$type = \Input::get('type');
		if (!$id = (int)\Input::get('id')) return json_encode($response);

		if ($type == 'file_tmp')
		{
			if (!$id || !$file_tmp = \Model_FileTmp::check_authority($id, $member_id))
			{
				throw new \HttpNotFoundException;
			}
			$deleted_filesize = \Model_FileTmp::delete_with_file($id);
			$response[] = $deleted_filesize;
		}
		elseif($type == 'album_image')
		{
			if (!$album_image = Model_AlbumImage::check_authority($id, $member_id))
			{
				throw new \HttpNotFoundException;
			}

			\DB::start_transaction();
			$deleted_filesize = Model_AlbumImage::delete_with_file($id);
			\Model_Member::add_filesize($member_id, -$deleted_filesize);
			\DB::commit_transaction();
			$response[] = $deleted_filesize;
		}

		return json_encode($response);
	}

	protected function save_file($upload, $album_id, $member_id, $extention = '', $prefix = '', $max_size = 0, $index = null, $public_flag = null, $contents = '', $tmp_hash = '')
	{
		try
		{
			\DB::start_transaction();
			if (is_null($public_flag)) $public_flag = \Config::get('site.public_flag.default');
			$filepath = \Site_Upload::get_filepath('ai', $album_id);

			if (!$this->options['is_tmp'])
			{
				// album_image の保存
				$album_image = Model_AlbumImage::forge(array(
					'album_id'    => (int)$album_id,
					'file_id'     => 0,
					'shot_at'     => date('Y-m-d H:i:s'),
					'public_flag' => $public_flag,
				));
				$album_image->save();
			}

			// param_name is a single object identifier like "file",
			// $_FILES is a one-dimensional array:
			if (isset($index))
			{
				$original_filename = $upload['name'][$index];
				$filename  = \Util_file::make_filename(\Input::server('HTTP_X_FILE_NAME', $upload['name'][$index]), $extention, $prefix);
				$filesize  = \Input::server('HTTP_X_FILE_SIZE', isset($upload['size'][$index])? $upload['size'][$index] : null);
				$file_type = \Input::server('HTTP_X_FILE_TYPE', isset($upload['type'][$index])? $upload['type'][$index] : null);

				// Exif データの取得
				$exif = ($this->is_save_exif_data) ? exif_read_data($upload['tmp_name'][$index]) : array();

				$this->check_filesize_per_member($filesize);
				$result = $this->handle_file_upload_site(
					isset($upload['tmp_name'][$index]) ? $upload['tmp_name'][$index] : null,
					$filename,
					$filepath,
					$filesize,
					$file_type,
					isset($upload['error'][$index]) ? $upload['error'][$index] : null,
					$index,
					$original_filename,
					$max_size
				);
			}
			else
			{
				$original_filename = $upload['name'];
				$filename = \Util_file::make_filename(\Input::server('HTTP_X_FILE_NAME', $upload['name']), $extention, $prefix);
				$filesize  = \Input::server('HTTP_X_FILE_SIZE', isset($upload['size'])? $upload['size'] : null);
				$file_type = \Input::server('HTTP_X_FILE_TYPE', isset($upload['type'])? $upload['type'] : null);

				// Exif データの取得
				$exif = ($this->is_save_exif_data) ? exif_read_data($upload['tmp_name']) : array();

				$this->check_filesize_per_member($filesize);
				$result = $this->handle_file_upload_site(
					isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
					$filename,
					$filepath,
					$filesize,
					$file_type,
					isset($upload['error']) ? $upload['error'] : null,
					null,
					$original_filename,
					$max_size
				);
			}

			if (isset($result->error)) throw new \FuelException($result->error);

			// file の保存
			$is_tmp = $this->options['is_tmp'];
			$model_file = $is_tmp ? new \Model_FileTmp : new \Model_File;
			$model_file->name = $filename;
			$model_file->path = $filepath;
			$model_file->filesize = $result->size;
			$model_file->type = $file_type;
			$model_file->original_filename = $original_filename;
			if ($member_id) $model_file->member_id = $member_id;
			if ($exif)
			{
				if (!empty($exif['DateTimeOriginal'])) $model_file->shot_at = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
				$model_file->exif = serialize($exif);
			}
			//if (empty($model_file->shot_at)) $model_file->shot_at = date('Y-m-d H:i:s');
			if ($is_tmp)
			{
				$model_file->hash     = $tmp_hash;
				$model_file->contents = $contents;
			}
			$model_file->save();

			if ($is_tmp)
			{
				$result->file_tmp_id = $model_file->id;
			}
			else
			{
				// album_image の保存
				$album_image->file_id = $model_file->id;
				$album_image->shot_at = isset($model_file->shot_at) ? $model_file->shot_at : date('Y-m-d H:i:s');
				$album_image->save();

				$this->member_filesize_total += $result->size;

				$result->album_image_id = $album_image->id;
			}
			\DB::commit_transaction();
			$result = $this->set_file_delete_url($result);
		}
		catch(LimitUploadFileSizeException $e)
		{
			\DB::rollback_transaction();
			if (!empty($result)) $result->name = $original_filename;
			$result->error = $e->getMessage();
		}
		catch(\FuelException $e)
		{
			\DB::rollback_transaction();
			if (!empty($result)) $result->name = $original_filename;
		}

		return $result;
	}

	private function check_filesize_per_member($size)
	{
		if (!$this->accepted_upload_filesize) return;

		$accept_size = $this->accepted_upload_filesize - $this->member_filesize_total;
		if ($size > $accept_size) throw new LimitUploadFileSizeException('File size is over the limit of the member.');
	}

	private function make_thumbnail($from_file, $to_base_dir, $size, $filepath, $filename)
	{
		if (!file_exists($from_file)) return false;

		$to_dir = sprintf('%s%s/%s', $to_base_dir, $size, $filepath);
		if (!file_exists($to_dir)) \Site_Upload::check_and_make_uploaded_dir($to_dir);
		$new_file = $to_dir.$filename;
		$item = \Site_Upload::conv_size_str_to_array($size);

		return \Util_file::resize($from_file, $new_file, $item['width'], $item['height'], $item['resize_type']);
	}
}
