<?php
namespace Note;
class Model_Note extends \Orm\Model
{
	protected static $_table_name = 'note';

	//protected static $_belongs_to = array('member');
	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		)
	);
	//protected static $_has_many = array('NoteComment');

	protected static $_properties = array(
		'id',
		'member_id',
		'title',
		'body',
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'max_length' => array(1)),
			'default' => 0,
		),
		'created_at',
		'updated_at',
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
/*
	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		$val->add_field('title', 'タイトル', 'required|max_length[255]');
		$val->add_field('body', '本文', 'required');

		return $val;
	}
*/
}
