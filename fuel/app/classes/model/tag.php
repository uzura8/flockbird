<?php
class Model_Tag extends \MyOrm\Model
{
	protected static $_table_name = 'tag';

	protected static $_properties = array(
		'id',
		'name' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(128)),
			'form' => array('type' => 'select', 'multiple' => 'multiple'),
		),
		'created_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
	);

	public static function _init()
	{
		static::$_properties['name']['label'] = term('site.tag');
	}

	public static function get_one4name($name)
	{
		return self::query()
			->where('name', $name)
			->get_one();
	}

	public static function get4names($names)
	{
		return self::query()
			->where('name', 'in', $names)
			->get();
	}
}

