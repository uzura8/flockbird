<?php
namespace MyOrm;

class Observer_DeleteAlbumImage extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_delete(\Orm\Model $obj)
	{
		// カバー写真の確認 & 削除
		if (!$album = \Album\Model_Album::check_authority($obj->album_id))
		{
			throw new \FuelException('Invalid album id.');
		}
		if ($album->cover_album_image_id == $obj->id)
		{
			$album->cover_album_image_id = null;
			$album->save();
		}

		// プロフィール写真の確認 & 削除
		if ($album->foreign_table == 'member')
		{
			if ($album->member->file_id == $obj->file_id)
			{
				$album->member->file_id = null;
				$album->member->save();
			}
			// timeline 投稿の削除
			\Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('album_image', $obj->id);
		}

		// timeline_child_data の削除
		\Timeline\Model_TimelineChildData::delete4foreign_table_and_foreign_ids('album_image', $obj->id);

		// file 削除
		if (!$file = \Model_File::query()->where('id', $obj->file_id)->get_one())
		{
			throw new \FuelException('Invalid file id.');
		}
		$file->delete();
	}
}
// End of file deletealbumimage.php
