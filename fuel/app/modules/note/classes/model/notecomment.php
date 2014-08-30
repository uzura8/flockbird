<?php
namespace Note;

class Model_NoteComment extends \MyOrm\Model
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
		'note_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'body',
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
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
		'MyOrm\Observer_CountUpToRelations'=>array(
			'events'   => array('after_insert'),
			'relations' => array(
				array(
					'model_to' => '\Note\Model_Note',
					'conditions' => array(
						'id' => array(
							'note_id' => 'property',
						),
					),
					//'optional_updates' => array(
					//	'sort_datetime' => array(
					//		'created_at' => 'property',
					//	),
					//),
				),
			),
		),
		'MyOrm\Observer_CountDownToRelations'=>array(
			'events'   => array('after_delete'),
			'relations' => array(
				array(
					'model_to' => '\Note\Model_Note',
					'conditions' => array(
						'id' => array(
							'note_id' => 'property',
						),
					),
				),
			),
		),
	);

	protected static $count_per_note = array();

	public static function _init()
	{
		if (\Module::loaded('timeline'))
		{
			static::$_observers['MyOrm\Observer_InsertMemberFollowTimeline'] = array(
				'events'   => array('after_insert'),
				'timeline_relations' => array(
					'foreign_table' => array(
						'note' => 'value',
					),
					'foreign_id' => array(
						'note_id' => 'property',
					),
				),
				'property_from_member_id' => 'member_id',
			);
		}
	}

	public static function check_authority($id, $target_member_id = 0, $related_tables = null)
	{
		if (is_null($related_tables)) $related_tables = array('note', 'member');

		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;

		$accept_member_ids = array($obj->member_id, $obj->note->member_id);
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids))
		{
			throw new \HttpForbiddenException;
		}

		return $obj;
	}

	public static function get_count4note_id($note_id)
	{
		if (!empty(self::$count_per_note[$note_id])) return self::$count_per_note[$note_id];

		$query = self::query()->select('id')->where('note_id', $note_id);
		self::$count_per_note[$note_id] = $query->count();

		return self::$count_per_note[$note_id];
	}

	public static function save_comment($note_id, $member_id, $body = '')
	{
		$values = array(
			'body' => $body,
			'note_id' => $note_id,
			'member_id' => $member_id,
		);
		$obj = self::forge($values);
		$obj->save();

		return $obj;
	}
}
