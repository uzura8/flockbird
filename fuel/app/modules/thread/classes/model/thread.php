<?php
namespace Thread;

class Model_Thread extends \MyOrm\Model
{
	protected static $_table_name = 'thread';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		)
	);

	protected static $_properties = array(
		'id',
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
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'category_id' => array(
			'data_type' => 'integer',
			'default' => 0,
			'form' => array('type' => false),
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
					'title',
					'body',
					'public_flag' => array(
						'ignore_value' => 'reduced_public_flag_range',
					),
				),
			),
		),
		// delete 時に紐づくデータを削除する
		'MyOrm\Observer_DeleteRelationalTables' => array(
			'events' => array('before_delete'),
			'relations' => array(
				//array(
				//	'model_to' => '\News\Model_NewsImage',
				//	'conditions' => array(
				//		'news_id' => array('id' => 'property'),
				//	),
				//),
				//array(
				//	'model_to' => '\News\Model_NewsFile',
				//	'conditions' => array(
				//		'news_id' => array('id' => 'property'),
				//	),
				//),
			),
		),
	);

	public static function _init()
	{
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();

		if (is_enabled('notice'))
		{
			static::$_observers['MyOrm\Observer_DeleteNotice'] = array(
				'events' => array('before_delete'),
				'conditions' => array(
					'foreign_table' => array('thread' => 'value'),
					'foreign_id' => array('id' => 'property'),
				),
			);
		}
		if (is_enabled('timeline'))
		{
			$type = conf('types.thread', 'timeline');
			// 更新時に timeline の sort_datetime, comment_count を更新
			static::$_observers['MyOrm\Observer_UpdateRelationalTables'] = array(
				'events' => array('after_update'),
				'relations' => array(
					'model_to' => '\Timeline\Model_Timeline',
					'conditions' => array(
						'foreign_table' => array('thread' => 'value'),
						'foreign_id' => array('id' => 'property'),
						'type' => array($type => 'value'),
					),
					'check_changed' => array(
						'check_properties' => array(
							'title',
							'body',
							'public_flag',
							'sort_datetime',
							'comment_count',
							'like_count',
						),
					),
					'update_properties' => array(
						'public_flag',
						'sort_datetime',
						'comment_count',
						'like_count',
						'updated_at',
					),
				),
			);
			static::$_observers['MyOrm\Observer_DeleteRelationalTables']['relations'][] = array(
				'model_to' => '\Timeline\Model_Timeline',
				'conditions' => array(
					'foreign_table' => array('thread' => 'value'),
					'foreign_id' => array('id' => 'property'),
					'type' => array($type => 'value'),
				),
			);
		}
	}

	public function save_with_relations($member_id, $values, $file_tmps = null, $album_images = array(), $files = array())
	{
		if (!empty($this->member_id) && $this->member_id != $member_id)
		{
			throw new \InvalidArgumentException('Parameter member_id is invalid.');
		}

		$is_new = $this->_is_new;

		$this->member_id = $member_id;
		if (isset($values['title'])) $this->title = $values['title'];
		if (isset($values['body'])) $this->body = $values['body'];

		if (isset($values['public_flag'])) $this->public_flag = $values['public_flag'];
		$is_changed_public_flag = $this->is_changed('public_flag');

		$is_changed = $this->is_changed();
		if ($is_changed) $this->save();

		$moved_files = array();
		//if (is_enabled('album'))
		//{
		//	$image_public_flag = $this->public_flag;
		//	if ($file_tmps)
		//	{
		//		$album_id = \Album\Model_Album::get_id_for_foreign_table($member_id, 'note');
		//		list($moved_files, $album_image_ids) = \Site_FileTmp::save_images($file_tmps, $album_id, 'album_id', 'album_image', $image_public_flag);
		//		\Note\Model_NoteAlbumImage::save_multiple($this->id, $album_image_ids);
		//	}
		//	// フォーム編集時
		//	if ($album_images && $files)
		//	{
		//		\Site_Upload::update_image_objs4file_objects($album_images, $files, $image_public_flag);
		//	}
		//	// フォーム編集以外で日記が公開された時
		//	elseif ($is_published && $saved_album_images = Model_NoteAlbumImage::get_album_image4note_id($this->id))
		//	{
		//		foreach ($saved_album_images as $saved_album_image) $saved_album_image->update_public_flag($this->public_flag, true);
		//	}
		//}

		if (is_enabled('timeline'))
		{
			if (!$is_new && $is_changed_public_flag)
			{
				// timeline の public_flag の更新
				\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($this->public_flag, 'thread', $this->id, \Config::get('timeline.types.thread'));
			}
			else	
			{
				// timeline 投稿
				\Timeline\Site_Model::save_timeline($member_id, $this->public_flag, 'thread', $this->id, $this->updated_at);
			}
		}

		return array($is_changed, $moved_files);
	}

//	public function delete_with_relations()
//	{
//		//// album_image の削除
//		//if (\Module::loaded('album') && $album_images = Model_NoteAlbumImage::get_album_image4note_id($this->id))
//		//{
//		//	$album_image_ids = array();
//		//	foreach ($album_images as $album_image)
//		//	{
//		//		$album_image_ids[] = $album_image->id;
//		//	}
//		//	\Album\Model_AlbumImage::delete_multiple($album_image_ids);
//		//}
//
//		// timeline 投稿の削除
//		if (\Module::loaded('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('thread', $this->id);
//
//		// thread の削除
//		$this->delete();
//	}

//	public function update_public_flag_with_relations($public_flag)
//	{
//		$this->public_flag = $public_flag;
//		if (!$this->is_changed('public_flag')) return;
//		$this->save();
//
//		// album_image の public_flag の更新
//		if ($this->is_published && is_enabled('album') && $album_images = Model_NoteAlbumImage::get_album_image4note_id($this->id))
//		{
//			foreach ($album_images as $album_image)
//			{
//				$album_image->update_public_flag($public_flag, true);
//			}
//		}
//	}
}
