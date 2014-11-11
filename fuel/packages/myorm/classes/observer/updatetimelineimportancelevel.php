<?php
namespace MyOrm;

class Observer_UpdateTimelineImportanceLevel extends \Orm\Observer
{
	public function __construct($class)
	{
		//$props = $class::observers(get_class($this));
	}

	public function before_update(\Orm\Model $obj)
	{
		if (!$obj->is_changed('comment_count') && !$obj->is_changed('like_count')) return;

		$obj->importance_level = \Timeline\Site_Util::get_importance_level($obj->comment_count, $obj->like_count);
	}
}
// End of file updatetimelineimportancelevel.php
