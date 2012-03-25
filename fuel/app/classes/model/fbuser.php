<?php

namespace Model;

class Fbuser extends \Orm\Model
{
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
		'member_id',
		'facebook_id' => array(
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
				'valid_string' => array('integer'),
			),
		),
		'facebook_name' => array(
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
			),
		),
		'facebook_link' => array(
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
			),
		),
		'created_at',
		'updated_at',
	);
}
