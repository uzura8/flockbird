<?php
namespace Notice;

class Model_NoticeStatus extends \MyOrm\Model
{
	protected static $_table_name = 'notice_status';

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
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'notice_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'is_read' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0,1))),
			'default' => 0,
			'form' => array('type' => false),
		),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
	);

	public static function change_status2unread($member_id, $notice_id)
	{
		if (!$obj = self::get4member_id_and_notice_id($member_id, $notice_id))
		{
			$obj = self::forge(array(
				'member_id' => $member_id,
				'notice_id' => $notice_id,
				'is_read' => 0,
			));
			$obj->save();
		}
		elseif ($obj->is_read)
		{
			$obj->is_read = 0;
			$obj->save();
		}

		return $obj;
	}

	public static function get4member_id_and_notice_id($member_id, $notice_id)
	{
		return self::query()
			->where('member_id', $member_id)
			->where('notice_id', $notice_id)
			->get_one();
	}

	public static function get_unread_count4member_id($member_id)
	{
		return self::query()
			->where('member_id', $member_id)
			->where('is_read', 0)
			->count();
	}
}
