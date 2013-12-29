<?php

/**
 * Model_File class tests
 *
 * @group App
 * @group Model
 */
class Test_Model_File extends TestCase
{
	/**
	* @dataProvider file_exists_provider
	*/
	public function test_file_exists($file_path)
	{
		$test = file_exists($file_path);
		$this->assertTrue($test);
	}

	public function file_exists_provider()
	{
		$data = array();
		$raw_dir_path = Config::get('site.upload.types.img.raw_file_path');
		$files = Model_File::find('all');
		foreach ($files as $file)
		{
			$data[] = array($raw_dir_path.$file->path.$file->name);
		}

		return $data;
	}

	/**
	* @dataProvider check_file_size_provider
	*/
	public function test_check_file_size($file_path, $expected)
	{
		$test = File::get_size($file_path);
		$this->assertEquals($expected, $test);
	}

	public function check_file_size_provider()
	{
		$data = array();
		$raw_dir_path = Config::get('site.upload.types.img.raw_file_path');
		$files = Model_File::find('all');
		foreach ($files as $file)
		{
			$data[] = array(
				$raw_dir_path.$file->path.$file->name,
				$file->filesize,
			);
		}

		return $data;
	}
}
