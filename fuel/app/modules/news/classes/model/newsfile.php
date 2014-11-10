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
		'MyOrm\Observer_DeleteNewsImage' => array(
			'events' => array('before_delete'),
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

	public static function delete_multiple4news_id($news_id)
	{
		if (!$file_ids = \Util_db::conv_col(\DB::select('file_id')->from('news_file')->where('news_id', $news_id)->execute()->as_array()))
		{
			return array(true, array());
		}

		$deleted_files = \DB::select('path', 'name')->from('file')->where('id', 'in', $file_ids)->execute()->as_array();

		if (!$result = \DB::delete('file')->where('id', 'in', $file_ids)->execute()) throw new \FuelException('Files delete error.');
		if (!$result = \DB::delete('news_file')->where('news_id', $news_id)->execute()) throw new \FuelException('News files delete error.');

		return array($result, $deleted_files);
	}
}
