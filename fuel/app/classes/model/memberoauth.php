<?php
class Model_MemberOauth extends \Orm\Model
{
	protected static $_table_name = 'member_oauth';
	protected static $_has_one = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
		)
	);
	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
			'Orm\Observer_Validation'=> array('events'=>array('before_save')),
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('trim'),
			'form' => array('type' => false),
		),
		'oauth_provider_id' => array(
			'data_type' => 'integer',
			'validation' => array('trim'),
			'form' => array('type' => false),
		),
		'uid' => array(
			'data_type' => 'varchar',
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
			),
		),
		'token' => array(
			'data_type' => 'varchar',
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
			),
		),
		'secret' => array(
			'data_type' => 'varchar',
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'expires' => array(
			'data_type' => 'integer',
			'validation' => array('trim'),
		),
		'service_name' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(255)),
		),
		'service_url' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(255), 'valid_url'),
		),
		'created_at',
		'updated_at',
	);

	/**
	 * Create new member from Oauth response.
	 */
	public function create_member($input = array())
	{
	}
}
