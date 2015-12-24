<?php
namespace Message;

class Model_MessageSentAdmin extends \MyOrm\Model
{
	protected static $_table_name = 'message_sent_admin';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => '\Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
		'message' => array(
			'key_from' => 'message_id',
			'model_to' => '\Message\Model_Message',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);
	protected static $_has_one = array(
		'admin_user' => array(
			'key_from' => 'admin_user_id',
			'model_to' => '\Admin\Model_AdminUser',
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
		'admin_user_id' => array(
			'data_type' => 'integer',
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
	);

	protected static $_to_array_exclude = array();

	public static function get_one4message_id_and_member_id($message_id, $member_id)
	{
		return self::query()
			->where('message_id', $message_id)
			->where('member_id', $member_id)
			->get_one();
	}

	public static function get4message_id($message_id, $relateds = array())
	{
		$query = self::query();
		if ($relateds) $query->related($relateds);
		$query->where('message_id', $message_id);

		return $query->get();
	}

	public static function get_member_ids4message_id($message_id)
	{
		return self::get_col_array('member_id', array('where' => array('message_id' => $message_id)));
	}
}
