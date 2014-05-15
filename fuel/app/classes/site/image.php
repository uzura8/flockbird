<?php

class Site_image
{
	private $file_cate   = '';
	private $split_num   = '';
	private $filename    = '';
	private $filepath    = '';
	private $extension   = 'gif';//noimage 画像の拡張子をデフォルト値とする
	private $size        = '';
	private $widtn       = '';
	private $resize_type = '';
	private $height      = '';
	private $is_noimage  = false;

	public function __construct($config = array())
	{
		$this->setup($config);
	}

	private function setup($config)
	{
		if (!$this->set_configs($config))   $this->is_noimage = true;
		if (!$this->check_configs($config)) $this->is_noimage = true;
		$this->filepath = sprintf('%s/%s/', $this->file_cate, $this->split_num);
		if (!$this->check_file()) $this->is_noimage = true;
		$this->set_size();
	}

	private function set_configs($config)
	{
		$result = true;
		$necessary_keys = array(
			'file_cate',
			'split_num',
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

	private function check_configs()
	{
		return $this->check_file_cate() && $this->check_split_num();
	}

	private function check_file_cate()
	{
		if (!$is_correct = in_array($this->file_cate, array_keys(conf('upload.types.img.types'))))
		{
			$this->file_cate = null;
		}

		return $is_correct;
	}

	private function check_split_num()
	{
		if (!$is_correct = $this->split_num == 'all' || is_numeric($this->split_num))
		{
			$this->split_num = 'all';
		}

		return $is_correct;
	}

	private function check_file()
	{
		if ($this->filename == conf('upload.types.img.noimage_filename'))   return false;
		if (!Site_Upload::check_uploaded_file_exists($this->filepath, $this->filename)) return false;
		if (!$this->check_filename()) return false;

		return true;
	}

	private function set_size()
	{
		if ($this->size == 'raw') return;
		$this->check_size();

		$item = Site_Upload::conv_size_str_to_array($this->size);
		$this->width       = $item['width'];
		$this->height      = $item['height'];
		$this->resize_type = $item['resize_type'];
	}

	private function check_size()
	{
		$default_size = conf('upload.types.img.defaults.default_size');
		if (!$this->size)
		{
			$this->size = $default_size;
			return;
		}

		$sizes = Site_Upload::get_sizes_all4file_cate($this->file_cate);
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

		$ext = Util_file::get_extension_from_filename($this->filename);
		$accept_formats = Site_Upload::get_accept_format();
		if (!$accept_formats || !in_array($ext, $accept_formats)) return false;

		$this->extension = $ext;

		return true;
	}

	public function get_noimage()
	{
		return file_get_contents($this->get_noimage_file_path());
	}

	private function get_noimage_file_path()
	{
		$original_noimage_filename  = conf('upload.types.img.noimage_filename');
		$original_noimage_file_path = sprintf('%sassets/img/site/%s', PRJ_PUBLIC_DIR, $original_noimage_filename);
		if (!$this->file_cate) return $original_noimage_file_path;
		if ($this->size == 'raw') return $original_noimage_file_path;

		$noimage_filename  = $this->file_cate.'_'.$original_noimage_filename;
		$original_noimage_file_path = sprintf('%sassets/img/site/%s', PRJ_PUBLIC_DIR, $original_noimage_filename);

		$noimage_file_dir  = sprintf('%simg/%s/%s/all', PRJ_UPLOAD_DIR, $this->size, $this->file_cate);
		$noimage_file_path = $noimage_file_dir.'/'.$noimage_filename;
		if (!file_exists($noimage_file_path))
		{
			$this->make_image($original_noimage_file_path, $noimage_file_dir, $noimage_filename);
		}

		return $noimage_file_path;
	}

	public function get_image()
	{
		if ($this->is_noimage) return $this->get_noimage();

		$original_file_path = Site_Upload::get_uploaded_file_real_path($this->filepath, $this->filename);
		if ($this->size == 'raw')
		{
			return file_get_contents($original_file_path);
		}

		$base_path = PRJ_UPLOAD_DIR.'img/';
		$target_file_dir = sprintf('%s%s/%s', $base_path, $this->size, $this->filepath);
		$target_file_path = $target_file_dir.$this->filename;
		if (!file_exists($target_file_path))
		{
			$this->make_image($original_file_path, $target_file_dir, $this->filename);
		}

		return file_get_contents($target_file_path);
	}

	private function make_image($original_file_path, $target_file_dir, $target_filename)
	{
		$target_file_path = sprintf('%s/%s', $target_file_dir, $target_filename);
		Site_Upload::check_and_make_uploaded_dir($target_file_dir);
		Util_file::resize($original_file_path, $target_file_path, $this->width, $this->height, $this->resize_type);
	}

	public function get_extension()
	{
		return $this->extension;
	}
}
