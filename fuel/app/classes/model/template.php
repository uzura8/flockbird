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
		'lang' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(5)),
			'form' => array('type' => 'select'),
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

	public static function _init()
	{
		$lang_options = conf('lang.options', 'i18n');
		static::$_properties['lang']['form']['options'] = $lang_options;
		static::$_properties['lang']['validation']['in_array'][] = array_keys($lang_options);
	}

	public static function get_one4name_lang($name, $lang)
	{
		return self::query()
			->where('name', $name)
			->where('lang', $lang)
			->get_one();
	}

	public static function get4lang($lang)
	{
		return self::query()
			->where('lang', $lang)
			->get();
	}
}
