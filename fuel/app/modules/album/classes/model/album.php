<?php
namespace Album;

class Model_Album extends \Orm\Model
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
			'form' => array('type' => 'text', 'class' => 'input-xlarge'),
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
		'Orm\\Observer_Validation',
		// 更新時に timeline の sort_datetime を更新
		'MyOrm\Observer_UpdateTimelineDatetime'=>array(
			'events'=>array('after_update'),
			'model_to' => '\Timeline\Model_Timeline',
			'relations' => array(
				'foreign_table' => array(
					'album' => 'value',
				),
				'foreign_id' => array(
					'id' => 'property',
				),
				'type' => array(),
			),
			'properties_check_changed' => array(
				'name',
				'body',
			),
			'property_from' => 'updated_at',
			'property_to' => 'sort_datetime',
		),
	);

	public static function _init()
	{
		static::$_properties['name']['label'] = \Config::get('term.album').'名';
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_album_foreign_tables();

		$observer_key = \Config::get('timeline.types.album');
		static::$_observers['MyOrm\Observer_UpdateTimelineDatetime']['relations']['type'][$observer_key] = 'value';
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id, array('rows_limit' => 1, 'related' => 'member'))? : null;
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public static function delete_relations(Model_Album $album, $id = null)
	{
		if (!$album)
		{
			if (!$id || !$album = self::find($id, array('rows_limit' => 1, 'related' => 'member')))
			{
				throw new \Exception('Invalid album id.');
			}
		}

		// Delete album_image file.
		$files = \DB::select('file.id', 'file.path', 'file.name')
			->from('file')
			->join('album_image', 'LEFT')->on('album_image.file_id', '=', 'file.id')
			->where('album_image.album_id', $id)
			->execute()->as_array();
		$file_ids = array();
		foreach ($files as $file) $file_ids[] = $file['id'];

		if ($file_ids)
		{
			// Profile 写真の登録確認&削除
			if ($album->foreign_table == 'member' && in_array($album->member->file_id, $file_ids))
			{
				$album->member->file_id = null;
				$album->member->save();
			}

			// Delete table file data.
			if (!\DB::delete('file')->where('id', 'in', $file_ids)->execute()) throw new \FuelException('Files delete error.');
		}

		// timeline 投稿の削除
		\Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('album', $album->id);

		// Delete album.
		$album->delete();

		\Model_Member::recalculate_filesize_total($album->member_id);

		return $files;
	}

	public function update_public_flag_with_relations($public_flag, $is_update_album_images = false)
	{
		// album_image の public_flag の更新
		if ($is_update_album_images)
		{
			Model_AlbumImage::update_public_flag4album_id($this->id, $public_flag);
		}

		// timeline の public_flag の更新
		\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($public_flag, 'album', $this->id, \Config::get('timeline.types.album'));

		$this->public_flag = $public_flag;
		$this->save();
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
