<?php

class MyUploadHandler extends UploadHandler
{
	public function get_file_objects_from_file_tmps($file_tmps, $type = 'img')
	{
		if (!in_array($type, array('img', 'file'))) throw new InvalidArgumentException('Second parameter is invalid.');
		//if (!$this->options['member_id']) throw new \FuelException('Need member_id.');

		$files = array();
		foreach ($file_tmps as $file_tmp)
		{
			$file_name = $this->remove_filename_prefix($file_tmp->name);
			if (!$file = $this->get_file_object($file_name)) continue;
			$file->is_tmp = true;
			$file->name_prefix = $this->options['filename_prefix'];
			$file->id = $file_tmp->id;
			$file->original_name = $file_tmp->original_filename;
			$file->description   = $file_tmp->description;
			if ($type == 'img')
			{
				$file->thumbnail_uri = $this->options['image_versions']['thumbnail']['upload_url'].$file_name;
			}

			$files[] = $file;
		}

		return $files;
	}

	public function get_file_objects_from_related_model($model_objs, $image_names_posted = array())
	{
		$files = array();
		if (!$model_objs) return $files;

		$key = Util_Array::get_first_key($model_objs);
		$table = $model_objs[$key]->table();
		$need_member_id_tables = array('album_image');
		if (in_array($table, $need_member_id_tables) && !$this->options['member_id']) throw new \FuelException('Need member_id.');

		$file_cate = $model_objs[$key]->get_image_prefix();
		$cache_size    = conf('upload.types.img.types.'.$file_cate.'.sizes.thumbnail');

		foreach ($model_objs as $model_obj)
		{
			$file_name = $this->remove_filename_prefix($model_obj->file_name);
			if (!$file = $this->get_file_object($file_name)) continue;
			$file_obj = Model_File::get4name($model_obj->file_name);
			$file->is_tmp = false;
			$file->name_prefix = $this->options['filename_prefix'];
			$file->id = (int)$model_obj->id;
			$file->original_name = $file_obj->original_filename;
			$file->thumbnail_uri = Site_Upload::get_uploaded_file_path($model_obj->file_name, $cache_size, 'img', false, true);
			
			$file->description   = $model_obj->name;
			if (isset($image_names_posted[$model_obj->id]) && strlen($image_names_posted[$model_obj->id]))
			{
				$file->description = $image_names_posted[$model_obj->id];
			}
			if (!empty($this->options['accept_sizes'])) $file->accept_sizes = $this->options['accept_sizes'];
			$files[] = $file;
		}

		return $files;
	}

	public function get($print_response = true)
	{
		//if ($print_response && isset($_GET['download']))
		//{
		//	return $this->download();
		//}
		$file_name = $this->get_file_name_param();
		if (!$file_name) throw new \FuelException('Not requested file_name.');

		if (!$this->options['member_id']) throw new \FuelException('Need member_id.');
		if (!$file_tmp = \Model_FileTmp::get4name_and_member_id($file_name, $this->options['member_id'], $this->options['user_type']))
		{
			throw new \FuelException('Not exists file_tmp data.');
		}

		$file = $this->get_file_object($file_name);
		$file->file_tmp_id   = $file_tmp->id;
		$file->original_name = $file_tmp->original_filename;
		$response = array($this->get_singular_param_name() => $file);

		return $this->generate_response($response, $print_response);
	}

	public function post($print_response = true)
	{
		$upload = \Input::file($this->options['param_name'], null);
		// Parse the Content-Disposition header, if available:
		$file_name = \Input::server('HTTP_CONTENT_DISPOSITION') ?
			rawurldecode(preg_replace(
				'/(^[^"]+")|("$)/',
				'',
				\Input::server('HTTP_CONTENT_DISPOSITION')
			)) : null;
		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range = \Input::server('HTTP_CONTENT_RANGE') ?
			preg_split('/[^0-9]+/', \Input::server('HTTP_CONTENT_RANGE')) : null;
		$size =  $content_range ? $content_range[3] : null;
		$files = array();
		if ($upload && is_array($upload['tmp_name']))
		{
			// param_name is an array identifier like "files[]",
			// $_FILES is a multi-dimensional array:
			foreach ($upload['tmp_name'] as $index => $value)
			{
				$files[] = $this->handle_file_upload(
					$upload['tmp_name'][$index],
					$file_name ? $file_name : $upload['name'][$index],
					$size ? $size : $upload['size'][$index],
					$upload['type'][$index],
					$upload['error'][$index],
					$index,
					$content_range
				);
			}
		}
		else
		{
			// param_name is a single object identifier like "file",
			// $_FILES is a one-dimensional array:
			$files[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				$file_name ? $file_name : (isset($upload['name']) ?
								$upload['name'] : null),
				$size ? $size : (isset($upload['size']) ?
								$upload['size'] : \Input::server('CONTENT_LENGTH')),
				isset($upload['type']) ?
								$upload['type'] : \Input::server('CONTENT_TYPE'),
				isset($upload['error']) ? $upload['error'] : null,
				null,
				$content_range
			);
		}

		return $this->generate_response(
			array($this->options['param_name'] => $files),
			$print_response
		);
	}

	public function delete($print_response = true, Model_FileTmp $file_tmp = null)
	{
		$response = array();
		if ($file_tmp)
		{
			$response[$file_tmp->name] = $this->delete_file($file_tmp->name, $this->options['storage_type']) && $file_tmp->delete();
		}

		return $this->generate_response($response, $print_response);
	}

	protected function handle_file_upload($uploaded_file, $original_name, $size, $type, $error, $index = null, $content_range = null)
	{
		$file = new \stdClass();
		$file->is_tmp = $this->options['is_tmp'];
		$file->original_name = $original_name;
		$file->size = $this->fix_integer_overflow(intval($size));
		$file->type = $type;
		if (!$extention = Util_file::check_file_type($uploaded_file, \Site_Upload::get_accept_format($this->options['upload_type']), $type, $this->options['upload_type']))
		{
			$file->error = $this->get_error_message('accept_file_types');
			return $file;
		}
		if (!$filename_with_prefix = Site_Upload::make_unique_filename($extention, $this->options['filename_prefix'], $original_name))
		{
			$file->error = 'ファイル名の作成に失敗しました。';
			return $file;
		}
		$file->name = $this->remove_filename_prefix($filename_with_prefix);
		$file->name_prefix = $this->options['filename_prefix'];
		if (!\Site_Upload::check_and_make_uploaded_dir($this->options['upload_dir'], null, $this->options['mkdir_mode']))
		{
			$file->error = 'ディレクトリの作成に失敗しました。';
			return $file;
		}
		if (!$this->validate($uploaded_file, $file, $error, $index))
		{
			return $file;
		}

		if ($this->options['upload_type'] == 'img')
		{
			$file->thumbnail_uri = $this->options['image_versions']['thumbnail']['upload_url'].$file->name;
		}
		$this->handle_form_data($file, $index);
		$upload_dir = $this->get_upload_path();
		$file_path = $this->get_upload_path($file->name);
		$append_file = $content_range && is_file($file_path) &&
				$file->size > $this->get_file_size($file_path);
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
				$res = move_uploaded_file($uploaded_file, $file_path);
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
		$file_size = $this->get_file_size($file_path, $append_file);
		if ($file_size === $file->size)
		{
			$file->url = $this->get_download_url($file->name);
			if ($this->is_valid_image_file($file_path))
			{
				$this->handle_image_file($file_path, $file);
			}
		}
		else
		{
			$file->size = $file_size;
			if (!$content_range && $this->options['discard_aborted_uploads'])
			{
				$this->delete_file($filename_with_prefix, $this->options['storage_type']);
				$file->error = 'abort';
			}
		}
		$this->set_additional_file_properties($file);

		// exif データの取得
		$exif = array();
		if ($this->options['is_save_exif_to_db'] && $extention == 'jpg')
		{
			$exif = \Util_Exif::get_exif($file_path, $this->options['exif_accept_tags'], $this->options['exif_ignore_tags']);
		}

		if ($this->options['upload_type'] == 'img')
		{
			// 大きすぎる場合はリサイズ & 保存ファイルから exif 情報削除
			$file_size_before = $file->size;
			if ($this->options['member_id'] && $this->options['user_type'] === 0 && $max_size = Site_Upload::get_accepted_max_size($this->options['member_id']))
			{
				$file->size = Site_Upload::check_max_size_and_resize($file_path, $max_size);
			}
			// Exif情報の削除
			$is_resaved = $file->size != $file_size_before;
			if ($this->options['is_clear_exif_on_file'] && !$is_resaved)
			{
				Site_Upload::clear_exif($file_path);
				$file->size = File::get_size($file_path);
			}

			if (!empty($this->options['accept_sizes'])) $file->accept_sizes = $this->options['accept_sizes'];
		}

		try
		{
			if ($this->options['storage_type'] != 'normal')
			{
				$this->save_file2storage($file_path, $filename_with_prefix);
				$this->delete_file($filename_with_prefix, $this->options['storage_type'], false, false);
			}
			$file->id = $this->save_file($file, $exif);
		}
		catch(\Exception $e)
		{
			if ($this->options['is_output_log_save_error'])
			{
				\Util_Toolkit::log_error(sprintf('file save error: %s', $e->getMessage()));
			}
			$this->delete_file($filename_with_prefix, $this->options['storage_type']);
			$file->error = 'ファイルの保存に失敗しました。';
		}

		return $file;
	}

	protected function save_file2storage($file_path, $filename_with_prefix)
	{
		if ($this->options['storage_type'] == 'db')
		{
			Model_FileBin::save_from_file_path($file_path, $filename_with_prefix, $this->options['upload_type'] == 'img');
		}
		elseif ($this->options['storage_type'] == 'S3')
		{
			Site_S3::save($file_path, $filename_with_prefix, $this->options['upload_type']);
		}
	}

	protected function save_file($file, $exif = array())
	{
		$model_file_name = $this->options['is_tmp'] ? '\Model_FileTmp' : '\Model_File';
		$model_file = $model_file_name::forge();
		$model_file->name = $this->options['filename_prefix'].$file->name;
		$model_file->filesize = $file->size;
		$model_file->type = $file->type;
		$model_file->original_filename = $file->original_name;
		if ($this->options['member_id']) $model_file->member_id = $this->options['member_id'];
		$model_file->user_type = $this->options['user_type'];
		if ($exif)
		{
			$model_file->exif = serialize($exif);
			if ($exif_time = Util_Exif::get_original_datetime($exif))
			{
				$model_file->shot_at = $exif_time;
			}
		}
		if (!$model_file->save()) throw new \FuelException('Failed to save file.');

		return $model_file->id;
	}

	protected function delete_file($filename, $storage_type = 'normal', $is_delete_raw_file_only = false, $is_delete_with_storage_data = true)
	{
		$filename_excluded_prefix = str_replace($this->options['filename_prefix'], '', $filename);
		$file_path = $this->get_upload_path($filename_excluded_prefix);
		$success = is_file($file_path) && unlink($file_path);
		if ($success && !$is_delete_raw_file_only)
		{
			foreach($this->options['image_versions'] as $version => $options)
			{
				if (!empty($version))
				{
					$varsion_file = $this->get_upload_path($filename_excluded_prefix, $version);
					if (is_file($varsion_file)) unlink($varsion_file);
				}
			}
		}
		if (!$is_delete_with_storage_data) return $success;

		if ($storage_type == 'db')
		{
			if ($file_bin = Model_FileBin::get4name($filename)) $success = (bool)$file_bin->delete();
		}
		elseif ($storage_type == 'S3')
		{
			$success = (bool)Site_S3::delete($filename, $this->options['upload_type']);
		}

		return $success;
	}

	//private function check_filesize_per_member($size)
	//{
	//	if (!$this->accepted_upload_filesize) return;
	//
	//	$accept_size = $this->accepted_upload_filesize - $this->member_filesize_total;
	//	if ($size > $accept_size) throw new LimitUploadFileSizeException('File size is over the limit of the member.');
	//}

	protected function validate($uploaded_file, $file, $error, $index)
	{
		if ($error)
		{
			$file->error = $this->get_error_message($error);
			return false;
		}
		$content_length = $this->fix_integer_overflow(intval(\Input::server('CONTENT_LENGTH')));
		$post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
		if ($post_max_size && ($content_length > $post_max_size))
		{
			$file->error = $this->get_error_message('post_max_size');
			return false;
		}
		if (!preg_match($this->options['accept_file_types'], $file->name))
		{
			$file->error = $this->get_error_message('accept_file_types');
			return false;
		}
		if ($uploaded_file && is_uploaded_file($uploaded_file))
		{
			$file_size = $this->get_file_size($uploaded_file);
		}
		else
		{
			$file_size = $content_length;
		}
		if ($this->options['max_file_size'] && (
					$file_size > $this->options['max_file_size'] ||
					$file->size > $this->options['max_file_size']))
		{
			$file->error = $this->get_error_message('max_file_size');
			return false;
		}
		if ($this->options['min_file_size'] && $file_size < $this->options['min_file_size'])
		{
			$file->error = $this->get_error_message('min_file_size');
			return false;
		}
		if (is_int($this->options['max_number_of_files']) && $this->count_file_objects() >= $this->options['max_number_of_files'])
		{
			$file->error = $this->get_error_message('max_number_of_files');
			return false;
		}
		$max_width = @$this->options['max_width'];
		$max_height = @$this->options['max_height'];
		$min_width = @$this->options['min_width'];
		$min_height = @$this->options['min_height'];
		if (($max_width || $max_height || $min_width || $min_height))
		{
			list($img_width, $img_height) = $this->get_image_size($uploaded_file);
		}
		if (!empty($img_width))
		{
			if ($max_width && $img_width > $max_width)
			{
				$file->error = $this->get_error_message('max_width');
				return false;
			}
			if ($max_height && $img_height > $max_height)
			{
				$file->error = $this->get_error_message('max_height');
				return false;
			}
			if ($min_width && $img_width < $min_width)
			{
				$file->error = $this->get_error_message('min_width');
				return false;
			}
			if ($min_height && $img_height < $min_height)
			{
				$file->error = $this->get_error_message('min_height');
				return false;
			}
		}

		return true;
	}

	protected function is_valid_file_object($filename_excluded_prefix)
	{
		$file_path = $this->get_upload_path($filename_excluded_prefix);
		if (is_file($file_path) && $filename_excluded_prefix[0] !== '.')
		{
			return true;
		}
		if ($this->options['storage_type'] != 'normal' && !file_exists($file_path))
		{
			$file_name = $this->options['filename_prefix'].$filename_excluded_prefix;
			if (Site_Upload::make_raw_file_from_storage($file_name, $file_path, $this->options['storage_type'], $this->options['upload_type'])) return true;
		}

		return false;
	}

	protected function remove_filename_prefix($filename)
	{
		return str_replace($this->options['filename_prefix'], '', $filename);
	}
}
