<?php
class Model_Template extends \MyOrm\Model
{
	protected static $_table_name = 'template';
	protected static $_properties = array(
		'id',
		'name' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(64), 'unique' => array('template.name')),
			'form' => array('type' => false),
		),
		'format' => array(
			'validation' => array('trim', 'required', 'max_length' => array(25)),
			'form' => array('type' => false),
		),
		'title' => array(
			'data_type' => 'varchar',
			'label' => 'タイトル',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'body' => array(
			'data_type' => 'varchar',
			'label' => '本文',
			'validation' => array(),
			'form' => array('type' => 'textarea', 'rows' => 15),
		),
		'created_at',
		'updated_at'
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

	public static function get4name($name)
	{
		return self::query()->where('name', $name)->get_one();
	}
}
