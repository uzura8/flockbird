<?php

class Site_FileMaker
{
	private $type = 'img';
	private $strage_type;
	private $is_tmp      = false;
	private $file_cate   = '';
	private $split_num   = '';
	private $file_name    = '';
	private $filepath_prefix = '';
	private $extension   = 'gif';//noimage 画像の拡張子をデフォルト値とする
	private $size        = '';
	private $width       = '';
	private $height      = '';
	private $resize_type = '';
	private $is_nofile  = false;

	public function __construct($config = array())
	{
		$this->setup($config);
	}

	private function setup($config)
	{
		$this->strage_type = conf('upload.storageType');
		if (!$this->set_configs($config))   $this->is_nofile = true;
		if (!$this->check_configs($config)) $this->is_nofile = true;
		$this->filepath_prefix = Site_Upload::get_filepath_prefix($this->file_cate, $this->split_num);
		$this->filename = Site_Upload::convert_filepath2filename($this->filepath_prefix.$this->file_name);
		if (!$this->check_file()) $this->is_nofile = true;
		if (!$this->check_size()) $this->is_nofile = true;
		$this->set_size();
	}

	private function set_configs($config)
	{
		$result = true;
		$necessary_keys = array(
			'file_cate',
			'split_num',
			'size',
			'file_name',
		);
		foreach ($necessary_keys as $key)
		{
			if (!isset($config[$key]))
			{
				$result = false;
				continue;
			}
			$this->$key = $config[$key];
		}
		if (!empty($config['is_tmp'])) $this->is_tmp = true;

		if (!empty($config['type'])) $this->type = $config['type'];
		if (in_array($this->type, array('img', 'file'))) $success = false;

		return $result;
	}

	private function check_configs()
	{
		return $this->check_file_cate() && $this->check_split_num();
	}

	private function check_file_cate()
	{
		$file_cate_keys_accepted = $this->get_accepted_file_cate_list();
		if (!$is_correct = in_array($this->file_cate, $file_cate_keys_accepted))
		{
			$this->file_cate = null;
		}

		return $is_correct;
	}

	private function get_accepted_file_cate_list()
	{
		if ($this->type == 'file')
		{
			$file_cates = array('nw');
		}
		else
		{
			$file_cates = array_keys(conf('upload.types.img.types'));
		}
		if ($this->is_tmp) $file_cates[] = 'au';

		return $file_cates;
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
		if ($this->type == 'img' && $this->file_name == conf('upload.types.img.noimage_filename')) return false;
		if (!$this->check_file_name()) return false;

		$raw_file_path = Site_Upload::get_uploaded_file_path($this->filename, 'raw', $this->type, $this->is_tmp);
		if (!file_exists($raw_file_path))
		{
			if ($this->strage_type == 'normal') return false;

			return (bool)Site_Upload::make_raw_file_from_storage($this->filename, $raw_file_path, $this->strage_type, $this->type);
		}

		return true;
	}

	private function check_size()
	{
		if ($this->type == 'file')
		{
			return $this->size == 'raw';
		}

		if ($this->size == 'raw') return true;
		if ($this->is_tmp) return $this->size == 'thumbnail';

		return true;
	}

	private function set_size()
	{
		if ($this->type == 'file') return;
		if ($this->size == 'raw') return;

		if ($this->is_tmp && $this->size == 'thumbnail')
		{
			$size = conf('upload.types.img.tmp.sizes.thumbnail');
		}
		else
		{
			$this->validate_size();
			$size = $this->size;
		}
		$item = Site_Upload::conv_size_str_to_array($size);
		$this->width       = $item['width'];
		$this->height      = $item['height'];
		$this->resize_type = $item['resize_type'];
	}

	private function validate_size()
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

		if ($this->size == 'thumbnail')
		{
			if (!empty($sizes['thumbnail'])) $this->size = $sizes['thumbnail'];
			return;
		}

		if (!in_array($this->size, $sizes))
		{
			$this->size = $default_size;
			return;
		}
	}

	private function check_file_name()
	{
		if (empty($this->file_name)) return false;

		$ext = Util_file::get_extension_from_filename($this->file_name);
		$accept_formats = Site_Upload::get_accept_format($this->type);
		if (!$accept_formats || !in_array($ext, $accept_formats)) return false;

		$this->extension = $ext;

		return true;
	}

	public function get_file_path()
	{
		if ($this->is_nofile)
		{
			return ($this->type == 'img') ? $this->get_noimage_file_path() : false;
		}

		$original_file_path = Site_Upload::get_uploaded_file_path($this->filename, 'raw', $this->type, $this->is_tmp);
		if ($this->type == 'file' || $this->size == 'raw')
		{
			return $original_file_path;
		}

		//$target_file_path = Site_Upload::get_uploaded_file_path($this->filename, $this->size, $this->type, $this->is_tmp);
		$target_file_dir = Site_Upload::get_uploaded_path($this->size, $this->type, $this->is_tmp).$this->filepath_prefix;
		$target_file_path = $target_file_dir.$this->file_name;
		if (!file_exists($target_file_path))
		{
			$this->make_image($original_file_path, $target_file_dir, $this->file_name);
		}

		return $target_file_path;
	}

	public function get_data()
	{
		return file_get_contents($this->get_file_path());
	}

	private function make_image($original_file_path, $target_file_dir, $target_filename)
	{
		$target_file_path = $target_file_dir.$target_filename;
		Site_Upload::check_and_make_uploaded_dir($target_file_dir);
		Util_file::resize($original_file_path, $target_file_path, $this->width, $this->height, $this->resize_type);
	}

	public function get_extension()
	{
		return $this->extension;
	}

	private function get_noimage_file_path()
	{
		$original_noimage_filename  = conf('upload.types.img.noimage_filename');
		$original_noimage_file_path = sprintf('%sassets/img/site/%s', DOCROOT, $original_noimage_filename);
		if (!$this->file_cate) return $original_noimage_file_path;
		if ($this->size == 'raw') return $original_noimage_file_path;

		$noimage_filename  = $this->file_cate.'_'.$original_noimage_filename;
		$original_noimage_file_path = sprintf('%sassets/img/site/%s', DOCROOT, $original_noimage_filename);

		$noimage_file_dir  = sprintf('%simg/%s/%s/all/', FBD_UPLOAD_DIR, $this->size, $this->file_cate);
		$noimage_file_path = $noimage_file_dir.'/'.$noimage_filename;
		if (!file_exists($noimage_file_path))
		{
			$this->make_image($original_noimage_file_path, $noimage_file_dir, $noimage_filename);
		}

		return $noimage_file_path;
	}
}
