<?php

/**
 * Site_Util class tests
 *
 * @group App
 * @group Model
 */
class Test_Site_Util extends TestCase
{
	/**
	* @dataProvider check_is_expanded_public_flag_range_provider
	*/
	public function test_check_is_expanded_public_flag_range($original_public_flag = null, $changed_public_flag = null, $expected = null)
	{
		$test = Site_Util::check_is_expanded_public_flag_range($original_public_flag, $changed_public_flag);
		$this->assertEquals($expected, $test);
	}

	//define('FBD_PUBLIC_FLAG_PRIVATE', 0);
	//define('FBD_PUBLIC_FLAG_ALL',     1);
	//define('FBD_PUBLIC_FLAG_MEMBER',  2);
	////define('FBD_PUBLIC_FLAG_FRIEND',  3);
	public function check_is_expanded_public_flag_range_provider()
	{
		$data = array();
		$data[] = array(0, 0, false);
		$data[] = array(0, 1, true);
		$data[] = array(0, 2, true);
		$data[] = array(2, 0, false);
		$data[] = array(2, 1, true);
		$data[] = array(2, 2, false);
		$data[] = array(1, 0, false);
		$data[] = array(1, 1, false);
		$data[] = array(1, 2, false);

		return $data;
	}

	/**
	* @dataProvider check_is_reduced_public_flag_range_provider
	*/
	public function test_check_is_reduced_public_flag_range($original_public_flag = null, $changed_public_flag = null, $expected = null)
	{
		$test = Site_Util::check_is_reduced_public_flag_range($original_public_flag, $changed_public_flag);
		$this->assertEquals($expected, $test);
	}

	//define('FBD_PUBLIC_FLAG_PRIVATE', 0);
	//define('FBD_PUBLIC_FLAG_ALL',     1);
	//define('FBD_PUBLIC_FLAG_MEMBER',  2);
	////define('FBD_PUBLIC_FLAG_FRIEND',  3);
	public function check_is_reduced_public_flag_range_provider()
	{
		$data = array();
		$data[] = array(0, 0, false);
		$data[] = array(0, 1, false);
		$data[] = array(0, 2, false);
		$data[] = array(2, 0, true);
		$data[] = array(2, 1, false);
		$data[] = array(2, 2, false);
		$data[] = array(1, 0, true);
		$data[] = array(1, 1, false);
		$data[] = array(1, 2, true);

		return $data;
	}
}
