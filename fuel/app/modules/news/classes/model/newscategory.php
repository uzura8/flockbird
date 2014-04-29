<?php
namespace News;

class Model_NewsCategory extends \Orm\Model
{
	protected static $_table_name = 'news_category';

	protected static $_properties = array(
		'id',
		'name' => array(
			'data_type' => 'text',
			'label' => 'カテゴリ名',
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

	public static function _init()
	{
		static::$_properties['name']['label'] = term('news.category.name');
	}

	public static function get_all($order_by = null)
	{
		if (empty($order_by)) $order_by = array('sort_order' => 'asc');

		return self::query()->order_by($order_by)->get();
	}
}
