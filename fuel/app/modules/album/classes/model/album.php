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
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => 'radio', 'options' => array()),
		),
		'cover_album_image_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'foreign_table' => array(
			'data_type' => 'text',
			//'validation' => array('trim', array('in_array', array('note'))),
			'validation' => array('trim', 'max_length' => array(20)),
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
		static::$_properties['name']['label'] = \Config::get('term.album').'名';
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_album_foreign_tables();
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
		$files = \DB::select('file.id', 'file.path', 'file.name')
			->from('file')
			->join('album_image', 'LEFT')->on('album_image.file_id', '=', 'file.id')
			->where('album_image.album_id', $id)
			->execute()->as_array();
		$file_ids = array();
		foreach ($files as $file)
		{
			\Site_Upload::remove_images($file['path'], $file['name']);
			$file_ids[] = $file['id'];
		}

		// Delete table file data.
		if ($file_ids) \DB::delete('file')->where('id', 'in', $file_ids)->execute();

		// Delete table timeline data.
		$album_image_ids = \Util_db::conv_col(
			\DB::select('album_image_id')->from('album_image')
				->where('album_id', $id)
				->execute()->as_array()
		);
		\Timeline\Site_Model::delete_timelines('album_image', $album_image_ids);

		// Delete album.
		$album->delete();
	}

	public static function get_album_for_foreign_table($member_id, $table_name)
	{
		$album = self::find('first', array(
			'where' => array(array('member_id', $member_id), array('foreign_table', $table_name)),
			'order_by' => array('id' => 'asc'),
		));
		if ($album) return $album;

		$self = self::forge();
		$self->name          = self::get_name_for_foreign_table($table_name);
		$self->member_id     = $member_id;
		$self->public_flag   = \Config::get('site.public_flag.default');
		$self->foreign_table = $table_name;
		$self->save();

		return $self;
	}

	public static function get_id_for_foreign_table($member_id, $table_name)
	{
		$album = self::get_album_for_foreign_table($member_id, $table_name);

		return $album->id;
	}

	private static function get_name_for_foreign_table($table_name)
	{
		switch ($table_name)
		{
			case 'note':
				return sprintf('%s用%s', \Config::get('term.note'), \Config::get('term.album'));
				break;
			case 'member':
				return sprintf('%s写真用%s', \Config::get('term.profile'), \Config::get('term.album'));
				break;
			default :
				break;
		}

		return \Config::get('term.album');
	}
}
