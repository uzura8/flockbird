<?php
namespace MyOrm;

class Observer_RemoveFile extends \Orm\Observer
{
	protected $_is_tmp = false;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		if (isset($props['is_tmp'])) $this->_is_tmp = $props['is_tmp'];
	}

	public function before_delete(\Orm\Model $obj)
	{
		\Site_Upload::remove_images($obj->path, $obj->name, $this->_is_tmp);
	}
}
// End of file removefile.php
