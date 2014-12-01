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
		if (!$album = \Album\Model_Album::find($obj->album_id))
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
			if ($album->member->file_name == $obj->file_name)
			{
				$album->member->file_name = null;
				$album->member->save();
			}
			// timeline 投稿の削除
			if (\Module::loaded('timeline'))
			{
				\Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('album_image', $obj->id);
			}
		}

		if (\Module::loaded('timeline'))
		{
			// timeline_child_data の削除
			\Timeline\Model_TimelineChildData::delete4foreign_table_and_foreign_ids('album_image', $obj->id);

			// timeline view cache の削除
			if (\Module::loaded('note')
				&& \Config::get('timeline.articles.cache.is_use')
				&& $obj->album->foreign_table == 'note')
			{
				\Timeline\Site_Model::delete_note_view_cache4album_image_id($obj->id);
			}
		}

		// file 削除
		if ($file = \Model_File::get4name($obj->file_name))
		{
			$file->delete();
		}
	}
}
// End of file deletealbumimage.php
