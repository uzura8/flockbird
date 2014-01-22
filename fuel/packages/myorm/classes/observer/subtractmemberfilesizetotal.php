<?php
namespace MyOrm;

class Observer_SubtractMemberFilesizeTotal extends \Orm\Observer
{
	protected $_key_from;
	protected $_key_to;
	protected $_property_from;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_property_from = $props['property_from'];
		$this->_key_from = $props['key_from'];
		$this->_key_to   = $props['key_to'];
	}

	public function after_delete(\Orm\Model $obj)
	{
		$member = \Model_Member::find('first', array('where' => array($this->_key_to => $obj->{$this->_key_from})));
		$member->filesize_total -= $obj->{$this->_property_from};
		if ($member->filesize_total < 0) $member->filesize_total = 0;

		$member->save();
	}
}
// End of file subtractmemberfilesizetotal.php
