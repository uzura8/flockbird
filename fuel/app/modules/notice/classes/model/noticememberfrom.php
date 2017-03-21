<?php
namespace Notice;

class Model_NoticeMemberFrom extends \MyOrm\Model
{
	protected static $_table_name = 'notice_member_from';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
		'notice' => array(
			'key_from' => 'notice_id',
			'model_to' => '\Notice\Model_Notice',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'notice_id',
		'member_id',
		'created_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		// Update sort_datetime of notice_status on inserting
		'MyOrm\Observer_UpdateRelationalTables' => array(
			'events' => array('after_insert'),
			'relations' => array(
				'model_to' => '\Notice\Model_NoticeStatus',
				'conditions' => array(
					'notice_id' => array('notice_id' => 'property'),
				),
				'update_properties' => array(
					'sort_datetime' => array('created_at' => 'property'),
				),
			),
		),
	);

	public static function _init()
	{
		static::$_properties['notice_id'] = \Util_Orm::get_relational_numeric_key_prop();
		static::$_properties['member_id'] = \Util_Orm::get_relational_numeric_key_prop();
	}

	public static function check_and_create($notice_id, $member_id)
	{
		if (!$obj = self::get4notice_id_and_member_id($notice_id, $member_id))
		{
			$obj = self::forge(array(
				'notice_id' => $notice_id,
				'member_id' => $member_id,
			));
			$obj->save();
		}

		return $obj;
	}

	public static function get4notice_id_and_member_id($notice_id, $member_id)
	{
		return self::query()
			->where('notice_id', $notice_id)
			->where('member_id', $member_id)
			->get_one();
	}

	public static function get4notice_id($notice_id, $limit, $ignore_member_id = 0, $order_by = array('id' => 'desc'))
	{
		$query = self::query()->where('notice_id', $notice_id);
		if ($ignore_member_id) $query->where('member_id', '<>', $ignore_member_id);
		$query->order_by($order_by);
		if ($limit) $query->rows_limit($limit);

		return $query->get();
	}

	public static function get_count4notice_id($notice_id, $ignore_member_id = 0)
	{
		$query = self::query()->where('notice_id', $notice_id);
		if ($ignore_member_id) $query->where('member_id', '<>', $ignore_member_id);

		return $query->count();
	}

	public static function delete4notice_id_and_member_id($notice_id, $member_id)
	{
		if (!$obj = self::get4notice_id_and_member_id($notice_id, $member_id)) return false;

		return $obj->delete();
	}
}
