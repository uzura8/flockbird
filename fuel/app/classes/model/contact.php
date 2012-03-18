<?php
use Orm\Model;

class Model_Contact extends Model
{
	protected static $_properties = array(
		'id',
		'name',
		'email',
		'subject',
		'body',
		'ip',
		'ua',
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
		$val->add_field('name', 'Name', 'required|max_length[50]');
		$val->add_field('email', 'Email', 'required|valid_email|max_length[255]');
		$val->add_field('subject', 'Subject', 'required|max_length[255]');
		$val->add_field('body', 'Body', 'required');
		$val->add_field('ip', 'Ip', 'max_length[255]');
		$val->add_field('ua', 'Ua', 'max_length[255]');

		return $val;
	}

}
