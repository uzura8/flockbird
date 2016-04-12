<?php

class Site_SetupMemberProfileCache extends Site_BatchHandler
{
	protected $offset = 0;
	protected $status_flags = array();
	protected $member_id;
	protected $member_profile_cache;
	protected $is_create = false;
	protected $cache_values = array();
	protected $save_count = 0;

	public function __construct($options = array())
	{
		parent::__construct($options);
		//$this->options = $this->options + array(
		//	'queues_limit' => conf('default.limit.sendMail', 'task'),
		//);
		$this->status_flags = conf('default.statusFlags', 'task');
	}

	protected function execute_pre()
	{
		\Site_MemberProfileCacheBuilder::reset_profile_colmuns();
	}

	protected function get_queues()
	{
		$queues = \Model_Member::get_all(
			null,
			null,
			array('id' => 'ASC'),
			$this->options['queues_limit'],
			$this->offset,
			null,
			$this->max_count ? false : true
		);
		$this->offset += $this->options['queues_limit'];
		if ($this->max_count) return $queues;

		$this->set_max_count($queues[1]);

		return $queues[0];
	}

	protected function execute_each()
	{
		$record_builder = new Site_MemberProfileCacheBuilder($this->each_queue);
		\DB::start_transaction();
		$this->save_count += $record_builder->save();
		$this->update_status();
		\DB::commit_transaction();
	}

	protected function get_status_value($key)
	{
		if (!isset($this->status_flags[$key])) throw new InvalidArgumentException('Parameter is invalid.');
		return $this->status_flags[$key];
	}

	protected function update_status()
	{
		if (is_null($this->each_result))
		{
			$this->each_queue->status = $this->get_status_value('unexecuted');
		}
		else
		{
			$this->each_queue->status = $this->each_result;
		}
		if ($this->each_error_message) $this->each_queue->result_message = $this->each_error_message;
		$this->each_queue->save();
	}

	protected function get_result()
	{
		return $this->save_count;
	}

	protected function execute_post() {}
}

