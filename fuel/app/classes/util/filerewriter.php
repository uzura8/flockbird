<?php

class Util_FileRewriter
{
	protected static $basepath;
	protected $file_path;
	protected $lines = array();
	//protected $options = array();

	public function __construct($file_path, $options = array())
	{
		static::$basepath = FBD_BASEPATH;
		//$this->options = array(
		//);
		//if (!is_array($options)) $options = (array)$options;
		//if ($options) $this->options = $options + $this->options;

		$this->set_filepath($file_path);
		$this->read_file();
	}

	protected function set_filepath($file_path)
	{
		if (!static::check_full_path($file_path))
		{
			$file_path = static::$basepath.trim($file_path, '/');
		}
		if (!file_exists($file_path))
		{
			throw new FuelException('File not exists.');
		}

		$this->file_path = $file_path;
	}

	protected function read_file()
	{
		if (!$this->lines = explode(PHP_EOL, file_get_contents($this->file_path)))
		{
			throw new FuelException('File is empty.');
		}
	}

	protected static function check_full_path($file_path)
	{
		$basepath_length = strlen(static::$basepath);
		if (strlen($file_path) < $basepath_length) return false;
		if (substr($file_path, 0, $basepath_length) === static::$basepath) return true;

		return false;
	}

	public function replace_lines($pattern, $replacement, $is_exit_first_matching = false)
	{
		$is_replaced = false;
		foreach ($this->lines as $index => $line)
		{
			$replaced_line = preg_replace($pattern, $replacement, $line);
			if ($replaced_line == $line) continue;

			$this->lines[$index] = $replaced_line;
			$is_replaced = true;
			if ($is_exit_first_matching) break;
		}

		if (!$is_replaced) return 0;

		return $this->output_file();
	}

	protected function output_file()
	{
		return file_put_contents($this->file_path, implode(PHP_EOL, $this->lines));
	}
}
