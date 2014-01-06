<?php
namespace Timeline;

class Model_Timeline extends \Orm\Model
{
	protected static $_table_name = 'timeline';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
		'member_to' => array(
			'key_from' => 'member_id_to',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		)
	);
//	protected static $_has_many = array(
//		'timeline_child_data' => array(
//			'key_from' => 'id',
//			'model_to' => '\Timeline\Model_TimelineChildData',
//			'key_to' => 'timeline_id',
//		),
//		'timeline_comment' => array(
//			'key_from' => 'id',
//			'model_to' => '\Timeline\Model_TimelineComment',
//			'key_to' => 'timeline_id',
//		),
//		'timeline_cache' => array(
//			'key_from' => 'id',
//			'model_to' => '\Timeline\Model_TimelineCache',
//			'key_to' => 'timeline_id',
//		),
//	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'member_id_to' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'group_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'page_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'type' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(2)),
			'form' => array('type' => false),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '本文',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'),
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
		'source' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(64)),
			'form' => array('type' => false),
		),
		'source_uri' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
			'form' => array('type' => false),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'max_length' => array(2)),
			'form' => array(),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
		'sort_datetime' => array('form' => array('type' => false)),
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
		'Orm\\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'MyOrm\Observer_CopyValue'=>array(
			'events'=>array('before_insert'),
			'property_to'   => 'sort_datetime',
			'property_from' => 'created_at',
		),
		// insert 時に紐づく timeline_cache レコードを挿入する
		'MyOrm\Observer_InsertTimelineCache'=>array(
			'events' => array('after_insert'),
			'properties' => array(
				'timeline_id' => 'id',
				'member_id',
				'member_id_to',
				'group_id',
				'public_flag',
				'created_at',
				'sort_datetime',
			),
		),
		// update 時に timeline_cache の特定のカラムのみ更新する
		'MyOrm\Observer_UpdateTimelineCache'=>array(
			'events' => array('after_update'),
		),
		// insert 時に紐づく memberfollow_timeline を inseert する
		'MyOrm\Observer_InsertRelationialTable'=>array(
			'events'   => array('after_insert'),
			'model_to' => '\Timeline\Model_MemberFollowTimeline',
			'properties' => array(
				'timeline_id' => 'id',
				'member_id',
			),
			'additional_records' => array(
				array(
					'timeline_id' => 'id',
					'member_id' => 'member_id_to',
				),
			),
		),
		// update 時に紐づく memberfollow_timeline の updated_at を更新する
		'MyOrm\Observer_UpdateRelationalTable'=>array(
			'events'=>array('after_update'),
			'model_to' => '\Timeline\Model_MemberFollowTimeline',
			'relations' => array(
				'timeline_id' => array(
					'id' => 'property',
				),
			),
			'properties_check_changed' => array(
				'sort_datetime',
			),
			'property_to'   => 'updated_at',
			'property_from' => 'sort_datetime',
		),
	);

	public static function _init()
	{
		static::$_properties['type']['validation']['in_array'][] = \Config::get('timeline.types');
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_accept_timeline_foreign_tables();

		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id);
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public static function get4latest_foreign_data($foreign_table, $foreign_id, $since_datetime = null)
	{
		$query = self::query()
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id);

		if ($since_datetime) $query = $query->where('created_at', '>', $since_datetime);

		return $query->order_by('created_at', 'desc')
			->rows_limit(1)
			->get_one();
	}

	public static function get4ids($timeline_ids)
	{
		if (!is_array($timeline_ids)) $timeline_ids = (array)$timeline_ids;

		return self::query()
			->where('id', 'in', $timeline_ids)
			->get();
	}

	public static function get4foreign_table_and_foreign_ids($foreign_table, $foreign_ids, $type = null)
	{
		$query = self::query()->where('foreign_table', $foreign_table);

		if (is_array($foreign_ids))
		{
			$query = $query->where('foreign_id', 'in', $foreign_ids);
		}
		else
		{
			$query = $query->where('foreign_id', $foreign_ids);
		}

		if (!is_null($type))
		{
			$query = $query->where('type', $type);
		}

		return $query->get();
	}

	public static function delete4foreign_table_and_foreign_ids($foreign_table, $foreign_ids)
	{
		$deleted_files_all = array();
		$objs = self::get4foreign_table_and_foreign_ids($foreign_table, $foreign_ids);
		foreach ($objs as $obj)
		{
			list($result, $deleted_files) = Site_Model::delete_timeline($timeline, $this->u->id);
			$deleted_files_all = array_merge($deleted_files_all, $deleted_files);
		}

		return $deleted_files_all;
	}

	public static function get4type_key($type_key)
	{
		$type = \Config::get('timeline.types.'.$type_key);
		if (is_null($type)) throw new \InvalidArgumentException('first parameter is invalid.');

		return self::query()->where('type', $type)->get();
	}

	public static function update_public_flag4foreign_table_and_foreign_id($public_flag, $foreign_table, $foreign_ids, $type)
	{
		$objs = self::get4foreign_table_and_foreign_ids($foreign_table, $foreign_ids, $type);
		foreach ($objs as $obj)
		{
			$obj->update_public_flag($public_flag);
		}
	}

	public function update_public_flag($public_flag, $is_check_child_data = false)
	{
		if ($is_check_child_data) return $this->update_public_flag_with_check_child_data($public_flag);

		$this->public_flag = $public_flag;

		return $this->save();
	}

	public static function check_and_update_public_flag4child_data($public_flag, $child_foreign_table, $child_foreign_id)
	{
		if (!$ids = Model_TimelineChildData::get_timeline_ids4foreign_table_and_foreign_id($child_foreign_table, $child_foreign_id))
		{
			return false;
		}
		if (!$objs = self::get4ids($ids)) return false;

		foreach ($objs as $obj)
		{
			$obj->update_public_flag_with_check_child_data($public_flag);
		}
	}

	public function update_public_flag_with_check_child_data($public_flag = null)
	{
		if (is_null($public_flag)) $public_flag = PRJ_PUBLIC_FLAG_PRIVATE;

		$public_flag = self::get_public_flag_for_update_with_check_child_data($public_flag, $this);
		if ($this->public_flag == $public_flag) return;

		$this->public_flag = $public_flag;
		$this->save();
	}

	public static function get_public_flag_for_update_with_check_child_data($public_flag, Model_Timeline $obj)
	{
		$check_target_types = array(
			\Config::get('timeline.types.album_image'),
		);
		if (!in_array($obj->type, $check_target_types)) return $public_flag;

		$public_flag_range_max = Model_TimelineChildData::get_public_flag_range_max4timeline_id($obj->id);
		if ($public_flag_range_max === false) return $public_flag;

		if (\Site_Util::check_is_reduced_public_flag_range($public_flag_range_max, $public_flag))
		{
			return $public_flag_range_max;
		}

		return $public_flag;
	}

	public function delete_with_album_image($member_id)
	{
		$album_image_ids = array();
		$deleted_files   = null;
		if (Site_Util::check_type($this->type, 'album_image_timeline')
			&& $album = \Album\Model_Album::check_authority($this->foreign_id, $member_id))
		{
			$album_image_ids = Model_TimelineChildData::get_foreign_ids4timeline_id($this->id);
		}
		if ($album_image_ids)
		{
			list($result, $deleted_files) = \Album\Model_AlbumImage::delete_multiple($album_image_ids, $album);
		}
		$result = $this->delete();

		return array($result, $deleted_files);
	}
}
