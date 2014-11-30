<?php
namespace MyOrm;

class Observer_DeleteMember extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_delete(\Orm\Model $obj)
	{
		// delete profile image.
		if (conf('upload.types.img.types.m.save_as_album_image') && $obj->file_name && $file = Model_File::get4name($obj->file_name))
		{
			$file->delete();
		}

		// delete album and images.
		$limit = conf('batch.limit.delete.default');
		while ($album = \Album\Model_Album::get4member_id($obj->id, null, true, $limit))
		{
			$album->delete();
		}
	}
}
// End of file deletemember.php
