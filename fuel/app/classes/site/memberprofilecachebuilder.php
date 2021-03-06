<?php

class Site_MemberProfileCacheBuilder
{
	protected $member;
	protected $current_obj;
	protected $save_values = array();

	public function __construct($member = null)
	{
		if ($member)
		{
			if (!($member instanceof \Model_Member)) throw new \FuelException('Memeber object is invalid.');
			$this->member = $member;
		}
	}

	public function save($member_id = null)
	{
		$this->configure_before_save($member_id);
		if (!$values = $this->get_save_values()) return 0;

		return $this->current_obj ? self::update($this->member->id, $values) : self::create($values);
	}

	public function save_for_member($member_id = null)
	{
		$this->configure_before_save($member_id);

		$this->set_save_values_for_member();
		if (!$this->save_values) return 0;

		return $this->current_obj ? self::update($this->member->id, $this->save_values) : self::create($this->save_values);
	}

	public function save_for_member_profile($member_id = null)
	{
		$this->configure_before_save($member_id);

		$member_profiles = \Model_MemberProfile::get4member_id($this->member->id, true);
		foreach ($member_profiles as $member_profile)
		{
			$this->set_save_values_for_member_profile($member_profile);
		}
		if (!$this->save_values) return 0;

		return $this->current_obj ? self::update($this->member->id, $this->save_values) : self::create($this->save_values);
	}

	public function delete_for_member_profile($member_id = null, $profile_id = null)
	{
		$this->configure_before_save($member_id);
		if (!$this->current_obj) return 0;
		if (!$profile_id || !$profile = \Model_Profile::get_one4id($profile_id)) return 0;

		return self::update($member_id, array(
			$profile->name => null,
			$profile->name.'_public_flag' => conf('public_flag.default'),
		));
	}

	protected function configure_before_save($member_id = null)
	{
		if ($member_id) $this->member = Model_Member::get_one4id($member_id);
		if (!$this->member) throw new \FuelException('Memeber object not set.');
		$this->current_obj = self::get_obj4member_id($this->member->id);
	}

	protected function get_save_values()
	{
		$this->save_values = array();

		$this->set_save_values_for_member();

		$member_profiles = \Model_MemberProfile::get4member_id($this->member->id, true);
		foreach ($member_profiles as $member_profile)
		{
			$this->set_save_values_for_member_profile($member_profile);
		}

		return $this->save_values;
	}

	protected function set_save_values_for_member()
	{
		if (!$this->member) throw new \FuelException('Memeber not set.');

		$is_create = !$this->current_obj;
		$cols = $this->get_member_table_columns_for_save();
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
			if ($is_create)
			{
				$this->save_values[$member_profile_cache_col] = $this->member->{$member_col};
			}
			elseif (! is_null($this->member->{$member_col}) && $this->current_obj->{$member_profile_cache_col} !== $this->member->{$member_col})
			{
				$this->save_values[$member_profile_cache_col] = $this->member->{$member_col};
			}
		}

		if ($this->member->birthyear && $this->member->birthdate)
		{
			$birthday = $this->member->birthyear.'-'.$this->member->birthdate;
			$birthday_public_flag = \Site_Util::get_public_flag_min_range(array($this->member->birthyear_public_flag, $this->member->birthdate_public_flag));
			if ($is_create)
			{
				$this->save_values['birthday'] = $birthday;
				$this->save_values['birthday_public_flag'] = $birthday_public_flag;
			}
			else
			{
				if (! is_null($birthday) && $this->current_obj->birthday !== $birthday)
				{
					$this->save_values['birthday'] = $birthday;
				}
				if (! is_null($birthday_public_flag) && $this->current_obj->birthday_public_flag !== $birthday_public_flag)
				{
					$this->save_values['birthday_public_flag'] = $birthday_public_flag;
				}
			}
		}
	}

	protected function set_save_values_for_member_profile(Model_MemberProfile $member_profile)
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
		$col_name_public_flag = $col_name.'_public_flag';

		if ($this->current_obj)
		{
			if ($this->current_obj->{$col_name} !== $value)
			{
				$this->save_values[$col_name] = $value;
			}
			if ($this->current_obj->{$col_name_public_flag} !== $member_profile->public_flag)
			{
				$this->save_values[$col_name_public_flag] = $member_profile->public_flag;
			}
		}
		else
		{
			$this->save_values[$col_name] = $value;
			$this->save_values[$col_name_public_flag] = $member_profile->public_flag;
		}
	}

	protected static function get_obj4member_id($member_id)
	{
		return DB::select()->from('member_profile_cache')->where('member_id', $member_id)->as_object()->execute()->current();
	}

	protected static function create(array $values)
	{
		if (!$values) return 0;

		$values['updated_at'] = \Date::time()->format('mysql');
		list($insert_id, $rows_affected) = \DB::insert('member_profile_cache')->set($values)->execute();

		return $rows_affected;
	}

	protected static function update($member_id, array $values)
	{
		if (!$values) return 0;
		$values['updated_at'] = \Date::time()->format('mysql');

		return \DB::update('member_profile_cache')
				->set($values)
				->where('member_id', '=', $member_id)
				->execute();
	}

	public static function reset_profile_fields()
	{
		if (!$profiles = \Model_Profile::get_all()) return;

		$exist_columns = \DB::list_columns('member_profile_cache');
		$add_columns = array();
		foreach ($profiles as $id => $profile)
		{
			if (isset($exist_columns[$profile->name])) continue;
			// Excluded for multiple selection
			if ($profile->form_type == 'checkbox') continue;

			$columns_name_public_flag = $profile->name.'_public_flag';
			$add_columns[$profile->name] = self::get_field_setting4profile($profile);
			$add_columns[$columns_name_public_flag] = self::get_public_flag_field_setting();
		}
		\DBUtil::add_fields('member_profile_cache', $add_columns);

		self::drop_not_exists_fields4profile($profiles);
	}

	protected static function drop_not_exists_fields4profile($profiles = array())
	{
		if (!$profiles) $profiles = \Model_Profile::get_all();
		if (!$profiles) return;
		if (!$not_exists_fields = self::get_not_exists_fields4profile($profiles)) return;

		\DBUtil::drop_fields('member_profile_cache', $not_exists_fields);
	}

	protected static function get_not_exists_fields4profile($profiles = array())
	{
		if (!$profiles) $profiles = \Model_Profile::get_all();
		if (!$profiles) return;

		$exist_columns = \DB::list_columns('member_profile_cache');
		foreach ($profiles as $id => $profile)
		{
			if (isset($exist_columns[$profile->name]))
			{
				unset($exist_columns[$profile->name]);
			}

			$name_public_flag = $profile->name.'_public_flag';
			if (isset($exist_columns[$name_public_flag]))
			{
				unset($exist_columns[$name_public_flag]);
			}
		}

		// removed not exists columns
		$default_columns = array_merge(self::get_member_table_columns_for_save(), array(
			'member_id',
			'birthday',
			'birthday_public_flag',
			'updated_at',
		));
		foreach ($default_columns as $column)
		{
			unset($exist_columns[$column]);
		}

		return array_keys($exist_columns);
	}

	public static function add_profile_field(\Model_Profile $profile)
	{
		if ($profile->form_type == 'checkbox') return;

		\DBUtil::add_fields('member_profile_cache', self::get_save_fields($profile));
	}

	public static function modify_profile_field(\Model_Profile $profile)
	{
		if ($profile->form_type == 'checkbox')
		{
			self::drop_profile_field($profile->name);
			return;
		}

		$fields = self::get_save_fields($profile);
		if (\DBUtil::field_exists('member_profile_cache', array($profile->name)))
		{
			\DBUtil::modify_fields('member_profile_cache', $fields);
		}
		else
		{
			\DBUtil::add_fields('member_profile_cache', $fields);
			self::drop_not_exists_fields4profile();
		}
	}

	public static function drop_profile_field($profile_field_name)
	{
		$name_public_flag = $profile_field_name.'_public_flag';
		if (!\DBUtil::field_exists('member_profile_cache', array($profile_field_name, $name_public_flag))) return;

		\DBUtil::drop_fields('member_profile_cache', $profile_field_name);
		\DBUtil::drop_fields('member_profile_cache', $name_public_flag);
	}

	protected static function get_save_fields(\Model_Profile $profile)
	{
		$name = $profile->name;
		$name_public_flag = $name.'_public_flag';
		$fields = array(
			$name => self::get_field_setting4profile($profile),
			$name_public_flag => self::get_public_flag_field_setting(),
		);

		return $fields;
	}

	protected static function get_member_table_columns_for_save()
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
}

