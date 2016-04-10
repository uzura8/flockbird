<?php

class Model_ProfileOption extends \MyOrm\Model
{
	protected static $_table_name = 'profile_option';

	protected static $_belongs_to = array(
		'profile' => array(
			'key_from' => 'profile_id',
			'model_to' => 'Model_Profile',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'profile_id',
		'label' => array(
			'data_type' => 'text',
			'label' => '項目名',
			'validation' => array('trim', 'required'),
			'form' => array('type' => 'text'),
		),
		'sort_order' => array(
			'data_type' => 'integer',
			'validation' => array('valid_string' => array('numeric')),
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

	public static function get4profile_id($profile_id)
	{
		return self::query()
			->where('profile_id', $profile_id)
			->order_by('sort_order')
			->get();
	}
}
