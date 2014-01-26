<?php

class Site_Uploader
{
	private $options = array();
	private $file;

	public function __construct($options = array())
	{
		$this->file = new stdClass();

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

	public function save($tmp_file_path = null)
	{
		try
		{
			if (!$tmp_file_path) $tmp_file_path = $this->upload_file();
			$this->set_file_properties($tmp_file_path);
			if (!Site_Upload::check_and_make_uploaded_dir($this->options['upload_dir'], Config::get('site.upload.check_and_make_dir_level'), $this->options['path_chmod']))
			{
				throw new FuelException('ディレクトリの作成に失敗しました。');
			}
			if (!Util_file::move($tmp_file_path, $this->file->file_path)) throw new FuelException('Save raw file error.');

			// exif データの取得
			$exif = null;
			if ($this->options['is_save_exif'] && $this->file->type == 'image/jpeg')
			{
				$exif = exif_read_data($this->file->file_path) ?: null;
			}

			$is_resaved = false;
			// 回転状態の補正
			if ($this->options['auto_orient'] && !empty($exif['Orientation']))
			{
				Util_file::correct_orientation($this->file->file_path, $exif['Orientation']);
				$is_resaved = true;
			}

			// 大きすぎる場合はリサイズ & 保存ファイルから exif 情報削除
			$file_size_before = $this->file->size;
			if ($max_size = Site_Upload::get_accepted_max_size($this->options['member_id']))
			{
				$this->file->size = Site_Upload::check_max_size_and_resize($this->file->file_path, $max_size);
				$is_resaved = true;
			}
			if (Config::get('site.upload.remove_exif_data') && !$is_resaved)
			{
				Util_file::resave($this->file->file_path);
			}

			$this->save_model_file($exif);
		}
		catch(\FuelException $e)
		{
			if (isset($this->file->file_path) && file_exists($this->file->file_path))
			{
				Util_file::remove($this->file->file_path);
			}
			$this->file->error = $e->getMessage();
		}

		return $this->file;
	}

	protected function save_model_file($exif)
	{
		$model_file = new \Model_File;
		$model_file->name     = $this->file->name;
		$model_file->filesize = $this->file->size;
		$model_file->type     = $this->file->type;
		$model_file->path     = $this->options['filepath'];
		$model_file->original_filename = $this->file->original_name;
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

		$this->file->id       = $model_file->id;
		$this->file->shot_at  = $model_file->shot_at;
	}

	private function upload_file()
	{
		Upload::process($this->options);
		$this->validate();
		Upload::save(0);
		$file = Upload::get_files(0);

		$this->file->original_name = $file['name'];

		return $file['saved_to'].$file['saved_as'];
	}

	private function set_file_properties($file_path)
	{
		$ext = Util_file::get_image_type($file_path);
		$file_info = File::file_info($file_path);
		$this->file->size = $file_info['size'];
		$this->file->type = $file_info['mimetype'];
		if (empty($this->file->original_name)) $this->file->original_name = $file_info['filename'];

		if (!$this->file->name = \Site_Upload::make_file_name($this->file->original_name, $ext, $this->options['upload_dir']))
		{
			throw new FuelException('File already exists.');
		}

		$this->file->file_path = $this->options['upload_dir'].$this->file->name;
		$this->file->filepath = $this->options['filepath'];
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
}
