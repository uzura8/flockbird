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

		// Delete notification cache
		if (\Site_Notification::check_is_enabled_cahce('message'))
		{
			static::$_observers['MyOrm\Observer_ExecuteOnCreate'] = array(
				'events' => array('after_insert'),
				'execute_func' => array(
					'method' => '\Site_Notification::delete_unread_count_cache',
					'params' => array(
						'message' => 'value',
						'member_id' => 'property',
					),
				),
			);
			static::$_observers['MyOrm\Observer_ExecuteOnUpdate'] = array(
				'events' => array('after_update'),
				'check_properties' => array('is_read'),
				'execute_func' => array(
					'method' => '\Site_Notification::delete_unread_count_cache',
					'params' => array(
						'message' => 'value',
						'member_id' => 'property',
					),
				),
			);
		}
	}

	public static function get_one4member_id_and_type_and_related_id($member_id, $type, $type_related_id)
	{
		return self::get_one4conditions(array(
			'member_id' => $member_id,
			'type' => $type,
			'type_related_id' => $type_related_id,
		));
	}

	public static function save_at_sent($member_id, $message_id, $type, $type_related_id, $datetime = null)
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
		$obj->is_read = 0;
		if ($datetime) $obj->last_sent_at = $datetime;
		$obj->save();

		return $obj;
	}

	public static function get_pager_list4member_id($member_id, $limit, $page = 1, $is_unread_only = false, $relateds = array('message'), $sorts = array('last_sent_at' => 'desc'))
	{
		$params = array();
		$params['limit'] = $limit;
		$params['where'] = array(array('member_id', $member_id));
		if ($is_unread_only) $params['where']['is_read'] = 0;
		if ($relateds) $params['related'] = $relateds;
		if ($sorts) $params['order_by'] = $sorts;

		return static::get_pager_list($params, $page);
	}

	public static function update_is_read4member_ids_old($member_id_to, $member_id_from)
	{
		$related_id = \Model_MemberRelationUnit::get_id4member_ids(array($member_id_to, $member_id_from));
		if (!$obj = self::get_one4conditions(array(
			'member_id' => $member_id_to,
			'type' => Site_Util::get_type4key('member'),
			'type_related_id' => $related_id,
			'is_read' => 0,
		))) return false;

		$obj->is_read = 1;

		return $obj->save();
	}

	public static function update_is_read4member_ids($self_member_id, $partner_member_id)
	{
		$type_related_id = \Model_MemberRelationUnit::get_id4member_ids(array($self_member_id, $partner_member_id));

		return self::update_is_read4unique_key($self_member_id, 'member', $type_related_id);
	}

	public static function update_is_read4unique_key($member_id, $type, $type_related_id)
	{
		if (!$obj = self::get_one4conditions(array(
			'member_id' => $member_id,
			'type' => Site_Util::get_type4key($type),
			'type_related_id' => $type_related_id,
			'is_read' => 0,
		))) return false;

		return $obj->update_status(true);
	}

	public function update_status($is_read)
	{
		$this->is_read = $is_read;
		return (bool)$this->save();
	}

	public static function get4member_id($member_id, $is_read = null)
	{
		$query = self::query()->where('member_id', $member_id);
		if (!is_null($is_read)) $query->where('is_read', (bool)$is_read);

		return $query->get();
	}

	public static function get_unread_count4member_id($member_id)
	{
		return self::query()
			->where('member_id', $member_id)
			->where('is_read', 0)
			->count();
	}
}

