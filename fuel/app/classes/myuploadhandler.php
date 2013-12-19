<?php

class MyUploadHandler extends UploadHandler
{
	public function get_file_objects_from_file_tmps($file_tmps)
	{
		if (!$this->options['member_id']) throw new \FuelException('Need member_id.');

		$thumbnail_dir_uri = $this->options['upload_uri'].'thumbnail/';
		$files = array();
		foreach ($file_tmps as $file_tmp)
		{
			$file = $this->get_file_object($file_tmp->name);
			$file->is_tmp   = true;
			$file->file_tmp_id   = $file_tmp->id;
			$file->original_name = $file_tmp->original_filename;
			$file->thumbnail_uri = $thumbnail_dir_uri.$file_tmp->name;
			$file->description   = $file_tmp->description;
			$files[] = $file;
		}

		return $files;
	}

	public function get_file_objects_from_album_images($album_images, $album_image_names_posted)
	{
		if (!$this->options['member_id']) throw new \FuelException('Need member_id.');

		$cache_dir_uri = Config::get('site.upload.types.img.root_path.cache_dir');
		$cache_size    = Config::get('site.upload.types.img.types.ai.sizes.thumbnail');
		$files = array();
		foreach ($album_images as $album_image)
		{
			$file = $this->get_file_object($album_image->file->name);
			$file->is_tmp = false;
			$file->id = (int)$album_image->id;
			$file->original_name = $album_image->file->original_filename;
			$file->thumbnail_uri = sprintf('%s%s/%s%s', $cache_dir_uri, $cache_size, $album_image->file->path, $album_image->file->name);
			
			$file->description   = $album_image->name;
			if (!is_null($album_image_names_posted[$album_image->id]) && strlen($album_image_names_posted[$album_image->id]))
			{
				$file->description = $album_image_names_posted[$album_image->id];
			}
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
		if (!$file_tmp = \Model_FileTmp::get4name_and_member_id($file_name, $this->options['member_id']))
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

		$file_name = $file_tmp->name;
		$file_path = $this->get_upload_path($file_name);
		$success = is_file($file_path) && unlink($file_path);
		if ($success)
		{
			foreach($this->options['image_versions'] as $version => $options)
			{
				if (!empty($version))
				{
					$file = $this->get_upload_path($file_name, $version);
					if (is_file($file)) unlink($file);
				}
			}

			if ($file_tmp) $file_tmp->delete();
		}
		$response[$file_name] = $success;

		return $this->generate_response($response, $print_response);
	}

	protected function handle_file_upload($uploaded_file, $original_name, $size, $type, $error, $index = null, $content_range = null)
	{
		$file = new \stdClass();
		$file->is_tmp = true;
		$file->original_name = $original_name;
		$file->size = $this->fix_integer_overflow(intval($size));
		$file->type = $type;
		if (!$extention = \Util_file::check_image_type($uploaded_file, \Site_Upload::get_accept_format(), $type))
		{
			$file->error = $this->get_error_message('accept_file_types');
			return $file;
		}
		if (!$file->name = $this->make_file_name($original_name, $extention))
		{
			$file->error = 'ファイル名の作成に失敗しました。';
			return $file;
		}
		if (!\Site_Upload::check_and_make_uploaded_dir($this->options['upload_dir'], Config::get('site.upload.check_and_make_dir_level'), $this->options['mkdir_mode']))
		{
			$file->error = 'ディレクトリの作成に失敗しました。';
			return $file;
		}
		if (!$this->validate($uploaded_file, $file, $error, $index))
		{
			return $file;
		}

		$file->thumbnail_uri = sprintf('%sthumbnail/%s', $this->options['upload_uri'], $file->name);
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
				$this->delete_file($file->name);
				$file->error = 'abort';
			}
		}
		$this->set_additional_file_properties($file);

		// exif データの取得
		$exif = array();
		if ($this->options['is_save_exif'] && $extention == 'jpg')
		{
			$exif = exif_read_data($file_path) ?: array();
		}

		// 大きすぎる場合はリサイズ & 保存ファイルから exif 情報削除
		$file_size_before = $file->size;
		if ($max_size = Site_Upload::get_accepted_max_size($this->options['member_id']))
		{
			$file->size = Site_Upload::check_max_size_and_resize($file_path, $max_size);
		}
		if (Config::get('site.upload.remove_exif_data') && $file_size_before == $file->size)
		{
			Util_file::resave($file_path);
		}

		try
		{
			$file->id = $this->save_file_tmp($file, $exif);
		}
		catch(\FuelException $e)
		{
			$this->delete_file($file->name);
			$file->error = '画像情報の保存に失敗しました。';
		}

		return $file;
	}

	protected function make_file_name($original_filename, $extention)
	{
		$name = \Util_file::make_filename($original_filename, $extention);
		$i = 0;
		while(file_exists($this->options['upload_dir'].$name))
		{
			$name = \Util_file::make_filename($original_filename, $extention);
			if ($i == 3) return false;
			$i++;
		}

		return $name;
	}

	protected function save_file_tmp($file, $exif = array())
	{
		$model_file_tmp = new \Model_FileTmp;
		$model_file_tmp->name = $file->name;
		$model_file_tmp->path = $this->options['filepath'];
		$model_file_tmp->filesize = $file->size;
		$model_file_tmp->type = $file->type;
		$model_file_tmp->original_filename = $file->original_name;
		$model_file_tmp->member_id = $this->options['member_id'];
		if ($exif)
		{
			$model_file_tmp->exif = serialize($exif);
			if ($exif_time = Site_Upload::get_exif_datetime($exif))
			{
				$model_file_tmp->shot_at = $exif_time;
			}
		}
		$model_file_tmp->save();

		return $model_file_tmp->id;
	}

	protected function delete_file($file_name)
	{
		$file_path = $this->get_upload_path($file_name);
		$success = is_file($file_path)  && unlink($file_path);
		if ($success)
		{
			foreach($this->options['image_versions'] as $version => $options)
			{
				if (!empty($version))
				{
					$varsion_file = $this->get_upload_path($file_name, $version);
					if (is_file($varsion_file)) unlink($varsion_file);
				}
			}
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
}
