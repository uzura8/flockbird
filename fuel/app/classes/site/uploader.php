<?php

class Site_Uploader
{
	private $options = array();

	public function __construct($options = array())
	{
		$this->options = array(
			'max_size'       => PRJ_UPLOAD_MAX_FILESIZE,
			'ext_whitelist'  => array_keys(Config::get('site.upload.types.img.accept_format')),
			'type_whitelist' => array('image'),
			'path_chmod'     => Config::get('site.upload.mkdir_mode'),
			'path'           => APPPATH.'tmp',
			'upload_dir'     => PRJ_UPLOAD_DIR.'img/raw/',
			'is_save_exif'   => PRJ_USE_EXIF_DATA,
			'auto_orient'   => true,
		);
		if ($options)
		{
			$this->options = $options + $this->options;
		}
	}

	public function save($file_path = null)
	{
		$file = array();
		try
		{
			if (!$file_path)
			{
				$file_info = $this->upload_file();
				$file_path = $file_info['path'];
			}
			$file = $this->get_file_info($file_path);
			$file['original_name'] = !empty($file_info['original_name']) ? $file_info['original_name'] : $file['name'];

			$file['name'] = Util_file::make_filename($file['original_name'], $file['ext']);
			if (!Site_Upload::check_and_make_uploaded_dir($this->options['upload_dir'], Config::get('site.upload.check_and_make_dir_level'), $this->options['path_chmod']))
			{
				throw new FuelException('ディレクトリの作成に失敗しました。');
			}
			$file_path = $this->save_raw_file($file['save_to'], $file['save_as'], $file['name']);
			$file['file_path'] = $file_path;
			//$file['new_filename'] = $file['name'];

			// exif データの取得
			$exif = array();
			if ($this->options['is_save_exif'] && $file['ext'] == 'jpg')
			{
				$exif = exif_read_data($file_path) ?: array();
			}
			$file['exif'] = $exif;

			// 大きすぎる場合はリサイズ & 保存ファイルから exif 情報削除
			$file_size_before = $file['size'];
			if ($max_size = Site_Upload::get_accepted_max_size($this->options['member_id']))
			{
				$file['size'] = Site_Upload::check_max_size_and_resize($file_path, $max_size);
			}
			if (Config::get('site.upload.remove_exif_data') && $file_size_before == $file['size'])
			{
				Util_file::resave($file_path);
			}

			if (!$model_file = $this->save_model_file($file, $exif))
			{
				throw new FuelException('画像情報の保存に失敗しました。');
			}
			$file['id'] = $model_file->id;
			$file['filepath'] = $model_file->path;
			$file['shot_at']  = $model_file->shot_at;
			//$this->make_thumbnails();// 各サイズの thumbnail を作成
		}
		catch(\FuelException $e)
		{
			if (isset($file_path) && file_exists($file_path)) Util_file::remove($file_path);
			$file['error'] = $e->getMessage();
		}

		return $file;
	}

	protected function save_model_file($file, $exif = array())
	{
		$model_file = new \Model_File;
		$model_file->name     = $file['name'];
		$model_file->filesize = $file['size'];
		$model_file->type     = $file['type'];
		$model_file->path     = $this->options['filepath'];
		$model_file->original_filename = $file['original_name'];
		$model_file->member_id         = $this->options['member_id'];
		if ($exif)
		{
			$model_file->exif = serialize($exif);
			if ($exif_time = Site_Upload::get_exif_datetime($exif))
			{
				$model_file->shot_at = $exif_time;
			}
		}
		if (!$model_file->shot_at) $model_file->shot_at = date('Y-m-d H:i:s');
		$model_file->save();

		return $model_file;
	}

	private function upload_file()
	{
		Upload::process($this->options);
		$this->validate();

		Upload::save(0);
		$file = Upload::get_files(0);

		return array('path' => $file['saved_to'].$file['saved_as'], 'original_name' => $file['name']);
	}

	private function get_file_info($file_path)
	{
		$file = array();
		$file_info = File::file_info($file_path);
		$file['size']    = $file_info['size'];
		$file['name']    = $file_info['basename'];
		$file['type']    = $file_info['mimetype'];
		$file['save_to'] = $file_info['dirname'].'/';
		$file['save_as'] = $file_info['basename'];
		$file['path']    = $file_info['realpath'];
		$file['ext']     =  Util_file::get_image_type($file_path);

		return $file;
	}

	private function validate()
	{
		if (!Upload::is_valid())
		{
			$errors = Upload::get_errors();
			if (!empty($errors[0]['errors'][0]['message']))
			{
				throw new FuelException($errors[0]['errors'][0]['message']);
			}
		}
		if (count(Upload::get_files()) > 1)
		{
			throw new FuelException('File upload error.');
		}
	}

	private function save_raw_file($original_file_dir, $original_filename, $new_filename)
	{
		$from = $original_file_dir.$original_filename;
		if (!file_exists($from))
		{
			throw new FuelException('File not found.');
		}
		$to = $this->options['upload_dir'].$new_filename;
		if (file_exists($to)) throw new FuelException('File already exists.');
		if (!Util_file::move($from, $to)) throw new FuelException('Save raw file error.');

		return $to;
	}

	private function make_thumbnails()
	{
		foreach ($this->sizes as $size)
		{
			$dir = sprintf('%s%s/%s', $this->cache_image_base_dir_path, $size, $this->filepath);
			Site_Upload::check_and_make_uploaded_dir($dir);
			$new_file = $dir.$this->filename;
			$item = Site_Upload::conv_size_str_to_array($size);
			Util_file::resize($this->raw_file, $new_file, $item['width'], $item['height'], $item['resize_type']);
		}
	}
}
