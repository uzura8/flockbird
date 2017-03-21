<?php

class Model_MemberProfile extends \MyOrm\Model
{
	protected static $_table_name = 'member_profile';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
		'profile' => array(
			'key_from' => 'profile_id',
			'model_to' => 'Model_Profile',
			'key_to' => 'id',
		),
		'profile_option' => array(
			'key_from' => 'profile_option_id',
			'model_to' => 'Model_ProfileOption',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'member_id' => array('form' => array('type' => false)),
		'profile_id' => array('form' => array('type' => false)),
		'profile_option_id' => array('form' => array('type' => false)),
		'value',
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
	);

	public static function _init()
	{
		static::$_properties['member_id'] = Util_Orm::get_relational_numeric_key_prop();
		static::$_properties['profile_id'] = Util_Orm::get_relational_numeric_key_prop();
		static::$_properties['profile_option_id'] = Util_Orm::get_relational_numeric_key_prop(false);
		static::$_properties['public_flag']['validation']['in_array'][] = Site_Util::get_public_flags();

		if (conf('profile.useCacheTable.isEnabled', 'member'))
		{
      // Update related member_profile_cache
			static::$_observers['MyOrm\Observer_UpdateMemberProfileCache'] = array(
				'events' => array('after_save', 'after_delete'),
			);
		}
	}

	public static function get4member_id($member_id, $with_relations = false, $profile_display_type_str = null)
	{
		$query = self::query()->where('member_id', $member_id);
		if ($profile_display_type_str && $profile_display_type = conf('member.profile.display_type.'.$profile_display_type_str))
		{
			$profile_ids = Model_Profile::get_ids4display_type($profile_display_type);
			if ($profile_ids) $query = $query->where('profile_id', 'in', $profile_ids);
		}
		if ($with_relations)
		{
			$query = $query->related(array('profile', 'profile_option'))->order_by('profile.sort_order');
		}

		return $query->get();
	}

	public static function get_member_ids4profile_id_option_ids($profile_id, $option_ids = array(), $public_flags = null)
	{
		if (!$option_ids) return array();

		if (is_null($public_flags)) $public_flags = array($public_flags);
		$query = self::query()->select('member_id')
			->where('profile_id', $profile_id)
			->where('profile_option_id', 'in', $option_ids);

		if ($public_flags)
		{
			if (count($public_flags) == 1)
			{
				$query->where('public_flag', $public_flags);
			}
			else
			{
				$query->where('public_flag', 'in', $public_flags);
			}
		}

		return \Util_Orm::conv_col2array($query->get(), 'member_id');
	}
}

