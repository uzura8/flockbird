<?php
namespace News;

class Model_NewsFile extends \MyOrm\Model
{
	protected static $_table_name = 'news_file';

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
			'label' => '表示名',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => 'text', 'class' => 'form-control'),
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

	public function get_file()
	{
		if (empty($this->file_id)) return 'nw';

		return \Model_File::get_name($this->file_id);
	}

	public static function get_count4news_id($news_id)
	{
		if (!empty(self::$count_list[$news_id])) return self::$count_list[$news_id];

		$query = self::query()->where('news_id', $news_id);
		self::$count_list[$news_id] = $query->count();

		return self::$count_list[$news_id];
	}

	public static function get4news_id($news_id)
	{
		return self::query()->where('news_id', $news_id)->get();
	}

	public static function get_ids4news_id($news_id, $order_by = 'id')
	{
		$result = \DB::select('id')->from('news_file')->where('news_id', $news_id)->order_by($order_by, 'asc')->execute()->as_array();

		return \Util_db::conv_col($result);
	}
}
