<?php
class Site_BatchInvalidOptionException extends FuelException {}
class Site_BatchAlreadyRunningException extends FuelException {}

abstract class Site_BatchHandler
{
	protected $options = array();
	protected $loop_count = 0;
	protected $max_count = 0;
	protected $each_queue;
	protected $each_result = false;
	protected $each_error_message = '';

	public function __construct($options = array())
	{
		$this->options = array(
			'is_admin' => false,
			'loop_max' => conf('default.loopMax', 'task'),
			'sleep_time' => conf('default.sleepTime', 'task'),
			'queues_limit' => conf('default.limit.default', 'task'),
		);
		$this->setup_options($options);
	}

	protected function setup_options($options)
	{
		if ($options) $this->options = $options + $this->options;
	}

	public function execute()
	{
		$this->check_required_options();
		$this->update_running_flag(true);
		$this->output_start_message();
		$this->execute_pre();
		while ($this->loop_count < $this->options['loop_max'])
		{
			if (!$queues = $this->get_queues()) break;

			foreach ($queues as $key => $queue)
			{
				$this->each_queue = $queue;
				unset($queue);
				try
				{
					$this->reset_each_status();
					$this->execute_each();
				}
				catch(\Exception $e)
				{
					if (\DB::in_transaction()) \DB::rollback_transaction();
					if (!empty($e)) Util_Toolkit::log_error(is_prod_env() ? $e->getMessage() : $e->__toString());
				}
				unset($queues[$key]);
			}
			if (\DB::in_transaction()) \DB::commit_transaction();
			$this->loop_count++;
			$this->output_progress();
			\Cli::wait($this->options['sleep_time'], true);
		}
		unset($queues);
		$this->execute_post();
		$this->output_end_message();
		$this->update_running_flag(false);

		return $this->get_result();
	}

	public function update_running_flag_off()
	{
		return $this->update_running_flag(false);
	}

	abstract protected function get_queues();
	abstract protected function execute_each();
	abstract protected function get_result();
	abstract protected function execute_pre();
	abstract protected function execute_post();

	protected static function get_required_options()
	{
		return array(
			'task_name',
		);
	}

	protected function check_required_options()
	{
		if (!$required_options = static::get_required_options()) return;
		foreach ($required_options as $key)
		{
			if (!isset($this->options[$key]))
			{
				throw new Site_BatchInvalidOptionException(sprintf('option %s is not set', $key));
			}
		}
	}

	protected function reset_each_status()
	{
		$this->each_result = null;
		$this->each_error_message = '';
	}

	protected function output_start_message()
	{
		\Cli::write(sprintf('start %s', \Util_String::camelcase2ceparated($this->options['task_name'])));
	}

	protected function output_end_message()
	{
		\Cli::write(sprintf('end %s', \Util_String::camelcase2ceparated($this->options['task_name'])));
	}

	protected function output_progress()
	{
		\Cli::write(sprintf('progress: %d / %d', $this->loop_count, $this->max_count));
	}

	protected function set_max_count($all_queues_count)
	{
		$max_count = $all_queues_count / $this->options['queues_limit'];
		if ($max_count == floor($max_count))
		{
			$this->max_count = $max_count;
		}
		else
		{
			$this->max_count = floor($max_count) + 1;
		}
		if ($this->max_count > $this->options['loop_max']) $this->max_count = $this->options['loop_max'];
	}

	protected function update_running_flag($is_start = true)
	{
		if ($is_start && $this->check_is_running())
		{
			throw new Site_BatchAlreadyRunningException('Another task is running.');
		}

		$site_config_name = static::get_site_config_name($this->options['task_name']);
		if (!$site_config = \Model_SiteConfig::get4name($site_config_name))
		{
			$site_config = \Model_SiteConfig::forge();
			$site_config->name = $site_config_name;
		}
		$site_config->value = $is_start ? 1 : 0;

		return $site_config->save();
	}

	protected function check_is_running()
	{
		$site_config_name = static::get_site_config_name($this->options['task_name']);
		if (!$site_config = \Model_SiteConfig::get4name($site_config_name)) return false;

		$enabled_priod = !empty($this->options['enabled_priod']) ? $this->options['enabled_priod'] : 0;
		if (!$enabled_priod) $enabled_priod = conf(sprintf('%s.runningFlag.enabledPriod', $this->options['task_name']), 'task');
		if (!$enabled_priod) $enabled_priod = conf('default.runningFlag.enabledPriod', 'task');
		if ($enabled_priod)
		{
			if (Util_Date::check_is_passed($site_config->updated_at, $enabled_priod, null, false))
			{
				$site_config->value = 0;
				$site_config->save();
			}
		}

		return (bool)$site_config->value;
	}

	protected static function get_site_config_name($task_name)
	{
		return sprintf('task_running_%s', $task_name);
	}
}

