<?php
namespace Album;

class Model_Album extends \MyOrm\Model
{
	protected static $_table_name = 'album';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		)
	);
//	protected static $_has_many = array(
//		'album_image' => array(
//			'key_from' => 'id',
//			'model_to' => '\Album\Model_AlbumImage',
//			'key_to' => 'album_image_id',
//			'cascade_save' => true,
//			'cascade_delete' => false,
//		)
//	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'name' => array(
			'data_type' => 'varchar',
			'label' => '名前',
			'validation' => array('trim', 'required', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '説明',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => 'radio', 'options' => array()),
		),
		'cover_album_image_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'foreign_table' => array(
			'data_type' => 'text',
			//'validation' => array('trim', array('in_array', array('note'))),
			'validation' => array('trim', 'max_length' => array(20)),
			'default' => '',
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
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
		'MyOrm\Observer_DeleteAlbum' => array(
			'events' => array('before_delete'),
		),
	);

	public static function _init()
	{
		static::$_properties['name']['label'] = term('album').'名';
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_album_foreign_tables();

		if (\Module::loaded('timeline'))
		{
			// 更新時に timeline の sort_datetime を更新
			$observer_key = \Config::get('timeline.types.album');
			static::$_observers['MyOrm\Observer_UpdateRelationalTables'] = array(
				'events' => array('after_update'),
				'relations' => array(
					'model_to' => '\Timeline\Model_Timeline',
					'conditions' => array(
						'foreign_table' => array('album' => 'value'),
						'foreign_id' => array('id' => 'property'),
						'type' => array($observer_key => 'value'),
					),
					'check_changed' => array(
						'check_properties' => array(
							'name',
							'body',
							'public_flag',
						),
					),
					'update_properties' => array(
						'sort_datetime' => array('updated_at' => 'property'),
					),
				),
			);

			if (\Config::get('timeline.articles.cache.is_use'))
			{
				static::$_observers['MyOrm\Observer_ExecuteToRelations'] = array(
					'events' => array('after_update'),
					'relations' => array(
						'model_to' => '\Timeline\Model_Timeline',
						'execute_func' => array(
							'method' => '\Timeline\Site_Util::delete_cache',
							'params' => array(
								'id' => 'property',
							),
						),
						'conditions' => array(
							'foreign_table' => array(
								'album' => 'value',
							),
							'foreign_id' => array(
								'id' => 'property',
							),
						),
					),
				);
			}
		}
	}

	public static function save_with_relations($values, $member_id, Model_Album $album = null, $file_tmps = array())
	{
		if (!$album) $album = self::forge();
		$is_new = $album->is_new();

		$album->name = $values['name'];
		$album->body = $values['body'];
		$album->public_flag = $values['public_flag'];
		$album->member_id = $member_id;

		$is_changed = $album->is_changed();
		$is_changed_public_flag = (!$is_new && $album->is_changed('public_flag'));
		$album->save();

		$moved_files = array();
		$album_image_ids = array();
		if ($file_tmps)
		{
			list($moved_files, $album_image_ids) = \Site_FileTmp::save_images($file_tmps, $album->id, 'album_id', 'album_image', 'Album', $values['public_flag']);
		}
		if (\Module::loaded('timeline'))
		{
			\Timeline\Site_Model::save_timeline($member_id, $values['public_flag'], 'album', $album->id, $album->updated_at, null, null, $album_image_ids);
		}

		if ($is_changed_public_flag && \Module::loaded('timeline'))
		{
			// timeline の public_flag の更新
			\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($values['public_flag'], 'album', $album->id, \Config::get('timeline.types.album'));
		}

		if (!empty($values['is_update_children_public_flag']))
		{
			// update album_image public_flag
			Model_AlbumImage::update_public_flag4album_id($album->id, $values['public_flag']);
		}

		return array($album, $moved_files, $is_changed);
	}

	public function update_public_flag_with_relations($public_flag, $is_update_album_images = false)
	{
		// album_image の public_flag の更新
		if ($is_update_album_images)
		{
			Model_AlbumImage::update_public_flag4album_id($this->id, $public_flag);
		}

		// timeline の public_flag の更新
		if (\Module::loaded('timeline'))
		{
			\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($public_flag, 'album', $this->id, \Config::get('timeline.types.album'));
		}

		$this->public_flag = $public_flag;
		$this->save();
	}

	public static function get4member_id($member_id, $cols = array(), $with_foreigns = true, $limit = null)
	{
		if (!is_array($cols)) $cols = (array)$cols;
		$query = self::query();
		foreach ($cols as $col) $query->select($col);
		$query->where('member_id', $member_id);
		if (!$with_foreigns) $query->where('foreign_table', '');
		if ($limit) $query->rows_limit($limit);
		$query->order_by('id', 'asc');

		return $query->get();
	}

	public static function get_album_for_foreign_table($member_id, $table_name)
	{
		$album = self::find('first', array(
			'where' => array(array('member_id', $member_id), array('foreign_table', $table_name)),
			'order_by' => array('id' => 'asc'),
		));
		if ($album) return $album;

		$table_info = Site_Util::get_foreign_table_info($table_name);
		$self = self::forge();
		$self->name          = $table_info['name'];
		$self->member_id     = $member_id;
		$self->public_flag   = $table_info['public_flag'];
		$self->foreign_table = $table_name;
		$self->save();

		return $self;
	}

	public static function get_id_for_foreign_table($member_id, $table_name)
	{
		$album = self::get_album_for_foreign_table($member_id, $table_name);

		return $album->id;
	}
}
