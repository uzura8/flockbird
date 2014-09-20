<?php
namespace MyOrm;

class Observer_UpdateTimeline extends \Orm\Observer
{
	protected $_check_properties;
	protected $_ignore_properties;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		if (!empty($props['check_changed']))
		{
			$this->_check_properties = isset($props['check_changed']['check_properties']) ? $props['check_changed']['check_properties'] : array();
			$this->_ignore_properties = isset($props['check_changed']['ignore_properties']) ? $props['check_changed']['ignore_properties'] : array();
		}
	}

	public function after_update(\Orm\Model $obj)
	{
		if (\Util_Orm::check_is_updated($obj, $this->_check_properties, $this->_ignore_properties))
		{
			\Timeline\Site_Util::delete_cache($obj->id, $obj->type);
		}
	}
}
// End of file updatetimeline.php
