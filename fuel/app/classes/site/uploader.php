<?php

class Site_Uploader
{
	private $raw_file = '';
	private $filename = '';
	private $filepath = '';
	private $raw_image_base_dir_path   = '';
	private $raw_image_dir_path        = '';
	private $cache_image_base_dir_path = '';
	private $tmp_dir_path = '';
	private $sizes        = array();
	private $max_size     = 0;
	private $member_id    = 0;
	private $member_filesize_total = 0;
	private $file_size_limit       = 0;
	private $old_filepath_name     = '';
	private $old_file_size         = 0;

	public function __construct($options = array())
	{
		if (empty($options['filepath'])) throw new FuelException('File path not set.');
		$this->filepath  = $options['filepath'];
		$file_type = $options['file_type'] ?: 'img';
		
		$this->raw_image_base_dir_path = (!empty($options['real_path_raw_file_dir'])) ?
			$options['real_path_raw_file_dir'] : Config::get('site.upload.types.'.$file_type.'.raw_file_path');
		$this->raw_image_dir_path .= $this->raw_image_base_dir_path.$this->filepath;

		$root_path_cache_file_dir = (!empty($options['root_path_cache_file_dir'])) ?
			$options['root_path_cache_file_dir'] : Config::get('site.upload.types.'.$file_type.'.root_path.cache_dir');
		$this->cache_image_base_dir_path = PRJ_PUBLIC_DIR.$root_path_cache_file_dir;

		$this->tmp_dir_path = (!empty($options['tmp_dir_path'])) ? $options['tmp_dir_path'] : APPPATH.'tmp';
		if (!empty($options['sizes'])) $this->sizes = $options['sizes'];
		if (!empty($options['old_filepath_name'])) $this->old_filepath_name = $options['old_filepath_name'];
		if (!empty($options['max_size'])) $this->max_size = $options['max_size'];
		if (!empty($options['member_id'])) $this->member_id = $options['member_id'];
		if (!empty($options['member_filesize_total'])) $this->member_filesize_total = $options['member_filesize_total'];
		if (!empty($options['file_size_limit'])) $this->file_size_limit = Util_string::convert2bytes($options['file_size_limit']);
		if (!empty($options['old_file_size'])) $this->old_file_size = $options['old_file_size'];
	}

	public function execute($file_path = null)
	{
		if (!$file_path) $file_path = $this->upload_file();

		$file = $this->get_file_info($file_path);
		if ($this->member_id && $this->file_size_limit)
		{
			$this->check_filesize_per_member($file['size']);
		}
		$this->filename = sprintf('%s.%s', Util_string::get_unique_id(), $file['ext']);
		$this->save_raw_file($file['save_to'], $file['save_as'], $this->filename);
		$this->raw_file = $this->raw_image_dir_path.$this->filename;
		if ($size = $this->check_and_resize_raw_file($this->raw_file))
		{
			$file['size'] = $size;
		}
		$file['new_filename'] = $this->filename;

		$this->make_thumbnails();// 各サイズの thumbnail を作成
		$this->remove_old_images();// 古い画像の削除

		return $file;
	}

	private function upload_file()
	{
		$options = array('path' => $this->tmp_dir_path);
		Upload::process($options);
		$this->validate();

		Upload::save(0);
		$file = Upload::get_files(0);

		return $file['saved_to'].$file['saved_as'];
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

		if (PRJ_USE_EXIF_DATA)
		{
			$exif = $this->get_exif_data($file_path);
			$file['exif'] = ($exif) ? $exif : array();
		}

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
		if (count(Upload::get_files()) != 1) throw new FuelException('File upload error.');
	}

	private function save_raw_file($original_file_dir, $original_filename, $new_filename)
	{
		$from = $original_file_dir.$original_filename;
		if (!file_exists($from))
		{
			throw new FuelException('File not found.');
		}
		Site_Upload::check_and_make_uploaded_dir($this->raw_image_dir_path);

		$to = $this->raw_image_dir_path.$new_filename;
		if (file_exists($to)) return;

		if (!rename($from, $to)) throw new FuelException('save raw file error.');
	}

	private function check_filesize_per_member($size)
	{
		if ($this->old_file_size) $size -= $this->old_file_size;

		$accept_size = $this->file_size_limit - $this->member_filesize_total;
		if ($size > $accept_size) throw new FuelException('File size is over the limit of the member.');
	}

	private function get_exif_data($file)
	{
		return exif_read_data($file);
	}

	private function check_and_resize_raw_file($file)
	{
		if (!$this->max_size) return false;

		return Site_Upload::check_max_size_and_resize($file, $this->max_size);
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

	private function remove_old_images()
	{
		if (!strlen($this->old_filepath_name)) return;

		self::remove_images($this->old_filepath_name);
	}

	private function remove_images($filepath_name)
	{
		$raw_file = $this->raw_image_base_dir_path.$filename_name;
		Util_file::remove($raw_file);

		foreach ($this->sizes as $size)
		{
			$file = sprintf('%s%s/%s', $this->cache_image_base_dir_path, $size, $filepath_name);
			if (!file_exists($file)) continue;

			Util_file::remove($file);
		}
	}
}
