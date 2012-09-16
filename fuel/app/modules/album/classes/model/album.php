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
/*
	protected static $_has_many = array(
		'note_comment' => array(
			'key_from' => 'id',
			'model_to' => '\Note\Model_NoteComment',
			'key_to' => 'note_id',
		)
	);
*/
	protected static $_properties = array(
		'id',
		'member_id',
		'name',
		'body',
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'max_length' => array(1)),
			'default' => 0,
		),
		'created_at',
		'updated_at',
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

	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		$val->add_field('name', \Config::get('album.term.album').'名', 'trim|required|max_length[255]');
		$val->add_field('body', '本文', 'required');

		return $val;
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find()->where('id', $id)->related('member')->get_one();
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public static function delete_all($id)
	{
		if (!$id || !$album = self::find()->where('id', $id)->get_one())
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
			\Site_util::remove_images('ai', $id, $file['name']);
			$file_ids[] = $file['id'];
		}

		// Delete table file data.
		\DB::delete('file')->where('id', 'in', $file_ids)->execute();

		// Delete album.
		$album->delete();
	}
}
