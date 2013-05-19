<?php

class Site_image
{
	private $identifier = '';
	private $filename = '';
	private $extension = 'gif';//noimage 画像の拡張子をデフォルト値とする
	private $size = '';
	private $widtn = '';
	private $height = '';
	private $is_noimage = false;

	public function __construct($config = array())
	{
		$this->setup($config);
	}

	private function setup($config)
	{
		if (!$this->check_configs($config)) $this->is_noimage = true;
		if (!$this->check_filename()) $this->is_noimage = true;
		$this->set_size();
	}

	private function check_configs($config)
	{
		$result = true;
		$necessary_keys = array(
			'identifier',
			'group_number',
			'size',
			'filename',
		);
		foreach ($necessary_keys as $key)
		{
			if (empty($config[$key]))
			{
				$result = false;
				continue;
			}
			$this->$key = $config[$key];
		}

		return $result;
	}

	private function set_size()
	{
		$this->check_size();
		if ($this->size == 'raw')
		{
			$this->size = Config::get('site.upload_files.img.default_size');
			return;
		}

		list($this->width, $this->height) = explode('x', $this->size);
	}

	private function check_size()
	{
		if ($this->is_noimage && empty($this->identifier))
		{
			$this->size = Config::get('site.upload_files.img.default_size');
			return;
		}

		$sizes = Config::get('site.upload_files.img.type.'.$this->identifier.'.sizes');
		if (empty($sizes))
		{
			$this->size = $default_size;
			return;
		}

		if (!in_array($this->size, $sizes))
		{
			$this->size = $default_size;
			return;
		}
	}

	private function check_filename()
	{
		if (empty($this->filename)) return false;

		// '/('.implode('|', $ids).')_[0-9]+_[0-9a-f]+\.(jpg|png|gif)/i';
		if (!preg_match(Site_util::get_filename_format(), $this->filename, $matches)) return false;
		if ($this->identifier != $matches[1]) return false;

		$accept_formats = Config::get('site.upload_files.img.accept_format');
		if (!$accept_formats || !in_array($matches[2], $accept_formats)) return false;

		$this->extension = $matches[2];

		return true;
	}

	public function get_noimage()
	{
		return file_get_contents($this->get_noimage_file_path());
	}

	private function get_noimage_file_path()
	{
		$original_noimage_filename  = 'noimage.gif';
		$original_noimage_file_path = sprintf('%s/assets/img/site/%s', PRJ_PUBLIC_DIR, $original_noimage_filename);
		if (!$this->identifier) return $original_noimage_file_path;

		$noimage_file_dir  = sprintf('%s/img/%s', PRJ_UPLOAD_DIR, $this->identifier);
		$noimage_filename  = sprintf('%s_%s', $this->size, $original_noimage_filename);
		$noimage_file_path = sprintf('%s/%s', $noimage_file_dir, $noimage_filename);
		if (!file_exists($noimage_file_path))
		{
			$this->make_image($original_noimage_file_path, $noimage_file_dir, $noimage_filename);
		}

		return $noimage_file_path;
	}

	public function get_image()
	{
		if ($this->is_noimage) return $this->get_noimage();

		$common_path = sprintf('%s/img/%s/%s', PRJ_UPLOAD_DIR, $this->identifier, $this->group_number);
		$target_file_dir = sprintf('%s/%s', $common_path, $this->size);
		$target_file_path = sprintf('%s/%s', $target_file_dir, $this->filename);
		if (!file_exists($target_file_path))
		{
			$original_file_path = sprintf('%s/raw/%s', $common_path, $this->filename);
			if (!file_exists($original_file_path)) return $this->get_noimage();

			$this->make_image($original_file_path, $target_file_dir, $this->filename);
		}

		return file_get_contents($target_file_path);
	}

	private function make_image($original_file_path, $target_file_dir, $target_filename)
	{
		$target_file_path = sprintf('%s/%s', $target_file_dir, $target_filename);
		if (!file_exists($target_file_dir) && $target_path = Util_file::check_exists_file_path($target_file_dir, 4))
		{
			Util_file::make_dir_recursive($target_file_dir);
			Util_file::chmod_recursive($target_path, 0777);
		}
		$resize_type = Site_util::get_image_resize_type($this->identifier);
		Util_file::resize($original_file_path, $target_file_path, $this->width, $this->height, $resize_type);
	}

	public function get_extension()
	{
		return $this->extension;
	}
}
