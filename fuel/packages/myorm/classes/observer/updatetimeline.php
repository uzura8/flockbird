<?php
namespace MyOrm;

class Observer_UpdateTimeline extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_update(\Orm\Model $obj)
	{
		\Timeline\Site_Util::delete_cache($obj->id, $obj->type);
	}
}
// End of file updatetimeline.php
