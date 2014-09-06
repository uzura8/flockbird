<?php

/**
 * Model_FileTmp class tests
 *
 * @group App
 * @group Model
 */
class Test_Model_FileTmp extends TestCase
{
	protected $files = array();

	protected function setUp()
	{
		if (!$this->files = \Model_FileTmp::find('all'))
		{
			$this->markTestSkipped('No data.');
		}
	}

	public function test_file_exists()
	{
		foreach ($this->files as $file)
		{
			if (!$file_path = self::check_and_get_file_path($file->path, $file->name)) continue;
			$this->assertFileExists($file_path);
		}
	}

	public function test_check_file_size()
	{
		foreach ($this->files as $file)
		{
			$file_path = self::check_and_get_file_path($file->path, $file->name);
			if (!file_exists($file_path)) continue;

			$test = File::get_size($file_path);
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
	//		$test = exif_read_data(self::check_and_get_file_path($file->path, $file->name));
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
		$raw_dir_path = conf('upload.types.'.$type.'.tmp.raw_file_path');

		return $raw_dir_path.$filepath.$name;
	}

	public function test_check_shot_at()
	{
		foreach ($this->files as $file)
		{
			if (!$file->shot_at) continue;

			$this->assertNotEquals('0000-00-00 00:00:00', $file->shot_at);
			$this->assertGreaterThanOrEqual(strtotime('-10 years'), strtotime($file->shot_at));
			$this->assertLessThanOrEqual(time(), strtotime($file->shot_at));
		}
	}
}
