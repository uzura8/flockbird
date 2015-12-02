<?php
namespace Message;

class Model_MessageRecieved extends \MyOrm\Model
{
	protected static $_table_name = 'message_recieved';

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
			'key_from' => 'message_id',
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
		'message_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'is_read' => array(
			'data_type' => 'integer',
			'default' => 0,
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'is_deleted' => array(
			'data_type' => 'integer',
			'default' => 0,
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0, 1))),
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
		// Add mail_queue record at inserted
		'MyOrm\Observer_InsertRelationialTable' => array(
			'events'   => array('after_insert'),
			'model_to' => '\Message\Model_MessageRecievedMailQueue',
			'properties' => array(
				'message_recieved_id' => 'id',
				'member_id',
			),
		),
		// Delete relaton table record on updated
		'MyOrm\Observer_DeleteRelationalTablesOnUpdated' => array(
			'events' => array('after_update'),
			'relations' => array(
				'model_to' => '\Message\Model_MessageRecievedMailQueue',
				'conditions' => array(
					'message_recieved_id' => array('id' => 'property'),
				),
				'check_changed' => array(
					'check_properties' => array(
						'is_read' => array(
							'value' => 1,
						),
					),
				),
			),
		),
	);

	protected static $_to_array_exclude = array();

	public static function save_at_sent($member_id, $message_id, $datetime = null)
	{
		// save message_recieved
		$obj = Model_MessageRecieved::forge();
		$obj->member_id = $member_id;
		$obj->message_id = $message_id;
		if ($datetime) $obj->created_at = $datetime;
		$obj->save();

		return $obj;
	}

	public static function get_unread_message_ids4member_ids($member_ids, $message_ids)
	{
		if (!$unread_conds = self::get_unread_condition($member_ids, $message_ids)) return false;

		return self::get_cols('message_id', $unread_conds);
	}

	public static function update_is_read4member_ids_and_message_ids($member_ids, $message_ids)
	{
		if (!$unread_conds = self::get_unread_condition($member_ids, $message_ids)) return false;
		if (!$objs = self::get_all(null, null, null, $unread_conds)) return false;

		foreach ($objs as $id => $obj)
		{
			$obj->is_read = 1;
			$obj->save();
		}
	}

	protected static function get_unread_condition($member_ids, $message_ids)
	{
		$member_ids = (array)$member_ids;
		if (!$member_ids || !$message_ids) return false;
		$member_id_cond = (count($member_ids) == 1) ? array('member_id', array_shift($member_ids)) : array('member_id', 'in', $member_ids);

		return array(
			$member_id_cond,
			array('message_id', 'in', $message_ids),
			array('is_read', 0),
		);
	}
}
