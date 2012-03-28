<?php
namespace Note;

class Model_NoteComment extends \Orm\Model
{
	protected static $_table_name = 'note_comment';

	protected static $_belongs_to = array(
		'note' => array(
			'key_from' => 'note_id',
			'model_to' => '\Note\Model_Note',
			'key_to' => 'id',
		),
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'note_id',
		'member_id',
		'body',
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

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		$val->add_field('body', 'コメント', 'required');

		return $val;
	}
}
