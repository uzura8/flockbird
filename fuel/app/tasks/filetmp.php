<?php
namespace Fuel\Tasks;

/**
 * Task FileTmp
 */

class FileTmp
{

	/**
	 * Usage (from command line):
	 *
	 * php oil r filetmp
	 *
	 * @return string
	 */
	public static function run($speech = null)
	{
		return self::clean();
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r filetmp:clean
	 *
	 * @return string
	 */
	public static function clean($lifetime = null)
	{
		if (is_null($lifetime)) $lifetime = conf('upload.tmp_file.lifetime');
		$limit = conf('upload.tmp_file.delete_record_limit');
		$file_tmps = \Model_FileTmp::query()
			->where('created_at', '<', date('Y-m-d H:i:s', time() - $lifetime))
			->order_by('id', 'asc')
			->rows_limit($limit)
			->get();
		$i = 0;
		foreach ($file_tmps as $file_tmp)
		{
			if ($file_tmp->delete()) $i++;
		}

		return $i.' file_tmps removed.';
	}
}

/* End of file tasks/filetmp.php */
