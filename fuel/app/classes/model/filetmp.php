<?php
class Model_FileTmp extends \Orm\Model
{
	protected static $_table_name = 'file_tmp';
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
		'exif',
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

	public static function get_enables($member_id, $contents, $hash)
	{
		return self::query()
			->where('member_id', $member_id)
			->where('contents', $contents)
			->where('created_at', '>', date('Y-m-d H:i:s', time() - Config::get('site.upload.tmp_file.lifetime')))
			->where('hash', $hash)
			->order_by('created_at')
			->get();
	}

	public static function delete_expired($member_id, $contents, $hash)
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

		$filename = $obj->name;
		$filepath = $obj->path;
		$filesize = $obj->filesize;
		$obj->delete();
		Site_Upload::remove_images($filepath, $filename, true);

		return $filesize;
	}
}
