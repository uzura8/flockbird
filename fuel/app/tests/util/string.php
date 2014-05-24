<?php

/**
 * Util_String class tests
 *
 * @group App
 * @group Util
 */
class Test_Util_String extends TestCase
{
	/**
	* @dataProvider get_next_alpha_str_provider
	*/
	public function test_get_next_alpha_str($targets = null, $expected = null)
	{
		$test = Util_String::get_next_alpha_str($targets);
		$this->assertEquals($expected, $test);
	}

	public function get_next_alpha_str_provider()
	{
		$data = array();
		$data[] = array('a', 'b');
		$data[] = array('z', 'aa');
		$data[] = array('aa', 'ab');
		$data[] = array('abz', 'abaa');
		$data[] = array('abay', 'abaz');
		$data[] = array('', 'a');
		$data[] = array('[', 'a');
		$data[] = array('2', 'a');

		return $data;
	}
}
