<?php
namespace Notice;

class Model_MemberWatchContent extends \MyOrm\Model
{
	protected static $_table_name = 'member_watch_content';

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
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'foreign_table' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(20)),
			'form' => array('type' => false),
		),
		'foreign_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
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
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_accept_foreign_tables();
	}

	public static function check_and_create($member_id, $foreign_table, $foreign_id)
	{
		if (!$obj = self::get4member_id_and_foreign_data($member_id, $foreign_table, $foreign_id))
		{
			$obj = self::forge(array(
				'member_id' => $member_id,
				'foreign_table' => $foreign_table,
				'foreign_id' => $foreign_id,
			));
			$obj->save();
		}

		return $obj;
	}

	public static function get4member_id_and_foreign_data($member_id, $foreign_table, $foreign_id)
	{
		return self::query()
			->where('member_id', $member_id)
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id)
			->get_one();
	}
}
