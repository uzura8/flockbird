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
		if (!$album = \Album\Model_Album::query()->where('id', $obj->album_id)->get_one())
		{
			throw new \FuelException('Invalid album id.');
		}
		if ($album->cover_album_image_id == $obj->id)
		{
			$album->cover_album_image_id = null;
			$album->save();
		}

		// プロフィール写真の確認 & 削除
		if (!$member = \Model_Member::query()->where('id', $album->member_id)->get_one())
		{
			throw new \FuelException('Invalid member id.');
		}
		if ($album->foreign_table == 'member')
		{
			if ($member->file_id == $obj->file_id)
			{
				$member->file_id = null;
				$member->save();
			}
			// timeline 投稿の削除
			\Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('album_image', $obj->id);
		}

		// file 削除
		if (!$file = \Model_File::query()->where('id', $obj->file_id)->get_one())
		{
			throw new \FuelException('Invalid file id.');
		}
		$file->delete();
	}
}
// End of file deletealbumimage.php
