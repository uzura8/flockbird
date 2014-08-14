<?php
class Model_OauthProvider extends \Orm\Model
{
	protected static $_table_name = 'oauth_provider';
	protected static $_properties = array(
		'id',
		'name' => array(
			'data_type' => 'varchar',
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(50),
			),
		),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
	);

	protected static $id_list = array();

	public static function get_id($name)
	{
		if (!empty(self::$id_list[$name])) return self::$id_list[$name];

		self::$id_list[$name] = '';
		if ($row = self::query()->where('name', $name)->get_one())
		{
			self::$id_list[$name] = $row->id;
		}

		return self::$id_list[$name];
	}
}
