<?php
namespace Message;

class Model_MessageRecievedMailQueue extends \MyOrm\Model
{
	protected static $_table_name = 'message_recieved_mail_queue';

	protected static $_belongs_to = array(
		'notice_status' => array(
			'key_from' => 'message_recieved_id',
			'model_to' => '\Message\Model_MessageRecieved',
			'key_to' => 'id',
		),
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'message_recieved_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'status' => array(
			'data_type' => 'integer',
			'default' => 0,
			'validation' => array('max_length' => array(2)),
			'form' => array('type' => false),
		),
		'result_message' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
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
}
