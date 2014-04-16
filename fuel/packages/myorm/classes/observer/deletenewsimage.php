<?php
namespace MyOrm;

class Observer_DeleteNewsImage extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_delete(\Orm\Model $obj)
	{
		//// timeline_child_data の削除
		//\Timeline\Model_TimelineChildData::delete4foreign_table_and_foreign_ids('news_image', $obj->id);

		// file 削除
		if (!$file = \Model_File::query()->where('id', $obj->file_id)->get_one())
		{
			throw new \FuelException('Invalid file id.');
		}
		$file->delete();
	}
}
// End of file deletenewsimage.php
