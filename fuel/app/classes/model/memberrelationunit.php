<?php

class Model_MemberRelationUnit extends \MyOrm\Model
{
	protected static $_table_name = 'member_relation_unit';
	protected static $_has_one = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'member_id_lower' => array('form' => array('type' => false)),
		'member_id_upper' => array('form' => array('type' => false)),
	);

	protected static $_observers = array();
	protected static $_to_array_exclude = array();
}
