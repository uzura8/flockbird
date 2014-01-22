<?php
namespace MyOrm;

class LimitUploadFileSizeException extends \FuelException {}

class Observer_AddMemberFilesizeTotal extends \Orm\Observer
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

	public function before_insert(\Orm\Model $obj)
	{
		$member = \Model_Member::find('first', array('where' => array($this->_key_to => $obj->{$this->_key_from})));
		$member->filesize_total += $obj->{$this->_property_from};

		// check filesize_total
		if ($member->filesize_total > \Site_Upload::get_accepted_filesize($obj->{$this->_key_from}))
		{
			throw new LimitUploadFileSizeException('File size is over the limit of the member.');
		}

		$member->save();
	}
}
// End of file addmemberfilesizetotal.php
