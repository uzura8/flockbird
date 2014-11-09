<?php
namespace MyOrm;

class Observer_DeleteAlbum extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_delete(\Orm\Model $obj)
	{
		// Delete album_image file.
		$album_images = \Album\Model_AlbumImage::get4album_id($obj->id);
		foreach ($album_images as $album_image)
		{
			$album_image->delete();
		}

		// timeline 投稿の削除
		if (\Module::loaded('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('album', $obj->id);
	}
}
// End of file deletealbumimage.php
