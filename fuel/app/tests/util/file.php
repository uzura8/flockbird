<?php

/**
 * Util_File class tests
 *
 * @group App
 * @group Util
 */
class Test_Util_File extends TestCase
{
	/**
	* @dataProvider get_path_partial_provider
	*/
	public function test_get_path_partial($path = null, $length = null, $offset = null, $expected = null)
	{
		$test = Util_File::get_path_partial($path, $length, $offset);
		$this->assertEquals($expected, $test);
	}

	public function get_path_partial_provider()
	{
		$data = array();
		$data[] = array('/aa/bb/cc/dd', 1, null, 'dd');
		$data[] = array('/aa/bb/cc/dd/', 1, null, 'dd');
		$data[] = array('/aa/bb/cc/dd/', 0, null, '');
		$data[] = array('/aa/bb/cc/dd/', 3, null, 'bb/cc/dd');
		$data[] = array('/aa/bb/cc/dd/', 1, 1, 'cc');
		$data[] = array('/aa/bb/cc/dd/', 1, 2, 'bb');
		$data[] = array('/aa/bb/cc/dd/', 1, 3, 'aa');
		$data[] = array('/aa/bb/cc/dd/', 2, 2, 'aa/bb');
		$data[] = array('/aa/bb/cc/dd/', 3, 1, 'aa/bb/cc');

		return $data;
	}

	/**
	* @dataProvider get_extension_from_filename_provider
	*/
	public function test_get_extension_from_filename($filename = null, $expected = null)
	{
		$test = Util_File::get_extension_from_filename($filename);
		$this->assertEquals($expected, $test);
	}

	public function get_extension_from_filename_provider()
	{
		$data = array();
		$data[] = array('aaa.bbb', 'bbb');
		$data[] = array('aaa.bbb.cc', 'cc');
		$data[] = array('aa.bb.c.d.e.f.g.h', 'h');
		$data[] = array('aaa', null);
		$data[] = array('.cc', null);

		return $data;
	}

	/**
	* @dataProvider get_filename_without_extension_provider
	*/
	public function test_get_filename_without_extension($filename = null, $expected = null)
	{
		$test = Util_File::get_filename_without_extension($filename);
		$this->assertEquals($expected, $test);
	}

	public function get_filename_without_extension_provider()
	{
		$data = array();
		$data[] = array('aaa.bbb', 'aaa');
		$data[] = array('aaa.bbb.cc', 'aaa.bbb');
		$data[] = array('aa.bb.c.d.e.f.g.h', 'aa.bb.c.d.e.f.g');
		$data[] = array('aaa', 'aaa');
		$data[] = array('.cc', '.cc');

		return $data;
	}
}
