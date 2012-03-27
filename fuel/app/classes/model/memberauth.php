<?php
class Model_Memberauth extends \Orm\Model
{
	protected static $_table_name = 'member_auth';
	protected static $_has_one = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
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
		'password' => array(
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
		//$val->add_field('title', 'Title', 'required|max_length[255]');

		return $val;
	}
}
