<?php
namespace Message;

class Model_MessageRecievedSummary extends \MyOrm\Model
{
	protected static $_table_name = 'message_recieved_summary';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		)
	);
	protected static $_has_one = array(
		'message' => array(
			'key_from' => 'last_message_id',
			'model_to' => '\Message\Model_Message',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'type' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(2)),
			'form' => array('type' => false),
		),
		'type_related_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'last_message_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'is_read' => array(
			'data_type' => 'integer',
			'default' => 0,
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
		'last_sent_at' => array('form' => array('type' => false)),
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

	protected static $_to_array_exclude = array();

	public static function _init()
	{
		static::$_properties['type']['validation']['in_array'][] = Site_Util::get_types(true);
	}

	public static function get_one4member_id_and_type_and_related_id($member_id, $type, $type_related_id)
	{
		return self::get_one4conditions(array(
			'member_id' => $member_id,
			'type' => $type,
			'type_related_id' => $type_related_id,
		));
	}

	public static function save_at_sent($member_id, $message_id, $type, $type_related_id = 0, $datetime = null)
	{
		if ($obj = self::get_one4member_id_and_type_and_related_id($member_id, $type, $type_related_id))
		{
			if ($obj->last_message_id && $obj->last_message_id == $message_id) return;
		}
		else
		{
			$obj = Model_MessageRecievedSummary::forge(array(
				'member_id' => $member_id,
				'type' => $type,
				'type_related_id' => $type_related_id,
			));
		}

		$obj->last_message_id = $message_id;
		if ($datetime) $obj->last_sent_at = $datetime;
		$obj->save();

		return $obj;
	}
}

