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
		if (conf('upload.types.img.types.m.save_as_album_image') && $obj->file_name && $file = \Model_File::get4name($obj->file_name))
		{
			$file->delete();
		}
	}
}
// End of file deletemember.php
