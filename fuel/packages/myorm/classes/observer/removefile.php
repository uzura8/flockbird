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
		$this->delete_stored_file($obj);
	}

	protected function delete_stored_file(\Orm\Model $obj)
	{
		if (conf('upload.storageType') == 'normal') return;
		if ($this->_is_tmp && \Model_File::get4name($obj->name)) return;

		if (conf('upload.storageType') == 'S3') return \Site_S3::delete($obj->name);

		if (!$file_bin = \Model_FileBin::get4name($obj->name)) return;
		return $file_bin->delete();
	}
}
// End of file removefile.php
