<?php
namespace Thread;

class Model_ThreadImage extends \MyOrm\Model
{
	protected static $_table_name = 'thread_image';

	protected static $_belongs_to = array(
		'thread' => array(
			'key_from' => 'thread_id',
			'model_to' => '\Thread\Model_Thread',
			'key_to' => 'id',
			'cascade_save' => true,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'thread_id' => array(
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
		'shot_at' => array(
			'data_type' => 'datetime',
			'validation' => array('trim', 'valid_date' => array('Y-m-d H:i:s')),
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
		'MyOrm\Observer_DeleteRelationalTables' => array(
			'events' => array('before_delete'),
			'relations' => array(
				'model_to' => '\Model_File',
				'conditions' => array(
					'name' => array('file_name' => 'property'),
				),
			),
		),
	);

	protected static $image_prefix = 't';
	protected static $count_list = array();

	public static function get_count4thread_id($thread_id)
	{
		if (!empty(self::$count_list[$thread_id])) return self::$count_list[$thread_id];

		$query = self::query()->where('thread_id', $thread_id);
		self::$count_list[$thread_id] = $query->count();

		return self::$count_list[$thread_id];
	}

	public static function get4thread_id($thread_id)
	{
		return self::query()->where('thread_id', $thread_id)->get();
	}

	public static function get_ids4thread_id($thread_id, $order_by = 'id')
	{
		$result = \DB::select('id')->from('thread_image')->where('thread_id', $thread_id)->order_by($order_by, 'asc')->execute()->as_array();

		return \Util_db::conv_col($result);
	}
}
