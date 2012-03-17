<?php
class Model_Comment extends \Orm\Model
{
	protected static $_belongs_to = array('post', 'user');

	protected static $_properties = array(
		'id',
		'name',
		'email',
		'website',
		'message',
		'post_id',
		'created_at',
		'updated_at',
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => false,
		),
	);

	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		$val->add_field('name', 'Name', 'required|max_length[255]');
		$val->add_field('email', 'Email', 'required|valid_email|max_length[255]');
		$val->add_field('website', 'Website', 'required|max_length[255]');
		$val->add_field('message', 'Message', 'required');
		$val->add_field('post_id', 'Post Id', 'required|valid_string[numeric]');

		return $val;
	}

}
