<?php
namespace MyOrm;

class Observer_DeleteTimeline extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_delete(\Orm\Model $obj)
	{
		\Timeline\Site_Util::delete_cache($obj->id, $obj->type);
	}
}
// End of file deletetimeline.php
