<?php
namespace Album;

class Model_AlbumImage extends \MyOrm\Model
{
	protected static $_table_name = 'album_image';

	protected static $_belongs_to = array(
		'album' => array(
			'key_from' => 'album_id',
			'model_to' => '\Album\Model_Album',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);
//	protected static $_has_many = array(
//		'album_image_comment' => array(
//			'key_from' => 'id',
//			'model_to' => '\Album\Model_AlbumImageComment',
//			'key_to' => 'album_image_id',
//			'cascade_save' => false,
//			'cascade_delete' => false,
//		)
//	);

	protected static $_properties = array(
		'id',
		'album_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'file_name' => array(
			'validation' => array('trim', 'required', 'max_length' => array(64)),
			'form' => array('type' => false),
		),
		'name' => array(
			'data_type' => 'varchar',
			'label' => '名前',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => 'text', 'class' => 'form-control'),
		),
		'comment_count' => array(
			'data_type' => 'integer',
			'default' => 0,
			'form' => array('type' => false),
		),
		'like_count' => array(
			'data_type' => 'integer',
			'default' => 0,
			'form' => array('type' => false),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => 'radio', 'options' => array()),
		),
		'shot_at' => array(
			'data_type' => 'datetime',
			'validation' => array('trim', 'valid_date' => array('Y-m-d H:i:s')),
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
		'sort_datetime' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
		'MyOrm\Observer_CopyValue'=>array(
			'events'=>array('before_insert'),
			'property_to'   => 'sort_datetime',
			'property_from' => 'created_at',
		),
		'MyOrm\Observer_SortDatetime' => array(
			'events' => array('before_update'),
			'mysql_timestamp' => true,
			'check_changed' => array(
				'check_properties' => array(
					'name',
					'public_flag' => array(
						'ignore_value' => 'reduced_public_flag_range',
					),
					'comment_count' => array(
						'ignore_value' => 'reduced_num',
					),
				),
			),
		),
		'MyOrm\Observer_DeleteAlbumImage' => array(
			'events' => array('before_delete'),
		),
	);

	protected static $image_prefix = 'ai';
	protected static $count_par_album_list = array();

	public static function _init()
	{
		static::$_properties['name']['label'] = term('site.picture', 'site.title');
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();

		if (is_enabled('notice'))
		{
			static::$_observers['MyOrm\Observer_InsertNotice'] = array(
				'events'   => array('after_insert'),
				'update_properties' => array(
					'foreign_table' => array('album' => 'value'),
					'foreign_id' => array('album_id' => 'property'),
					'type_key' => array('child_data' => 'value'),
					'member_id_from' => array(
						'related' => array('album' => 'member_id'),
					),
				),
			);
			static::$_observers['MyOrm\Observer_DeleteNotice'] = array(
				'events' => array('before_delete'),
				'conditions' => array(
					'foreign_table' => array('album_image' => 'value'),
					'foreign_id' => array('id' => 'property'),
				),
			);
		}

		if (\Module::loaded('timeline'))
		{
			$type_album_image_profile = \Config::get('timeline.types.album_image_profile');
			// 更新時に timeline の sort_datetime, comment_count を更新
			static::$_observers['MyOrm\Observer_UpdateRelationalTables'] = array(
				'events' => array('after_update'),
				'relations' => array(
					'model_to' => '\Timeline\Model_Timeline',
					'conditions' => array(
						'foreign_table' => array(
							'album_image' => 'value',
						),
						'foreign_id' => array(
							'id' => 'property',
						),
						'type' => array(
							$type_album_image_profile => 'value',
						),
					),
					'check_changed' => array(
						'check_properties' => array(
							'public_flag',
							'sort_datetime',
							'comment_count',
							'like_count',
						),
					),
					'update_properties' => array(
						'public_flag',
						'sort_datetime',
						'updated_at',
						'comment_count',
						'like_count',
					),
				),
			);
			if (\Config::get('timeline.articles.cache.is_use'))
			{
				static::$_observers['MyOrm\Observer_ExecuteToRelations'] = array(
					'events' => array('after_update'),
					'relations' => array(
						'model_to' => '\Timeline\Model_TimelineChildData',
						'conditions' => array(
							'foreign_table' => array(
								'album_image' => 'value',
							),
							'foreign_id' => array(
								'id' => 'property',
							),
						),
						'check_changed' => array(
							'check_properties' => array(
								'name',
								'public_flag',
							),
						),
						'execute_func' => array(
							'method' => '\Timeline\Site_Util::delete_cache',
							'params' => array(
								'timeline_id' => 'property',
							),
						),
					),
				);
			}
		}
	}

	public static function check_authority($id, $target_member_id = 0, $related_tables = null, $member_id_prop = 'member_id')
	{
		if (is_null($related_tables)) $related_tables = array('album');

		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;
		if ($target_member_id && $target_member_id != $obj->album->{$member_id_prop}) throw new \HttpForbiddenException;

		return $obj;
	}

	public static function get_count4album_id($album_id)
	{
		if (!empty(self::$count_par_album_list[$album_id])) return self::$count_par_album_list[$album_id];

		$query = self::query()->where('album_id', $album_id);
		self::$count_par_album_list[$album_id] = $query->count();

		return self::$count_par_album_list[$album_id];
	}

	public static function get4album_id($album_id)
	{
		return self::query()->where('album_id', $album_id)->get();
	}

	public static function get_ids4album_id($album_id, $order_by = 'id')
	{
		$result = \DB::select('id')->from('album_image')->where('album_id', $album_id)->order_by($order_by, 'asc')->execute()->as_array();

		return \Util_db::conv_col($result);
	}

	public function update_public_flag($public_flag, $is_skip_check_album_disabled_to_update = false)
	{
		if (!$is_skip_check_album_disabled_to_update && $result = Site_Util::check_album_disabled_to_update($this->album->foreign_table))
		{
			throw new \DisableToUpdateException($result['message']);
		}

		$this->public_flag = $public_flag;
		$result = $this->save();
		if (\Module::loaded('timeline')) \Timeline\Model_Timeline::check_and_update_public_flag4child_data($public_flag, 'album_image', $this->id);

		return $result;
	}

	public static function update_public_flag4album_id($album_id, $public_flag)
	{
		$objs = self::get4album_id($album_id);
		foreach ($objs as $obj)
		{
			$obj->update_public_flag($public_flag);
		}
	}

	public static function save_with_relations($album_id, \Model_Member $member = null, $public_flag = null, $file_path = null, $timeline_type_key = 'album_image', $optional_values = array())
	{
		if (!\Util_Array::array_in_array(array_keys($optional_values), array('name', 'shot_at', 'shot_at_time', 'public_flag')))
		{
			throw new \InvalidArgumentException('Parameter optional_values is invalid.');
		}
		if (is_null($public_flag))
		{
			$public_flag = isset($optional_values['public_flag']) ? $optional_values['public_flag'] : conf('public_flag.default');
		}

		$album = null;
		if (empty($member))
		{
			$album = Model_Album::find($album_id, array('related' => 'member'));
			$member = $album->member;
		}

		$options = \Site_Upload::get_uploader_options($member->id, 'ai', $album_id);
		$uploadhandler = new \Site_Uploader($options);
		$file = $uploadhandler->save($file_path);
		if (!empty($file->error)) throw new \FuelException($file->error);

		$self = new self;
		$self->album_id    = $album_id;
		$self->file_name   = $file->name;
		$self->public_flag = $public_flag;
		$self->shot_at = self::get_shot_at_for_insert(
			$file->shot_at,
			isset($optional_values['shot_at_time']) ? $optional_values['shot_at_time'] : null,
			isset($optional_values['shot_at']) ? $optional_values['shot_at'] : null
		);
		$self->save();

		// カバー写真の更新
		if ($timeline_type_key == 'album_image_profile')
		{
			if (!$album) $album = Model_Album::find($album_id);
			$album->cover_album_image_id = $self->id;
			$album->save();
		}

		// timeline 投稿
		if (\Module::loaded('timeline'))
		{
			switch ($timeline_type_key)
			{
				case 'album_image_profile':
					$timeline_foreign_id = $self->id;
					$timeline_child_foreign_ids = array();
					break;
				case 'album':
				case 'album_image':
				default :
					$timeline_foreign_id = $self->album->id;
					$timeline_child_foreign_ids = array($self->id);
					break;
			}
			\Timeline\Site_Model::save_timeline($member->id, $public_flag, $timeline_type_key, $timeline_foreign_id, $self->updated_at, null, null, $timeline_child_foreign_ids);
		}

		return array($self, $file);
	}

	public function update_with_relations(array $values)
	{
		if (isset($values['name']) && $values['name'] !== $this->name)
		{
			$this->name = $values['name'];
		}
		$this->shot_at = self::get_shot_at_for_update(
			$this->shot_at,
			isset($values['shot_at_time']) ? $values['shot_at_time'] : null,
			isset($values['shot_at']) ? $values['shot_at'] : null
		);
		$is_changed = $this->is_changed();
		$this->save();

		if (isset($values['public_flag']) && $values['public_flag'] !== $this->public_flag)
		{
			$this->update_public_flag($values['public_flag'], true);
		}

		return $is_changed;
	}

	public static function delete_multiple(array $ids)
	{
		if (empty($ids)) throw new InvalidArgumentException('Second parameter is invalid.');
		if (!$album_images = Model_AlbumImage::query()->where('id', 'in', $ids)->get()) return 0;

		$result = 0;
		foreach ($album_images as $album_image)
		{
			$album_image->delete();
			$result++;
		} 
		//\Model_Member::recalculate_filesize_total($album->member_id);

		return $result;
	}

	public static function update_multiple_all($ids, $set_value, $is_disabled_to_update_public_flag = false)
	{
		$values = array();
		if (isset($set_value['name']) && strlen($set_value['name']))
		{
			$values['name'] = $set_value['name'];
		}
		if (!$is_disabled_to_update_public_flag && isset($set_value['public_flag']) && $set_value['public_flag'] != 99)
		{
			$values['public_flag'] = $set_value['public_flag'];
		}
		if (isset($set_value['shot_at']) && $set_value['shot_at'])
		{
			$values['shot_at'] = $set_value['shot_at'];
		}
		if (isset($set_value['shot_at']) && !\Util_Date::check_is_same_minute($set_value['shot_at'], $album_image->shot_at))
		{
			$values['shot_at'] = $set_value['shot_at'].':'.'00';
		}

		$result = 0;
		if (!empty($values))
		{
			$values['updated_at'] = date('Y-m-d H:i:s');
			$result = \DB::update('album_image')->set($values)->where('id', 'in', $posted_album_image_ids)->execute();
		}
		$is_db_error = $result ? true : false;

		return array($is_db_error, $result);
	}

	public static function update_multiple_each($ids, $set_value, $is_disabled_to_update_public_flag = false)
	{

		$album_images = Model_AlbumImage::find('all', array('where' => array(array('id', 'in', $ids))));
		$result = 0;
		foreach ($album_images as $album_image)
		{
			$is_set = false;
			if (isset($set_value['name']) && strlen($set_value['name'])
				&& $album_image->name != $set_value['name'])
			{
				$album_image->name = $set_value['name'];
				$is_set = true;
			}
			if (isset($set_value['shot_at']) && strlen($set_value['shot_at'])
				&& !\Util_Date::check_is_same_minute($set_value['shot_at'], $album_image->shot_at))
			{
				$album_image->shot_at = $set_value['shot_at'].':'.'00';
				$is_set = true;
			}
			if ($is_set) $album_image->save();

			if (!$is_disabled_to_update_public_flag && isset($set_value['public_flag'])
				&& $set_value['public_flag'] != 99
				&& $album_image->public_flag != $set_value['public_flag'])
			{
				$album_image->update_public_flag($set_value['public_flag'], true);
				$is_set = true;
			}
			if ($is_set) $result++;;
		}

		return $result;
	}

	protected static function get_shot_at_for_insert($file_shot_at = null, $shot_at_time = null, $shot_at = null)
	{
		if (!empty($shot_at))
		{
			return $shot_at;
		}
		elseif (!empty($shot_at_time))
		{
			return $shot_at_time.':00';
		}
		elseif (!empty($file_shot_at))
		{
			return $file_shot_at;
		}

		return \Date::time()->format('mysql');
	}

	protected static function get_shot_at_for_update($default_shot_at, $update_shot_at_time = null, $update_shot_at = null)
	{
		if ($update_shot_at && $update_shot_at != $default_shot_at)
		{
			return $update_shot_at;
		}
		elseif ($update_shot_at_time && !\Util_Date::check_is_same_minute($update_shot_at_time, $default_shot_at))
		{
			return $update_shot_at_time.':00';
		}

		return $default_shot_at;
	}

	public static function get_album_cover_filename($cover_album_image_id = 0, $album_id = 0, $access_from = 'others')
	{
		$public_flag_conds = \Site_Model::get_where_public_flag4access_from($access_from);

		$query = \Util_Orm::add_query_where(self::query(), $public_flag_conds);
		if ($cover_album_image_id)
		{
			$query->where('id', $cover_album_image_id);
			if ($album_image = $query->get_one())
			{
				return $album_image->get_image();
			}
		}

		$query = \Util_Orm::add_query_where(self::query(), $public_flag_conds);
		$query->where('album_id', $album_id);
		$query->order_by('id', 'asc');
		$query->rows_limit(1);
		$album_image = $query->get_one();

		return $album_image->get_image();
	}
}
