<?php
namespace Timeline;

class Model_TimelineChildData extends \Orm\Model
{
	protected static $_table_name = 'timeline_child_data';

	protected static $_belongs_to = array(
		'timeline' => array(
			'key_from' => 'timeline_id',
			'model_to' => '\Timeline\Model_Timeline',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'timeline_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'foreign_table' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(20)),
			'form' => array('type' => false),
		),
		'foreign_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
	);

	public static function save_multiple($timeline_id, $foreign_table, $foreign_ids)
	{
		foreach ($foreign_ids as $foreign_id)
		{
			$obj = self::forge();
			$obj->timeline_id = $timeline_id;
			$obj->foreign_table = $foreign_table;
			$obj->foreign_id = $foreign_id;
			$obj->save();
		}
	}

	public static function get_foreign_ids4timeline_id($timeline_id)
	{
		return \Util_db::conv_col(
			\DB::select('foreign_id')
				->from('timeline_child_data')
				->where('timeline_id', $timeline_id)
				->execute()->as_array()
		);
	}
}
