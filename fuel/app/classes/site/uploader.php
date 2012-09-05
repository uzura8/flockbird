<?php

class Site_uploader
{
	private $base_path = '';
	private $file_path = '';
	private $prefix = '';
	private $tmp_dir_path = '';
	private $saved_raw_image_dir_name = 'raw';
	private $saved_raw_image_dir_path = '';
	private $sizes         = array();
	private $old_filename  = '';
	private $max_size      = 0;
	private $resize        = 0;
	private $resize_type   = 'relative';
	private $max_file_size = null;
	private $member_id     = 0;
	private $member_filesize_total = 0;
	private $old_file_size = 0;
	private $file_size_limit = 0;
	public  $new_filename  = '';

	public function __construct($options = array())
	{
		$this->base_path = (!empty($options['base_path'])) ? $options['base_path'] : 'img/general';
		$this->file_path = sprintf('%s/%s', PRJ_UPLOAD_DIR, $this->base_path );
		$this->saved_raw_image_dir_name = (!empty($options['saved_raw_image_dir_name'])) ? $options['saved_raw_image_dir_name'] : 'raw';
		$this->tmp_dir_path = (!empty($options['tmp_dir_path'])) ? $options['tmp_dir_path'] : APPPATH.'tmp';
		if (!empty($options['prefix'])) $this->prefix = $options['prefix'];
		if (!empty($options['sizes']))  $this->sizes  = $options['sizes'];
		if (!empty($options['old_filename'])) $this->old_filename = $options['old_filename'];
		if (!empty($options['max_size'])) $this->max_size = $options['max_size'];
		if (!empty($options['resize_type'])) $this->resize_type = $options['resize_type'];
		if (!empty($options['member_id'])) $this->member_id = $options['member_id'];
		if (!empty($options['member_filesize_total'])) $this->member_filesize_total = $options['member_filesize_total'];
		if (!empty($options['file_size_limit'])) $this->file_size_limit = Util_string::convert2bytes($options['file_size_limit']);
		if (!empty($options['old_file_size'])) $this->old_file_size = $options['old_file_size'];
		$this->saved_raw_image_dir_path = sprintf('%s/%s/', $this->file_path, $this->saved_raw_image_dir_name);
	}

	private function validate()
	{
		if (!Upload::is_valid())
		{
			$errors = Upload::get_errors();
			if (!empty($errors[0]['errors'][0]['message']))
			{
				throw new Exception($errors[0]['errors'][0]['message']);
			}
		}
		if (count(Upload::get_files()) != 1) throw new Exception('File upload error.');
	}

	private function check_filesize_per_member($size)
	{
		if ($this->old_file_size) $size -= $this->old_file_size;

		$accept_size = $this->file_size_limit - $this->member_filesize_total;
		if ($size > $accept_size) throw new Exception('File size is over the limit of the member.');
	}

	public function upload($sizes = array(), $old_image = '')
	{
		$options = array(
			'path'   => $this->tmp_dir_path,
			'prefix' => $this->prefix,
		);
		Upload::process($options);
		$this->validate();

		$file = Upload::get_files(0);
		if ($this->member_id && $this->file_size_limit)
		{
			$this->check_filesize_per_member($file['size']);
		}

		Upload::save(0);
		$file = Upload::get_files(0);

		/**
		 * ここで$fileを使ってアップロード後の処理
		 * $fileの中にいろんな情報が入っている
		 **/
		$ext = pathinfo($file['saved_as'], PATHINFO_EXTENSION);
		$filename = sprintf('%s%s.%s', $this->prefix, Util_string::get_unique_id(), $ext);
		$this->save_raw_file($file['saved_to'], $file['saved_as'], $filename, $file['size']);
		if ($size = $this->check_and_resize_raw_file($filename))
		{
			$file['size'] = $size;
		}

		// 各サイズの thumbnail を作成
		$this->make_thumbnails($this->saved_raw_image_dir_path, $filename);

		// 古い画像の削除
		$this->remove_old_images();

		$file['new_filename'] = $filename;

		return $file;
	}

	private function check_and_resize_raw_file($filename)
	{
		if (!$this->max_size) return false;

		$file = $this->saved_raw_image_dir_path.$filename;
		$sizes = Image::sizes($file);

		return Site_util::check_max_size_and_resize($file, $this->max_size, $sizes->width, $sizes->height, $this->resize_type);
	}

	private function save_raw_file($original_file_dir, $original_filename, $new_filename)
	{
		$from = $original_file_dir.$original_filename;
		if (!file_exists($from))
		{
			throw new Exception('File not found.');
		}
		if (!file_exists($this->saved_raw_image_dir_path) && $target_path = Util_file::check_exists_file_path($this->saved_raw_image_dir_path, 4))
		{
			Util_file::make_dir_recursive($this->saved_raw_image_dir_path);
			Util_file::chmod_recursive($target_path, 0777);
		}

		$to = $this->saved_raw_image_dir_path.$new_filename;
		if (file_exists($to)) return;

		if (!rename($from, $to)) throw new Exception('save raw file error.');
	}

	private function make_thumbnails($original_file_dir, $original_filename)
	{
		$original_file = $original_file_dir.$original_filename;

		foreach ($this->sizes as $size)
		{
			if ($size == $this->saved_raw_image_dir_name) continue;

			$dir = sprintf('%s/%s', $this->file_path, $size);
			if (!file_exists($dir) && $target_path = Util_file::check_exists_file_path($dir, 4))
			{
				Util_file::make_dir_recursive($dir);
				Util_file::chmod_recursive($target_path, 0777);
			}

			$path = sprintf('%s/%s', $dir, $original_filename);
			list($width, $height) = explode('x', $size);
			Util_file::resize($original_file, $path, $width, $height, $this->resize_type);
		}
	}

	private function remove_old_images()
	{
		if (!strlen($this->old_filename)) return;

		foreach ($this->sizes as $size)
		{
			$file = sprintf('%s/%s/%s', $this->file_path, $size, $this->old_filename);
			if (!file_exists($file)) continue;

			Util_file::remove($file);
		}
	}
}
