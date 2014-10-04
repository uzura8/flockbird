<?php
namespace Notice;

class Model_Notice extends \MyOrm\Model
{
	protected static $_table_name = 'notice';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'is_read' => array(
			'data_type' => 'integer',
			'default' => 0,
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0,1))),
			'form' => array('type' => false),
		),
		'type' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(2)),
			'form' => array('type' => false),
		),
		'foreign_table' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(20)),
			'form' => array('type' => false),
		),
		'foreign_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '本文',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'),
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
		'MyOrm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
		),
	);

	public static function _init()
	{
		static::$_properties['type']['validation']['in_array'][] = \Config::get('notice.types');
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_accept_timeline_foreign_tables();
	}

	public static function get4last_foreign_data($foreign_table, $foreign_id, $since_datetime = null)
	{
		$query = self::query()
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id);

		if ($since_datetime) $query = $query->where('created_at', '>', $since_datetime);

		return $query->order_by('created_at', 'desc')
			->rows_limit(1)
			->get_one();
	}
}
