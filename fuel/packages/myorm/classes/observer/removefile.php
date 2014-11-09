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
		\Site_Upload::remove_files_all($obj->name, $this->_is_tmp);
		$this->delete_file_bin($obj);
	}

	protected function delete_file_bin(\Orm\Model $obj)
	{
		if (!conf('upload.isSaveDb')) return;
		if (!$file_bin = \Model_FileBin::get4name($obj->name)) return;
		if ($this->_is_tmp && \Model_File::get4name($obj->name)) return;

		return $file_bin->delete();
	}
}
// End of file removefile.php
