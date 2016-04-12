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
		if (!$profiles = \Model_Profile::get_all()) return;
		self::reset_profile_colmuns($profiles);
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
		$this->cache_values = array();
		\DB::start_transaction();
		list($member, $member_profiles) = $this->get_member_objs();
		$this->save_count += $this->save_member_profile_cache($member, $member_profiles);
		$this->update_status();
		\DB::commit_transaction();
	}

	protected function get_member_objs()
	{
		$member = $this->each_queue;
		$member_profiles = \Model_MemberProfile::get4member_id($member->id, true);

		return array($member, $member_profiles);
	}

	protected function save_member_profile_cache($member, $member_profiles)
	{
		$this->member_id = $member->id;
		if (!$this->member_profile_cache = static::get_member_profile_cache_obj($this->member_id))
		{
			$this->is_create = true;
		}
		$this->set_cache_values($member, $member_profiles);

		return $this->save_cache_values();
	}

	protected function set_cache_values($member, $member_profiles)
	{
		$this->set_cache_values_member($member);
		foreach ($member_profiles as $member_profile)
		{
			$this->set_cache_values_member_profile($member_profile);
		}
	}

	protected function set_cache_values_member(Model_Member $member)
	{
		$cols = $this->get_member_table_columns();
		foreach ($cols as $name)
		{
			switch ($name)
			{
				case 'id':
					$member_col = 'id';
					$member_profile_cache_col = 'member_id';
					break;
				default :
					$member_col = $name;
					$member_profile_cache_col = $name;
					break;
			}
			if ($this->is_create)
			{
				$this->cache_values[$member_profile_cache_col] = $member->{$member_col};
			}
			elseif ($this->member_profile_cache->{$member_profile_cache_col} != $member->{$member_col})
			{
				$this->cache_values[$member_profile_cache_col] = $member->{$member_col};
			}
		}

		if ($member->birthyear && $member->birthdate)
		{
			$birthday = $member->birthyear.'-'.$member->birthdate;
			$birthday_public_flag = \Site_Util::get_public_flag_min_range(array($member->birthyear_public_flag, $member->birthdate_public_flag));
			if ($this->is_create)
			{
				$this->cache_values['birthday'] = $birthday;
				$this->cache_values['birthday_public_flag'] = $birthday_public_flag;
			}
			else
			{
				if ($this->member_profile_cache->birthday != $birthday)
				{
					$this->cache_values['birthday'] = $birthday;
				}
				if ($this->member_profile_cache->birthday_public_flag != $birthday_public_flag)
				{
					$this->cache_values['birthday_public_flag'] = $birthday_public_flag;
				}
			}
		}
	}

	protected function set_cache_values_member_profile(Model_MemberProfile $member_profile)
	{
		switch ($member_profile->profile->form_type)
		{
			case 'select':
			case 'radio':
				$value = $member_profile->profile_option_id;
				break;
			case 'input':
			case 'textarea':
				$value = $member_profile->value;
				break;
			default :
				return;
		}
		$col_name = $member_profile->profile->name;
		$this->cache_values[$col_name] = $value;
		$this->cache_values[$col_name.'_public_flag'] = $member_profile->public_flag;
	}

	protected function save_cache_values()
	{
		$rows_affected = 0;
		if ($this->is_create)
		{
			$this->cache_values['updated_at'] = \Date::time()->format('mysql');
			list($insert_id, $rows_affected) = \DB::insert('member_profile_cache')->set($this->cache_values)->execute();
		}
		elseif ($this->cache_values)
		{
			$this->cache_values['updated_at'] = \Date::time()->format('mysql');
			$rows_affected = \DB::update('member_profile_cache')
					->set($this->cache_values)
					->where('member_id', '=', $this->member_profile_cache->member_id)
					->execute();
		}
		$this->member_profile_cache = static::get_member_profile_cache_obj($this->member_id);

		return $rows_affected;
	}

	protected static function get_member_profile_cache_obj($member_id)
	{
		return DB::select()->from('member_profile_cache')->where('member_id', $member_id)->as_object()->execute()->current();
	}

	protected static function get_member_table_columns()
	{
		$columns = \DB::list_columns('member');
		$ignore_columns = array(
			'group',
			'status',
			'file_name',
			'filesize_total',
			'register_type',
			'login_hash',
			'last_login',
			'previous_login',
			'invite_member_id',
			'updated_at',
		);
		foreach ($ignore_columns as $name)
		{
			if (!isset($columns[$name])) continue;
			unset($columns[$name]);
		}

		return array_keys($columns);
	}

	protected static function reset_profile_colmuns($profiles)
	{
		$exist_columns = \DB::list_columns('member_profile_cache');
		$add_columns = array();
		foreach ($profiles as $id => $profile)
		{
			$columns_name_public_flag = $profile->name.'_public_flag';
			if (isset($exist_columns[$profile->name]))
			{
				// TODO: 存在していても変更のあるカラムはskipしない
				unset($exist_columns[$profile->name], $exist_columns[$columns_name_public_flag]);
				continue;
			}

			// 複数選択項目は対象外
			if ($profile->form_type == 'checkbox') continue;

			$add_columns[$profile->name] = self::get_field_setting4profile($profile);
			$add_columns[$columns_name_public_flag] = self::get_public_flag_field_setting();
		}
		\DBUtil::add_fields('member_profile_cache', $add_columns);

		// removed not exists columns
		$default_columns = array_merge(self::get_member_table_columns(), array(
			'member_id',
			'birthday',
			'birthday_public_flag',
			'updated_at',
		));
		foreach ($default_columns as $column)
		{
			unset($exist_columns[$column]);
		}
		\DBUtil::drop_fields('member_profile_cache', array_keys($exist_columns));
	}

	protected static function get_field_setting4profile(\Model_Profile $profile)
	{
		$null = true;
		switch ($profile->form_type)
		{
			case 'select':
			case 'radio':
				$type = 'int';
				$constraint = 3;// 仮に3桁とする
				return array('constraint' => $constraint, 'type' => $type, 'null' => $null);
			case 'input':
			case 'textarea':
				if ($profile->value_max)
				{
					$type = 'varchar';
					$constraint = $profile->value_max;
					return array('constraint' => $constraint, 'type' => $type, 'null' => $null);
				}
				return array('type' => 'text', 'null' => $null);
		}
	}

	protected static function get_public_flag_field_setting()
	{
		return array('constraint' => 2, 'type' => 'int', 'null' => false, 'default' => 0);
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

