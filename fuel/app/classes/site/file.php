<?php

class Site_File
{
	private $is_saved_db = false;
	private $is_tmp      = false;
	private $file_cate   = '';
	private $split_num   = '';
	private $filename    = '';
	private $filepath    = '';
	private $extension   = '';
	private $size        = '';
	private $is_nofile  = false;

	public function __construct($config = array())
	{
		$this->setup($config);
	}

	private function setup($config)
	{
		$this->is_saved_db = conf('upload.isSaveDb');
		if (!$this->set_configs($config))   $this->is_nofile = true;
		if (!$this->check_configs($config)) $this->is_nofile = true;
		$this->filepath = sprintf('%s/%s/', $this->file_cate, $this->split_num);
		if (!$this->check_file()) $this->is_nofile = true;
		if (!$this->check_size()) $this->is_nofile = true;
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
			if (!isset($config[$key]))
			{
				$result = false;
				continue;
			}
			$this->$key = $config[$key];
		}
		if (!empty($config['is_tmp'])) $this->is_tmp = true;

		return $result;
	}

	private function check_configs()
	{
		return $this->check_file_cate() && $this->check_split_num();
	}

	private function check_file_cate()
	{
		$file_cate_keys_accepted = array('nw');
		if (!$is_correct = in_array($this->file_cate, $file_cate_keys_accepted))
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
		if (!$this->check_filename()) return false;

		$raw_file_path = Site_Upload::get_uploaded_file_real_path($this->filepath, $this->filename, 'raw', 'file', $this->is_tmp);
		$dir_path = Site_Upload::get_uploaded_dir_path($this->filepath, 'raw', 'file', $this->is_tmp);
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
if (1) {
$isActive = 1;
$isExit   = 0;
$isEcho   = 0;
$is_html  = 0;
$isAdd    = 1;
$a = '';
if ($isActive) {$fhoge = "/tmp/test.log";$_type = 'wb';if ($isAdd) $_type = 'a';$fp = fopen($fhoge, $_type);ob_start();if ($is_html) echo '<pre>';
//if () var_dump(__LINE__, $a);// !!!!!!!
//var_dump(__LINE__, $e->getMessage());// !!!!!!!
var_dump(__LINE__, $raw_file_path, $dir_path);// !!!!!!!
if ($is_html) echo '</pre>';$out=ob_get_contents();fwrite( $fp, $out . "\n" );ob_end_clean();fclose( $fp );if ($isEcho) echo $out;if ($isExit) exit;}}
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		if (!file_exists($raw_file_path))
		{
			if (!$this->is_saved_db) return false;

			return (bool)Site_Upload::make_raw_file_from_db($this->filename, $dir_path, $this->is_tmp);
		}

		return true;
	}

	private function check_size()
	{
		return $this->size == 'raw';
	}

	private function check_filename()
	{
		if (empty($this->filename)) return false;

		$ext = Util_file::get_extension_from_filename($this->filename);
		$accept_formats = Site_Upload::get_accept_format('file');
		if (!$accept_formats || !in_array($ext, $accept_formats)) return false;

		$this->extension = $ext;

		return true;
	}

	public function get_file()
	{
		if ($this->is_nofile) return false;

		return file_get_contents(Site_Upload::get_uploaded_file_real_path($this->filepath, $this->filename, 'raw', 'file', $this->is_tmp));
	}

	public function get_extension()
	{
		return $this->extension;
	}
}
