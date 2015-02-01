<?php
namespace MyOrm;

class Observer_DeleteMember extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_delete(\Orm\Model $obj)
	{
		// delete profile image.
		if (conf('upload.types.img.types.m.save_as_album_image') && $obj->file_name && $file = Model_File::get4name($obj->file_name))
		{
			$file->delete();
		}

		// Delete for notice, member_watch_content
		$limit = conf('batch.limit.delete.default');
		$pre_delete_tables = \Notice\Site_Util::get_accept_foreign_tables();
		if (!in_array('album', $pre_delete_tables)) $pre_delete_tables[] = 'album';
		foreach ($pre_delete_tables as $pre_delete_table)
		{
			$pre_delete_model = \Site_Model::get_model_name($pre_delete_table);
			while ($pre_delete_objs = $pre_delete_model::query()->where('member_id', $obj->id)->limit($limit))
			{
				foreach ($pre_delete_objs as $pre_delete_obj)
				{
					if ($pre_delete_table == 'note')
					{
						$pre_delete_obj->delete_with_relations();	
					}
					else
					{
						$pre_delete_obj->delete();
					}
				}
			}
		}
	}
}
// End of file deletemember.php
