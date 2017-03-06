<?php
namespace News;

class Model_NewsImage extends \MyOrm\Model
{
	protected static $_table_name = 'news_image';

	protected static $_belongs_to = array(
		'news' => array(
			'key_from' => 'news_id',
			'model_to' => '\News\Model_News',
			'key_to' => 'id',
			'cascade_save' => true,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'news_id' => array(
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

	protected static $image_prefix = 'nw';
	protected static $count_list = array();

	public static function get_count4news_id($news_id)
	{
		if (!empty(self::$count_list[$news_id])) return self::$count_list[$news_id];

		$query = self::query()->where('news_id', $news_id);
		self::$count_list[$news_id] = $query->count();

		return self::$count_list[$news_id];
	}

	public static function get4news_id($news_id, $order_by = array())
	{
		$query = static::get_query4news_id($news_id, $order_by);

		return $query->get();
	}

	public static function get_one4news_id($news_id, $order_by = array())
	{
		if (!$order_by) $order_by = array('id' => 'desc');
		$query = static::get_query4news_id($news_id, $order_by);

		return $query->get_one();
	}

	protected static function get_query4news_id($news_id, $order_by = array())
	{
		$query = self::query()->where('news_id', $news_id);
		if ($order_by) $query->order_by($order_by);

		return $query;
	}

	public static function get_ids4news_id($news_id, $order_by = 'id')
	{
		$result = \DB::select('id')->from('news_image')->where('news_id', $news_id)->order_by($order_by, 'asc')->execute()->as_array();

		return \Util_db::conv_col($result);
	}

	public static function save_images($news_id, $files)
	{
		$file_cate = static::$image_prefix;
		$new_filepath_prefix = \Site_Upload::get_filepath_prefix($file_cate, $news_id);
		$new_filename_prefix = \Site_Upload::convert_filepath2filename($new_filepath_prefix);
		$returns = array();
		foreach ($files as $file)
		{
			$obj = self::forge();
			$obj->news_id = $news_id;
			$obj->file_name = $new_filename_prefix.$file->name;
			//$obj->name = $file->description;
			$obj->shot_at = !empty($file->shot_at) ? $file->shot_at : date('Y-m-d H:i:s');
			$obj->save();
			$file->id = $obj->id;
			$file->model = 'news';
			$returns[] = $file;
		}

		return $returns;
	}
}
