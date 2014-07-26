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

	protected static $count_per_note = array();

	public static function _init()
	{
		if (\Module::loaded('timeline'))
		{
			static::$_observers['MyOrm\Observer_UpdateRelationalTable'] = array(
				'events'=>array('after_insert'),
				'model_to' => '\Timeline\Model_Timeline',
				'relations' => array(
					'foreign_table' => array(
						'note' => 'value',
					),
					'foreign_id' => array(
						'note_id' => 'property',
					),
				),
				'property_from' => 'created_at',
				'property_to' => 'sort_datetime',
			);
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

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		$val->add_field('body', 'コメント', 'required');

		return $val;
	}

	public static function check_authority($id, $target_member_id = 0, $accept_member_ids = array())
	{
		if (!$id) return false;

		$obj = self::find($id, array('rows_limit' => 1, 'related' => array('note', 'member')))? : null;
		if (!$obj) return false;

		$accept_member_ids[] = $obj->member_id;
		$accept_member_ids[] = $obj->note->member_id;
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids)) return false;

		return $obj;
	}

	public static function get_count4note_id($note_id)
	{
		if (!empty(self::$count_per_note[$note_id])) return self::$count_per_note[$note_id];

		$query = self::query()->select('id')->where('note_id', $note_id);
		self::$count_per_note[$note_id] = $query->count();

		return self::$count_per_note[$note_id];
	}

	public static function get_comments($note_id, $record_limit = 0, $params = array(), $is_desc = false)
	{
		$is_all_records = false;
		$params = array_merge(array(array('note_id', '=', $note_id)), $params);;
		$query = self::query()->where($params);
		$all_records_count = $query->count();
		$query->related('member');
		if (!$record_limit || $record_limit >= $all_records_count)
		{
			$is_all_records = true;
			$comments = $query->order_by('id', ($is_desc)? 'desc' : 'asc')->get();
		}
		else
		{
			$comments = $query->order_by('id', 'desc')->rows_limit($record_limit)->get();
			if (!$is_desc) $comments = array_reverse($comments);
		}

		return array($comments, $is_all_records, $all_records_count);
	}
}
