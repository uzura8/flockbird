<?php
class Model_OauthProvider extends \Orm\Model
{
	protected static $_table_name = 'oauth_provider';
	protected static $_properties = array(
		'id',
		'name' => array(
			'data_type' => 'varchar',
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(50),
			),
		),
	);
}
