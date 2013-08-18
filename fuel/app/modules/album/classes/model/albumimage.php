<?php
namespace Album;

class Model_AlbumImage extends \Orm\Model
{
	protected static $_table_name = 'album_image';

	protected static $_belongs_to = array(
		'album' => array(
			'key_from' => 'album_id',
			'model_to' => '\Album\Model_Album',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);
	protected static $_has_one = array(
		'file' => array(
			'key_from' => 'file_id',
			'model_to' => '\Model_File',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
		),
	);
//	protected static $_has_many = array(
//		'album_image_comment' => array(
//			'key_from' => 'id',
//			'model_to' => '\Album\Model_AlbumImageComment',
//			'key_to' => 'album_image_id',
//			'cascade_save' => false,
//			'cascade_delete' => false,
//		)
//	);

	protected static $_properties = array(
		'id',
		'album_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'file_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'name' => array(
			'data_type' => 'varchar',
			'label' => '名前',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => 'text', 'class' => 'input-xlarge'),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => 'radio', 'options' => array()),
		),
		'shot_at'    => array('form' => array('type' => false)),
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

	protected static $count_par_album_list = array();

	public static function _init()
	{
		static::$_properties['name']['label'] = \Config::get('term.album_image').'タイトル';
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id, array('rows_limit' => 1, 'related' => array('album', 'file')))? : null;
		if (!$obj) return false;
		if ($target_member_id && $target_member_id != $obj->album->member_id) return false;

		return $obj;
	}

	public function get_image()
	{
		if (empty($this->file_id)) return 'ai';

		return \Model_File::get_name($this->file_id);
	}

	public static function delete_with_file($id)
	{
		if (!$id || !$album_image = self::find()->where('id', $id)->related('album')->get_one())
		{
			throw new \FuelException('Invalid album_image id.');
		}

		if ($album_image->album->cover_album_image_id == $id)
		{
			$album_image->album->cover_album_image_id = null;
			$album_image->album->save();
		}

		$album_id = $album_image->album_id;
		$album_image->file = \Model_File::find($album_image->file_id);
		$filename = $album_image->file->name;
		$filepath = $album_image->file->path;
		$filesize = $album_image->file->filesize;
		$album_image->file->delete();
		$album_image->delete();
		\Site_Upload::remove_images($filepath, $filename);

		return $filesize;
	}

	public static function get_count4album_id($album_id)
	{
		if (!empty(self::$count_par_album_list[$album_id])) return self::$count_par_album_list[$album_id];

		$query = self::query()->where('album_id', $album_id);
		self::$count_par_album_list[$album_id] = $query->count();

		return self::$count_par_album_list[$album_id];
	}

	public static function get_ids4album_id($album_id, $order_by = 'id')
	{
		$result = \DB::select('id')->from('album_image')->where('album_id', $album_id)->order_by($order_by, 'asc')->execute()->as_array();

		return \Util_db::conv_col($result);
	}

	public static function update_public_flag4album_id($album_id, $public_flag)
	{
		$values = array('public_flag' => $public_flag, 'updated_at' => date('Y-m-d H:i:s'));

		return \DB::update('album_image')->set($values)->where('album_id', $album_id)->execute();
	}

	public static function save_with_file($album_id, $member = null, $public_flag = null, $name = null, $file = null)
	{
		if (empty($member))
		{
			$album = \Model_Album::find($album_id, array('related' => 'member'));
			$member = $album->member;
		}
		if (is_null($public_flag)) $public_flag = \Config::get('site.public_flag.default');
		if (!$file) $file = \Site_Upload::upload('ai', $album_id, $member->id, $member->filesize_total);

		$self = new self;
		$self->album_id = $album_id;
		$self->file_id = $file->id;
		$self->public_flag = $public_flag;
		$self->shot_at = !empty($file->shot_at) ? $file->shot_at : date('Y-m-d H:i:s');
		if ($name) $self->name = $name;
		$self->save();
		\Model_Member::add_filesize($member->id, $file->filesize);

		return $self;
	}
}
