<?php
namespace MyOrm;

class Observer_InsertFileBinDeleteQueue extends \Orm\Observer
{
	protected $_is_tmp = false;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		if (isset($props['is_tmp'])) $this->_is_tmp = $props['is_tmp'];
	}

	public function before_delete(\Orm\Model $obj)
	{
		$file_bin_delete_queue = \Model_FileBinDeleteQueue::forge(array(
			'name' => $obj->name,
			'is_tmp' => $this->_is_tmp,
		));
		$file_bin_delete_queue->save();
	}
}
// End of file insertfilebindeletequeue.php
