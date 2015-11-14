<?php

abstract class Site_BatchHandler
{
	protected $task_name = '';
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
		$this->output_start_message();
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
				}
				unset($queues[$key]);
			}
			if (\DB::in_transaction()) \DB::commit_transaction();
			$this->loop_count++;
			$this->output_progress();
			\Cli::wait($this->options['sleep_time'], true);
		}
		unset($queues);
		$this->output_end_message();

		return $this->get_result();
	}

	protected function reset_each_status()
	{
		$this->each_result = null;
		$this->each_error_message = '';
	}

	protected function output_start_message()
	{
		\Cli::write(sprintf('start %s', $this->task_name));
	}

	protected function output_end_message()
	{
		\Cli::write(sprintf('end %s', $this->task_name));
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

	abstract protected function get_queues();
	abstract protected function execute_each();
	abstract protected function get_result();
}

