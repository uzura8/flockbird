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
				array(
					'model_to' => '\News\Model_NewsImage',
					'conditions' => array(
						'news_id' => array('id' => 'property'),
					),
				),
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

	public function save_with_relations($member_id, $values)
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

		return $is_changed;
	}
}
