<?php

/**
 * Site_Upload class tests
 *
 * @group App
 * @group Model
 */
class Test_Site_Upload extends TestCase
{
	protected function setUp()
	{
	}

	public function test_check_real_file_info()
	{
		if (!$file_paths = Util_file::get_file_recursive(Config::get('site.upload.types.img.raw_file_path')))
		{
			$this->markTestSkipped('No data.');
		}

		foreach ($file_paths as $file_path)
		{
			$file_info = File::file_info($file_path);
			$file_name = $file_info['basename'];
			$file = Model_File::get4name($file_name);

			// file に対応する Model_File が存在する
			$this->assertNotEmpty($file);

			// path の確認
			$this->assertEquals(trim($file->path, '/'), Util_file::get_path_partial($file_info['dirname'], 2));

			// type の確認
			$this->assertEquals($file->type, $file_info['mimetype']);

			// size の確認
			$this->assertEquals($file->filesize, $file_info['size']);
		}
	}
}
