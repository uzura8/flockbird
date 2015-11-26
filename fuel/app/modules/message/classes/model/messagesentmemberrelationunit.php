<?php
namespace Message;

class Model_MessageGroupSent extends \MyOrm\Model
{
	protected static $_table_name = 'message_group_sent';

	protected static $_belongs_to = array(
		'message' => array(
			'key_from' => 'message_id',
			'model_to' => '\Message\Model_Message',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
		'message_group' => array(
			'key_from' => 'message_group_id',
			'model_to' => '\Message\Model_MessageGroup',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'message_group_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'message_id' => array(
			'data_type' => 'integer',
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
