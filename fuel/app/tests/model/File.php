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
			$this->markTestSkipped('No data.');
		}
	}

	public function test_file_exists()
	{
		foreach ($this->files as $file)
		{
			$this->assertFileExists($this->get_file_path($file->path, $file->name));
		}
	}

	public function test_check_file_size()
	{
		foreach ($this->files as $file)
		{
			$test = File::get_size($this->get_file_path($file->path, $file->name));
			$this->assertEquals($file->filesize, $test);
		}
	}

	public function test_check_removed_exif()
	{
		if (!is_callable('exif_read_data'))
		{
			$this->markTestSkipped('No data.');
		}

		foreach ($this->files as $file)
		{
			$test = exif_read_data($this->get_file_path($file->path, $file->name));
			$this->assertFalse($test);
		}
	}

	private function get_file_path($filepath, $name)
	{
		$raw_dir_path = conf('upload.types.img.raw_file_path');

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
