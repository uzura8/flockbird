<?php
namespace Note;

class Model_NoteAlbumImage extends \Orm\Model
{
	protected static $_table_name = 'note_album_image';

	protected static $_belongs_to = array(
		'note' => array(
			'key_from' => 'note_id',
			'model_to' => '\Note\Model_Note',
			'key_to' => 'id',
		),
		'album_image' => array(
			'key_from' => 'album_image_id',
			'model_to' => '\Album\Model_AlbumImage',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'note_id',
		'album_image_id',
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

	public static function _init()
	{
		if (\Module::loaded('timeline'))
		{
			// album_image 追加時に timeline の sort_datetime を更新
			static::$_observers['MyOrm\Observer_UpdateRelationalTable'] = array(
				'events' => array('after_insert'),
				'model_to' => '\Timeline\Model_Timeline',
				'relations' => array(
					'foreign_table' => array(
						'note' => 'value',
					),
					'foreign_id' => array(
						'note_id' => 'property',
					),
				),
				'property_from' => 'created_at',
				'property_to' => 'sort_datetime',
			);
		}
	}

	public static function save_multiple($note_id, array $album_image_ids)
	{
		$note_album_image_ids = array();
		foreach ($album_image_ids as $album_image_id)
		{
			$self = self::forge();
			$self->note_id = $note_id;
			$self->album_image_id = $album_image_id;
			$self->save();

			$note_album_image_ids[] = $self->id;
		}

		return $note_album_image_ids;
	}

	public static function get_album_image4note_id($note_id, $limit = 0, $sort = array('id' => 'asc'), $with_count_all = false)
	{
		$album_image_ids = \Util_db::conv_col(\DB::select('album_image_id')->from('note_album_image')->where('note_id', $note_id)->execute()->as_array());
		if (!$album_image_ids)
		{
			return $with_count_all ? array(array(), 0) : array();
		}

		$query = \Album\Model_AlbumImage::query()
			->related(array('album', 'file'))
			->where(array('id', 'in', $album_image_ids));

		if ($with_count_all) $count_all = $query->count();

		if ($sort)
		{
			foreach ($sort as $column => $order)
			{
				$query->order_by($column, $order);
			}
		}
		if ($limit) $query->rows_limit($limit);
		$list = $query->get();

		return $with_count_all ? array($list, $count_all) : $list;
	}

	public static function get_note_id4album_image_id($album_image_id)
	{
		$obj = self::query()->where('album_image_id', $album_image_id)->get_one();

		return $obj->note_id;
	}
}
