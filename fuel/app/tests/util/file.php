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
}
