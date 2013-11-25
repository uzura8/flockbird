<?php
class Model_FileTmp extends \Orm\Model
{
	protected static $_table_name = 'file_tmp';
	protected static $_has_many = array(
		'file_tmp_config' => array(
			'key_from' => 'id',
			'model_to' => 'Model_FileTmpConfig',
			'key_to' => 'file_tmp_id',
		)
	);
	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'path' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'type' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'filesize' => array(
			'validation' => array('trim', 'valid_string' => array('integer')),
		),
		'original_filename' => array(
			'validation' => array('trim', 'max_length' => array(255)),
		),
		'member_id' => array(
			'validation' => array('trim', 'valid_string' => array('integer')),
		),
		'description' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 2, 'placeholder' => '写真の説明', 'class' => 'col-xs-12'),
		),
		'exif' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
		),
		'type' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'contents' => array(
			'validation' => array('trim', 'max_length' => array(20)),
		),
		'hash' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'shot_at',
		'created_at',
		'updated_at'
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
	);

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id);
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public static function get4name_and_member_id($name, $member_id)
	{
		return self::query()
			->where('name', $name)
			->where('member_id', $member_id)
			->get_one();
	}

	public static function get_enables($member_id, $contents, $hash, $spare_time = 0)
	{
		return self::query()
			->related('file_tmp_config')
			->where('member_id', $member_id)
			->where('contents', $contents)
			->where('created_at', '>', date('Y-m-d H:i:s', time() - Config::get('site.upload.tmp_file.lifetime') - $spare_time))
			->where('hash', $hash)
			->order_by('created_at')
			->get();
	}

	public static function delete_expired($member_id, $contents)
	{
		$query = self::query()
			->where('member_id', $member_id)
			->where('contents', $contents)
			->where('created_at', '<', date('Y-m-d H:i:s', time() - Config::get('site.upload.tmp_file.lifetime')))
			->order_by('created_at');
		if ($limit = Config::get('site.upload.tmp_file.delete_record_limit'))
		{
			$query = $query->rows_limit($limit);
		}
		$objs = $query->get();

		$i = 0;
		foreach ($objs as $obj)
		{
			$filename = $obj->name;
			$filepath = $obj->path;
			$obj->delete();
			$result = Site_Upload::remove_images($filepath, $filename, true);
			if ($result) $i++;
		}

		return $i;
	}

	public static function delete_with_file($id)
	{
		if (!$id || !$obj = self::find($id))
		{
			throw new \FuelException('Invalid file_tmp id.');
		}

		\Timeline\Site_Model::delete_timeline('album_image', $id);

		$filename = $obj->name;
		$filepath = $obj->path;
		$filesize = $obj->filesize;
		$obj->delete();
		Site_Upload::remove_images($filepath, $filename, true);

		return $filesize;
	}
}
