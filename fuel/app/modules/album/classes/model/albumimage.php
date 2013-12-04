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
		if (!$id || !$album_image = self::query()->where('id', $id)->related('album')->get_one())
		{
			throw new \FuelException('Invalid album_image id.');
		}

		if ($album_image->album->cover_album_image_id == $id)
		{
			$album_image->album->cover_album_image_id = null;
			$album_image->album->save();
		}
		self::check_and_delete_member_profile_image($album_image);

		//\Timeline\Site_Model::delete_timeline('album_image', $id);

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

	public static function save_with_file($album_id, $member = null, $public_flag = null, $name = null, $file = null, $sizes = array(), $file_path = null, $is_save_original_filename = true)
	{
		if (empty($member))
		{
			$album = \Model_Album::find($album_id, array('related' => 'member'));
			$member = $album->member;
		}
		if (!$file) $file = \Site_Upload::upload('ai', $album_id, $member->id, $member->filesize_total, $sizes, array(), 0, $file_path, $is_save_original_filename);

		$self = new self;
		$self->album_id = $album_id;
		$self->file_id = $file->id;
		$self->public_flag = is_null($public_flag) ? \Config::get('site.public_flag.default') : $public_flag;
		$self->shot_at = !empty($file->shot_at) ? $file->shot_at : date('Y-m-d H:i:s');
		$self->name = $name ?: $file->original_filename;
		
		$self->save();

		return $self;
	}

	public static function delete_multiple($ids, \Album\Model_Album $album)
	{
		$file_ids = \Util_db::conv_col(\DB::select('file_id')->from('album_image')->where('id', 'in', $ids)->execute()->as_array());
		$deleted_files = \DB::select('path', 'name')->from('file')->where('id', 'in', $file_ids)->execute()->as_array();

		// カバー写真が削除された場合の対応
		if ($album->cover_album_image_id && in_array($album->cover_album_image_id, $ids))
		{
			$album->cover_album_image_id = null;
			$album->save();
		}

		// Profile 写真の登録確認&削除
		if ($album->foreign_table == 'member' && in_array($album->member->file_id, $file_ids))
		{
			$album->member->file_id = null;
			$album->member->save();
		}

		// delete timeline data.
		\Timeline\Site_Model::delete_timelines('album_image', $ids);

		$is_db_error = false;
		if (!$result = \DB::delete('file')->where('id', 'in', $file_ids)->execute()) $is_db_error = true;
		if (!$result = \DB::delete('album_image')->where('id', 'in', $ids)->execute()) $is_db_error = true;

		foreach ($deleted_files as $file)
		{
			\Site_Upload::remove_images($file['path'], $file['name']);
		}

		\Model_Member::recalculate_filesize_total($album->member_id);

		return array($is_db_error, $result, $deleted_files);
	}

	public static function update_multiple_all($ids, $set_value, $is_disabled_to_update_public_flag = false)
	{
		$values = array();
		if (isset($set_value['name']) && strlen($set_value['name']))
		{
			$values['name'] = $set_value['name'];
		}
		if (!$is_disabled_to_update_public_flag && isset($set_value['public_flag']) && $set_value['public_flag'] != 99)
		{
			$values['public_flag'] = $set_value['public_flag'];
		}
		if (isset($set_value['shot_at']) && $set_value['shot_at'])
		{
			$values['shot_at'] = $set_value['shot_at'];
		}
		if (isset($set_value['shot_at']) && !\Util_Date::check_is_same_minute($set_value['shot_at'], $album_image->shot_at))
		{
			$values['shot_at'] = $set_value['shot_at'].':'.'00';
		}

		$result = 0;
		if (!empty($values))
		{
			$values['updated_at'] = date('Y-m-d H:i:s');
			$result = \DB::update('album_image')->set($values)->where('id', 'in', $posted_album_image_ids)->execute();
		}
		$is_db_error = $result ? true : false;

		return array($is_db_error, $result);
	}

	public static function update_multiple_each($ids, $set_value, $is_disabled_to_update_public_flag = false)
	{

		$album_images = Model_AlbumImage::find('all', array('where' => array(array('id', 'in', $ids))));
		$result = 0;
		foreach ($album_images as $album_image)
		{
			if (isset($set_value['name']) && strlen($set_value['name']))
			{
				$album_image->name = $set_value['name'];
			}
			if (!$is_disabled_to_update_public_flag && isset($set_value['public_flag']) && $set_value['public_flag'] != 99)
			{
				$album_image->public_flag = $set_value['public_flag'];
			}
			if (isset($set_value['shot_at']) && strlen($set_value['shot_at']) && !\Util_Date::check_is_same_minute($set_value['shot_at'], $album_image->shot_at))
			{
				$album_image->shot_at = $set_value['shot_at'].':'.'00';
			}
			$album_image->save();
			$result++;
		}

		return $result;
	}

	// Profile 写真の登録確認&削除
	public static function check_and_delete_member_profile_image($album_image)
	{
		if ($album_image->album->foreign_table == 'member' && $album_image->album->member->file_id == $album_image->file_id)
		{
			$album_image->album->member->file_id = null;
			$album_image->album->member->save();
		}
	}
}
