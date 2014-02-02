<?php

class Model_ProfileOption extends \Orm\Model
{
	protected static $_table_name = 'profile';

	protected static $_belongs_to = array(
		'profile' => array(
			'key_from' => 'profile_id',
			'model_to' => 'Model_Profile',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'profile_id',
		'label' => array(
			'data_type' => 'text',
			'label' => '項目名',
			'validation' => array('trim', 'required'),
			'form' => array('type' => 'text'),
		),
		'sort_order' => array(
			'data_type' => 'integer',
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
	);
}
