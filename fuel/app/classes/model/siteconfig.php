<?php
class Model_SiteConfig extends \MyOrm\Model
{
	protected static $_table_name = 'site_config';
	protected static $_properties = array(
		'id',
		'name' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(64), 'unique' => array('site_config.name')),
			'form' => array('type' => false),
		),
		'value' => array(
			'data_type' => 'varchar',
			'validation' => array(),
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

	protected static $values = array();

	private static function set_values_as_assoc()
	{
		$objs = self::query()->get();
		self::$values = Util_Orm::conv_cols2assoc($objs, 'name', 'value');
	}

	public static function get_valueas_assoc()
	{
		if (empty(self::$values)) self::set_values_as_assoc();

		return self::$values;
	}

	public static function get_value4name_as_assoc($name)
	{
		if (empty(self::$values)) self::set_values_as_assoc();
		if (!empty(self::$values[$name])) return self::$values[$name];

		return false;
	}

	public static function get4names_as_assoc(array $names, $remove_prefix = null)
	{
		if (empty(self::$values)) self::set_values_as_assoc();
		if (empty(self::$values)) return array();

		$return = array();
		foreach (self::$values as $name => $value)
		{
			if (!in_array($name, $names)) continue;
			if ($remove_prefix) $name = str_replace($remove_prefix.'_', '', $name);
			$return[$name] = $value;
		}

		return $return;
	}

	public static function get4name($name)
	{
		return self::query()->where('name', $name)->get_one();
	}
}
