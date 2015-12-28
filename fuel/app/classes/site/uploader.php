<?php

class Site_Uploader
{
	private $options = array();
	private $file;

	public function __construct($options = array())
	{
		$this->file = new stdClass();

		$this->options = array(
			'upload_type'    => 'img',
			'max_size'       => FBD_UPLOAD_MAX_FILESIZE,
			'ext_whitelist'  => array_keys(conf('upload.types.img.accept_format')),
			'type_whitelist' => array('image'),
			'path_chmod'     => conf('upload.mkdir_mode'),
			'path'           => APPPATH.'tmp',
			'upload_dir'     => FBD_UPLOAD_DIR.'img/raw/',
			'storage_type'   => conf('upload.storageType'),
			'auto_orient'    => true,
			'is_clear_exif_on_file' => conf('isClearFromFile', 'exif'),
			'is_save_exif_to_db'    => conf('isSaveToDb.isEnabled', 'exif'),
			'exif_accept_tags'      => array(),
			'exif_ignore_tags'      => array(),
		);
		if (conf('isSaveToDb.filterTags.accept.isEnabled', 'exif')) $this->options['exif_accept_tags'] = conf('isSaveToDb.filterTags.accept.tags', 'exif');
		if (conf('isSaveToDb.filterTags.ignore.isEnabled', 'exif')) $this->options['exif_ignore_tags'] = conf('isSaveToDb.filterTags.ignore.tags', 'exif');

		if ($options)
		{
			$this->options = $options + $this->options;
		}
	}

	public function save($uploaded_file_path = null)
	{
		try
		{
			if (!$uploaded_file_path) $uploaded_file_path = $this->upload_file();
			$this->set_file_properties($uploaded_file_path);
			if (!Site_Upload::check_and_make_uploaded_dir($this->options['upload_dir'], null, $this->options['path_chmod']))
			{
				throw new FuelException('ディレクトリの作成に失敗しました。');
			}
			$tmp_file_path = APPPATH.'tmp/'.$this->file->name;
			if (!Util_file::move($uploaded_file_path, $tmp_file_path)) throw new FuelException('Save raw file error.');

			// exif データの取得
			$exif = array();
			if ($this->options['is_save_exif_to_db'] && $this->file->type == 'image/jpeg')
			{
				$exif = \Util_Exif::get_exif($tmp_file_path, $this->options['exif_accept_tags'], $this->options['exif_ignore_tags']);
			}

			$is_resaved = false;
			// 回転状態の補正
			if ($this->options['auto_orient'] && !empty($exif['Orientation']))
			{
				Util_file::correct_orientation($tmp_file_path, $exif['Orientation']);
				$is_resaved = true;
			}

			// 大きすぎる場合はリサイズ & 保存ファイルから exif 情報削除
			$file_size_before = $this->file->size;
			if ($max_size = Site_Upload::get_accepted_max_size($this->options['member_id']))
			{
				$this->file->size = Site_Upload::check_max_size_and_resize($tmp_file_path, $max_size);
				$is_resaved = $this->file->size != $file_size_before;
			}
			if ($this->options['is_clear_exif_on_file'] && !$is_resaved)
			{
				Site_Upload::clear_exif($tmp_file_path);
				$this->file->size = File::get_size($tmp_file_path);
			}
			$this->save_model_file($exif);
			$this->save_file_bin($tmp_file_path);
		}
		catch(\FuelException $e)
		{
			if (isset($this->file->file_path) && file_exists($this->file->file_path))
			{
				Util_file::remove($this->file->file_path);
				$this->file->size = filesize($this->file->file_path);
			}
			$this->file->error = $e->getMessage();
		}

		return $this->file;
	}

	protected function save_file_bin($tmp_file_path)
	{
		switch ($this->options['storage_type'])
		{
			case 'db':
				$storage_save_result = (bool)Model_FileBin::save_from_file_path($tmp_file_path, $this->file->name);
				break;
			case 'S3':
				$storage_save_result = (bool)Site_S3::save($tmp_file_path, null, $this->options['upload_type']);
				break;
			case 'normal':
			default :
				$storage_save_result = true;
				break;
		}
		if (!$storage_save_result)
		{
			Util_File::remove($tmp_file_path);
			throw new FuelException('Save raw file error to storage.');
		}
		if (!$result = (bool)Util_file::move($tmp_file_path, $this->file->file_path)) throw new FuelException('Save raw file error.');

		return $storage_save_result && $result;
	}

	protected function save_model_file($exif)
	{
		$model_file = new \Model_File;
		$model_file->name     = $this->file->name;
		$model_file->filesize = $this->file->size;
		$model_file->type     = $this->file->type;
		$model_file->original_filename = $this->file->original_name;
		$model_file->member_id         = $this->options['member_id'];
		if ($exif)
		{
			$model_file->exif = serialize($exif);
			if ($exif_time = Util_Exif::get_original_datetime($exif))
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

		if (!$this->file->name = \Site_Upload::make_unique_filename($ext, $this->options['filename_prefix'], $this->file->original_name, $this->options['upload_dir']))
		{
			throw new FuelException('File already exists.');
		}

		$this->file->file_path = $this->options['upload_dir'].str_replace($this->options['filename_prefix'], '', $this->file->name);
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
