<?php

class Util_CsvBulkHandler
{
	protected $options = array();

	public function __construct($options = array())
	{
		Config::load('task', 'task');
		$this->options = array(
			'is_update_all' => false,
			'save_tables' => array(),
			'loop_max' => conf('default.loopMax', 'task'),
			'sleep_time' => conf('default.sleepTime', 'task'),
			'queues_limit' => conf('default.limit.default', 'task'),
			'columns' => array(),
		);
		$this->setup_options($options);
	}

	protected function setup_options($options)
	{
		if ($options) $this->options = $options + $this->options;
	}

	public function make_file($data = array(), $is_add_title_row = true)
	{
		$data = static::convert2output_array($data, array_keys($this->options['columns']));
		if (! $csv_data = \Format::forge($data)->to_csv()) return false;

		$file_path = static::get_randam_file_path();
		file_put_contents($file_path, $csv_data);

		return $file_path;
	}

	public function bulk_save($file_path)
	{
		//$this->output_start_message();
		$data = static::get_file_data($file_path);
		$data = \Format::forge($data, 'csv')->to_array();
		$result_ids = array();
		\DB::start_transaction();
		if (! empty($this->options['is_update_all'])) $this->truncate_tables();
		foreach($data as $row)
		{
			if ($id = $this->save_record($row)) $result_ids[] = $id;
		}
		\DB::commit_transaction();
		unset($data);
		//$this->output_end_message();

		return $result_ids;
	}

	protected function get_file_data($file_path)
	{
		if (!$data = static::get_and_format_file_contents($file_path)) throw new \FuelException('File is empty');

		return $data;
	}

	protected function get_and_format_file_contents($file_path)
	{
		if (!$data = file_get_contents($file_path)) throw new \FuelException('File is empty');
		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\r", "\n", $data);
		//$data = mb_convert_encoding($data, 'UTF-8', "JIS, sjis-win, eucjp-win");
		file_put_contents($file_path, $data);
		$file_path = static::format($file_path);
		if (!$data = file_get_contents($file_path)) throw new \FuelException('File is empty');

		return $data;
	}

	protected function format($original_file_path)
	{
		if (!file_exists($original_file_path)) throw new \FuelException('File is not exists');
		$original = fopen($original_file_path, 'r');

		$formated_file_path = static::get_randam_file_path();
		$formated = fopen($formated_file_path, 'w');
		if (!$original || !$formated) throw new \FuelException('Disabled to open file');

		for ($i = 0; !feof($original); $i++)
		{
			$line = fgets($original);
			if ($i == 0)
			{
				$line = $this->get_title_row();
			}
			fputs($formated, $line);
		}
		fclose($original);
		fclose($formated);

		return $formated_file_path;
	}

	protected function get_title_row()
	{
		$line = '';
		foreach ($this->options['columns'] as $key => $label)
		{
			if ($line) $line .= ',';
			$line .= sprintf('"%s"', $label);
		}
		$line .= PHP_EOL;

		return $line;
	}

	protected function save_record($row)
	{
	}

	protected function truncate_tables()
	{
		if (empty($this->options['is_update_all'])) return false;
		if (empty($this->options['save_tables'])) return false;
		if (! is_array($this->options['save_tables'])) $this->options['save_tables'] = (array)$this->options['save_tables'];

		foreach ($this->options['save_tables'] as $save_table)
		{
			DB::query(sprintf('TRUNCATE `%s`', $save_table))->execute();
		}
	}

	protected function get_row_value($prop, $row)
	{
		$key = $this->options['columns'][$prop];

		return $row[$key];
	}

	protected static function convert2output_array($objs = array(), $accept_props = array())
	{
		if (empty($objs) || ! is_array($objs)) return $objs;

		foreach ($objs as $i => $obj)
		{
			$row = $obj->to_array();
			if ($accept_props)
			{
				foreach ($row as $prop => $value)
				{
					if (in_array($prop, $accept_props)) continue;
					unset($row[$prop]);
				}
			}

			$objs[$i] = $row;
		}

		return $objs;
	}

	protected static function get_randam_file_path()
	{
		return sprintf('%stmp/%s.csv', APPPATH, \Util_String::get_random());
	}
}

