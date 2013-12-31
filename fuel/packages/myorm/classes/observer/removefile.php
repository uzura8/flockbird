<?php
namespace MyOrm;

class Observer_RemoveFile extends \Orm\Observer
{
	public function before_delete(\Orm\Model $obj)
	{
		\Site_Upload::remove_images($obj->path, $obj->name);
	}
}
// End of file removefile.php
