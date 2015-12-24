<?php
namespace Message;

class Model_MessageSentGroup extends \MyOrm\Model
{
	protected static $_table_name = 'message_sent_group';

	protected static $_belongs_to = array(
		'group' => array(
			'key_from' => 'group_id',
			'model_to' => '\Group\Model_Group',
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

	protected static $_properties = array(
		'id',
		'group_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'message_id' => array(
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

	public static function get_one4message_id($message_id, $relateds = array())
	{
		$query = self::query()->where('message_id', $message_id);
		if ($relateds) $query->related($relateds);

		return $query->get_one();
	}
}
