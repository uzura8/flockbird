<?php
namespace MyOrm;

class Observer_SaveAlbumImageLocation extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_insert(\Album\Model_AlbumImage $obj)
	{
		if (!$locations = static::get_location($obj)) return;

		$album_image_location = \Album\Model_AlbumImageLocation::forge();
		$album_image_location->album_image_id = $obj->id;
		$album_image_location->latitude  = $locations[0];
		$album_image_location->longitude = $locations[1];
		$album_image_location->save();
	}

	protected static function get_location(\Album\Model_AlbumImage $obj)
	{
		if (!$file = \Model_File::get4name($obj->file_name)) return false;
		if (!$file->exif) return false;

		$exif = unserialize($file->exif);

		return \Site_Upload::get_exif_location($exif);
	}
}
// End of file savealbumimagelocation.php
