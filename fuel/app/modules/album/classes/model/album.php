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
			'validation' => array('trim', 'required'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(1)),
			//'validation' => array('required', 'max_length' => array(1)),
			'default' => 0,
			'form' => array('type' => false),
		),
		'cover_album_image_id' => array(
			'data_type' => 'integer',
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
	);

	public static function _init()
	{
		static::$_properties['name']['label'] = \Config::get('album.term.album').'名';
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id, array('rows_limit' => 1, 'related' => 'member'))? : null;
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public static function delete_all($id)
	{
		if (!$id || !$album = self::find($id))
		{
			throw new \Exception('Invalid album id.');
		}

		// Delete album_image file.
		$files = \DB::select('file.id', 'file.name')
			->from('file')
			->join('album_image', 'LEFT')->on('album_image.file_id', '=', 'file.id')
			->where('album_image.album_id', $id)
			->execute()->as_array();
		$file_ids = array();
		foreach ($files as $file)
		{
			\Site_Upload::remove_images('ai', $id, $file['name']);
			$file_ids[] = $file['id'];
		}

		// Delete table file data.
		if ($file_ids) \DB::delete('file')->where('id', 'in', $file_ids)->execute();

		// Delete album.
		$album->delete();
	}
}
