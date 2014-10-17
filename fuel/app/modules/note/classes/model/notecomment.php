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
		'like_count' => array(
			'data_type' => 'integer',
			'default' => 0,
			'form' => array('type' => false),
		),
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
						'id' => array('note_id' => 'property'),
					),
					'optional_updates' => array(
						'sort_datetime' => array('created_at' => 'property'),
					),
				),
			),
		),
		'MyOrm\Observer_CountDownToRelations'=>array(
			'events'   => array('after_delete'),
			'relations' => array(
				array(
					'model_to' => '\Note\Model_Note',
					'conditions' => array(
						'id' => array('note_id' => 'property'),
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

		if (is_enabled('notice'))
		{
			static::$_observers['MyOrm\Observer_InsertNotice'] = array(
				'events'   => array('after_insert'),
				'update_properties' => array(
					'foreign_table' => array('note' => 'value'),
					'foreign_id' => array('note_id' => 'property'),
					'type_key' => array('comment' => 'value'),
					'member_id_from' => array('member_id' => 'property'),
					'member_id_to' => array(
						'related' => array(
							'table' => 'note',
							'property' => 'member_id',
						),
					),
				),
			);
			$type = \Notice\Site_Util::get_notice_type('comment');
			static::$_observers['MyOrm\Observer_DeleteNotice'] = array(
				'events' => array('before_delete'),
				'conditions' => array(
					'foreign_table' => array('note' => 'value'),
					'foreign_id' => array('note_id' => 'property'),
					'type' => array($type => 'value'),
				),
			);
		}
	}

	public static function check_authority($id, $target_member_id = 0, $related_tables = null, $member_id_prop = 'member_id')
	{
		if (is_null($related_tables)) $related_tables = array('note', 'member');

		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;

		$accept_member_ids = array($obj->{$member_id_prop}, $obj->note->{$member_id_prop});
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
}
