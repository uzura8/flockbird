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

	public static function save_with_file($note_id, $member = null, $album_image_public_flag = null)
	{
		if (empty($member))
		{
			$note = Model_Note::find($note_id, array('related' => 'member'));
			$member = $note->member;
		}
		$album_id = \Album\Model_Album::get_id_for_note($member->id);
		$album_image = \Album\Model_AlbumImage::save_with_file($album_id, $member, $album_image_public_flag);

		$self = self::forge();
		$self->note_id = $note_id;
		$self->album_image_id = $album_image->id;
		$self->save();

		return $self;
	}

	public static function get_album_image4note_id($note_id)
	{
		$album_image_ids = \Util_db::conv_col(\DB::select('id')->from('note_album_image')->where('note_id', $note_id)->execute()->as_array());
		if (!$album_image_ids) return array();

		return \Album\Model_AlbumImage::query()
			->related('file')
			->where(array('id', 'in', $album_image_ids))
			->order_by('id')
			->get();
	}
}
