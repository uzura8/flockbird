<?php

/**
 * Validation class tests
 *
 * @group App
 */
class Test_Validation extends TestCase
{
	/**
	* @dataProvider validation_no_platform_dependent_chars_provider
	*/
	public function test_validation_no_platform_dependent_chars($input = null, $input_encording = null, $expected = null)
	{
		$test = Validation::_validation_no_platform_dependent_chars($input, $input_encording);
		$this->assertEquals($expected, $test);
	}

	public function validation_no_platform_dependent_chars_provider()
	{
		$data = array();
		$data[] = array(0,     null, true);
		$data[] = array('',    null, true);
		$data[] = array(' ',   null, true);
		$data[] = array('　',  null, true);
		$data[] = array('a',   null, true);
		$data[] = array('[',   null, true);
		$data[] = array("\n",  null, true);
		$data[] = array('①',   null, false);
		$data[] = array('⊿',   null, false);
		$data[] = array('◉',   null, false);

		return $data;
	}
}
