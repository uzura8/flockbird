<?php
namespace Timeline;

class Model_TimelineChildData extends \MyOrm\Model
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
			'validation' => array('trim', 'max_length' => array(64)),
			'form' => array('type' => false),
		),
		'foreign_id' => array(
			'data_type' => 'string',
			'validation' => array('trim', 'max_length' => array(10)),
			'form' => array('type' => false),
		),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		// delete 後に timeline_child_data が無くなる timeline を削除、または timeline の公開範囲を適切に更新
		'MyOrm\Observer_UpdateTimeline4ChildData' => array(
			'events' => array('after_delete'),
		),
		'MyOrm\Observer_ExecuteToRelations' => array(
			'events' => array('after_save'),
			'relations' => array(
				'execute_func' => array(
					'method' => '\Timeline\Site_Util::delete_cache',
					'params' => array(
						'id' => 'property',
						'type' => 'property',
					),
				),
				'model_to' => '\Timeline\Model_Timeline',
				'conditions' => array(
					'id' => array(
						'timeline_id' => 'property',
					),
				),
			),
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

	public static function get_timeline_ids4foreign_table_and_foreign_id($foreign_table, $foreign_id)
	{
		return \Util_db::conv_col(
			\DB::select('timeline_id')
				->from('timeline_child_data')
				->where('foreign_table', $foreign_table)
				->where('foreign_id', $foreign_id)
				->group_by('timeline_id')
				->execute()->as_array()
		);
	}

	public static function get4foreign_table_and_foreign_ids($foreign_table, $foreign_ids)
	{
		if (!is_array($foreign_ids)) $foreign_ids = (array)$foreign_ids;

		return self::query()
			->where('foreign_table', $foreign_table)
			->where('foreign_id', 'in', $foreign_ids)
			->get();
	}

	public static function delete4foreign_table_and_foreign_ids($foreign_table, $foreign_ids)
	{
		if (!$objs = self::get4foreign_table_and_foreign_ids($foreign_table, $foreign_ids)) return;

		foreach ($objs as $obj) $obj->delete();
	}

	public static function get4timeline_id($timeline_id)
	{
		return self::query()->where('timeline_id', $timeline_id)->get();
	}

	public static function get_public_flag_range_max4timeline_id($timeline_id)
	{
		if (!$objs = self::get4timeline_id($timeline_id)) return false;

		$public_flag_range_max = false;
		foreach ($objs as $obj)
		{
			// 暫定的に album_image 限定
			if ($obj->foreign_table != 'album_image') continue;

			$child_obj = \Album\Model_AlbumImage::check_authority($obj->foreign_id);
			if ($public_flag_range_max === false || \Site_Util::check_is_expanded_public_flag_range($public_flag_range_max, $child_obj->public_flag))
			{
				$public_flag_range_max = $child_obj->public_flag;
			}
		}

		return $public_flag_range_max;
	}
}
