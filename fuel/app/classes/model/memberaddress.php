<?php

class Model_MemberAddress extends \MyOrm\Model
{
	protected static $_table_name = 'member_address';
	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		)
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
		),
		'last_name' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'required', 'max_length' => array(50)),
			'form' => array('type' => 'text'),
		),
		'first_name' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'required', 'max_length' => array(50)),
			'form' => array('type' => 'text'),
		),
		'last_name_phonetic' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'max_length' => array(50)),
			'form' => array('type' => 'text'),
		),
		'first_name_phonetic' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'max_length' => array(50)),
			'form' => array('type' => 'text'),
		),
		'company_name' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'max_length' => array(100)),
			'form' => array('type' => 'text'),
		),
		'country_id' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => false),
		),
		'postal_code' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'required', 'max_length' => array(20), 'valid_string' => array('numeric', 'dashes')),
			'validation' => array('trim', 'required', 'max_length' => array(20), 'match_pattern' => array('/^[0-9]+[0-9\-]{1,18}[0-9]+$/')),
			'form' => array('type' => 'text'),
		),
		'region' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => false),
		),
		'address01' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'required', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'address02' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'phone01' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'required', 'max_length' => array(20), 'match_pattern' => array('/^\+?[0-9]+[0-9\-]{6,17}[0-9]+$/')),
			'form' => array('type' => 'text'),
		),
		'phone02' => array(
			'data_type' => 'text',
			'validation' => array('trim', 'max_length' => array(20), 'match_pattern' => array('/^\+?[0-9]+[0-9\-]{6,17}[0-9]+$/')),
			'form' => array('type' => false),
		),
		'description' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
			'form' => array('type' => false),
		),
		'type' => array(
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
		static::$_properties['last_name']['label'] = term('member.address.last_name');
		static::$_properties['first_name']['label'] = term('member.address.first_name');
		static::$_properties['last_name_phonetic']['label'] = term('member.address.last_name_phonetic');
		static::$_properties['first_name_phonetic']['label'] = term('member.address.first_name_phonetic');
		static::$_properties['company_name']['label'] = term('member.address.company_name');
		static::$_properties['country_id']['label'] = term('member.address.country_id');
		static::$_properties['postal_code']['label'] = term('member.address.postal_code');
		static::$_properties['region']['label'] = term('member.address.region');
		static::$_properties['address01']['label'] = term('member.address.address01');
		static::$_properties['address02']['label'] = term('member.address.address02');
		static::$_properties['phone01']['label'] = term('member.address.phone01');
		static::$_properties['phone02']['label'] = term('member.address.phone02');
		static::$_properties['description']['label'] = term('member.address.description');

		static::$_properties['type']['label'] = term('member.address.type.view');
		static::$_properties['type']['enum_values'] = conf('address.type.options', 'member');
		static::$_properties['type']['validation']['in_array'][] = array_values(static::$_properties['type']['enum_values']);
	}

	public static function get_one_main($member_id)
	{
		return static::query()
			->where('member_id', $member_id)
			->where('type', static::get_enum_value4key('type', 'main'))
			->get_one();
	}

	public static function get_optionals($member_id)
	{
		return static::query()
			->where('member_id', $member_id)
			->where('type', static::get_enum_value4key('type', 'optional'))
			->order_by('created_at', 'asc')
			->get();
	}
}
