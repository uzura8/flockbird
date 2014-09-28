<?php

/**
 * Model_File class tests
 *
 * @group App
 * @group Model
 */
class Test_Model_File extends TestCase
{
	protected $files = array();

	protected function setUp()
	{
		if (!$this->files = \Model_File::find('all'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('No data.');
		}
	}

	public function test_file_exists()
	{
		foreach ($this->files as $file)
		{
			$this->assertFileExists(self::check_and_get_file_path($file->path, $file->name));
		}
	}

	public function test_check_file_size()
	{
		foreach ($this->files as $file)
		{
			$test = File::get_size(self::check_and_get_file_path($file->path, $file->name));
			$this->assertEquals($file->filesize, $test);
		}
	}

	//public function test_check_removed_exif()
	//{
	//	if (!is_callable('exif_read_data'))
	//	{
	//		$this->markTestSkipped('No data.');
	//	}

	//	foreach ($this->files as $file)
	//	{
	//		$test = exif_read_data($this->get_file_path($file->path, $file->name));
	//		$this->assertFalse($test);
	//	}
	//}

	private static function check_and_get_file_path($filepath, $name, $type = null)
	{
		if ($type) return self::get_file_path($filepath, $name, $type);

		$types = array('file', 'img');
		foreach ($types as $type)
		{
			$file_path = self::get_file_path($filepath, $name, $type);
			if (file_exists($file_path)) return $file_path;
		}

		return false;
	}

	private static function get_file_path($filepath, $name, $type = 'img')
	{
		$raw_dir_path = conf('upload.types.'.$type.'.raw_file_path');

		return $raw_dir_path.$filepath.$name;
	}

	public function test_check_shot_at()
	{
		foreach ($this->files as $file)
		{
			$this->assertNotEquals('0000-00-00 00:00:00', $file->shot_at);
			if (!is_null($file->shot_at)) $this->assertGreaterThanOrEqual(strtotime('-10 years'), strtotime($file->shot_at));
			$this->assertLessThanOrEqual(time(), strtotime($file->shot_at));
		}
	}
}
