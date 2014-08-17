<?php
namespace Note;

class Model_NoteLike extends \MyOrm\Model
{
	protected static $_table_name = 'note_like';

	protected static $_belongs_to = array(
		'timeline' => array(
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
		'created_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
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
					'update_property' => 'like_count',
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
					'update_property' => 'like_count',
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

	public static function check_liked($note_id, $member_id)
	{
		return (bool)$query = self::query()
			->where('note_id', $note_id)
			->where('member_id', $member_id)
			->get_one();
	}
}
