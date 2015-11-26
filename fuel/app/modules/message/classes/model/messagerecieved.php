<?php
namespace Message;

class Model_MessageRecieved extends \MyOrm\Model
{
	protected static $_table_name = 'message_recieve';

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
	);

	protected static $_to_array_exclude = array();
}
