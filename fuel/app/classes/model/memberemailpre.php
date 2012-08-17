<?php
class Model_MemberEmailPre extends \Orm\Model
{
	protected static $_table_name = 'member_email_pre';
	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		)
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'validation' => array(
				'trim',
				'required',
				'valid_string' => array('integer'),
			),
		),
		'email' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'token' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'created_at',
		'updated_at'
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
	);

	public static function validate($factory)
	{
		$val = Validation::forge($factory);

		return $val;
	}
}
