<?php
namespace Note;

class Model_Note extends \Orm\Model
{
	protected static $_table_name = 'note';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		)
	);
	//protected static $_has_many = array(
	//	'note_comment' => array(
	//		'key_from' => 'id',
	//		'model_to' => '\Note\Model_NoteComment',
	//		'key_to' => 'note_id',
	//	)
	//);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'title' => array(
			'data_type' => 'varchar',
			'label' => 'タイトル',
			'validation' => array('trim', 'required', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '本文',
			'validation' => array('trim', 'required'),
			'form' => array('type' => 'textarea', 'rows' => 10),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array(),
		),
		'comment_count' => array(
			'data_type' => 'integer',
			'default' => 0,
			'form' => array('type' => false),
		),
		'is_published' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(2), 'in_array' => array(0,1)),
			'form' => array('type' => false),
		),
		'published_at'    => array('form' => array('type' => false)),
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
					'title',
					'body',
					'public_flag',
					'is_published',
					'comment_count' => array(
						'ignore_value' => 'reduced_num',
					),
				),
			),
		),
	);

	public static function _init()
	{
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();

		if (\Module::loaded('timeline'))
		{
			$type_note = \Config::get('timeline.types.note');
			// 更新時に timeline の sort_datetime, comment_count を更新
			static::$_observers['MyOrm\Observer_UpdateRelationalTables'] = array(
				'events' => array('after_update'),
				'relations' => array(
					array(
						'model_to' => '\Timeline\Model_Timeline',
						'conditions' => array(
							'foreign_table' => array(
								'note' => 'value',
							),
							'foreign_id' => array(
								'id' => 'property',
							),
							'type' => array(
								$type_note => 'value',
							),
						),
						'update_properties' => array(
							'public_flag',
							'sort_datetime',
							'comment_count',
						),
					),
				),
			);
		}
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id, array('related' => 'member'));
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public function delete_with_relations()
	{
		$deleted_files = array();
		// album_image の削除
		if (\Module::loaded('album') && $album_images = Model_NoteAlbumImage::get_album_image4note_id($this->id))
		{
			$album_image_ids = array();
			foreach ($album_images as $album_image)
			{
				if (empty($album)) $album = $album_image->album;
				$album_image_ids[] = $album_image->id;
			}
			list($result, $deleted_files) = \Album\Model_AlbumImage::delete_multiple($album_image_ids, $album);
		}

		// timeline 投稿の削除
		if (\Module::loaded('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('note', $this->id);

		// note の削除
		$this->delete();

		return $deleted_files;
	}

	public function update_public_flag_with_relations($public_flag)
	{
		// album_image の public_flag の更新
		if (\Module::loaded('album') && $album_images = Model_NoteAlbumImage::get_album_image4note_id($this->id))
		{
			foreach ($album_images as $album_image)
			{
				$album_image->update_public_flag($public_flag, true);
			}
		}
		//// timeline の public_flag の更新
		//if (\Module::loaded('timeline'))
		//{
		//	\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($public_flag, 'note', $this->id, \Config::get('timeline.types.note'));
		//}

		$this->public_flag = $public_flag;
		$this->save();
	}
}
