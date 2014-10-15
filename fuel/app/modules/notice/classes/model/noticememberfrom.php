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
		'notice_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
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
		// insert 時に紐づく notice_status の sort_datetime を更新する
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

	public static function get4notice_id($notice_id, $limit, $order_by = array('id' => 'desc'))
	{
		$query = self::query()
			->where('notice_id', $notice_id)
			->order_by($order_by);
		if ($limit) $query->rows_limit($limit);

		return $query->get();
	}

	public static function get_count4notice_id($notice_id)
	{
		return self::query()->where('notice_id', $notice_id)->count();
	}
}
