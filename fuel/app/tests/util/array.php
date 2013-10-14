<?php

/**
 * Util_Array class tests
 *
 * @group App
 * @group Util
 */
class Test_Util_Array extends TestCase
{
	/**
	* @dataProvider array_in_array_provider
	*/
	public function test_array_in_array($targets = null, $haystacks = null, $expected = null)
	{
		$test = Util_Array::array_in_array($targets, $haystacks);
		if ($expected === true)
		{
			$this->assertTrue($test);
		}
		if ($expected === false)
		{
			$this->assertFalse($test);
		}
		else
		{
			$this->assertEquals($expected, $test);
		}
	}

	public function array_in_array_provider()
	{
		$data = array();

		$haystacks = array(
			'a',
			'b',
			'c',
			'd',
			'e',
		);
		$data[] = array(
			array('c', 'e'),
			$haystacks,
			true,
		);
		$data[] = array(
			array('c', 'x'),
			$haystacks,
			false,
		);
		$data[] = array(
			array('c'),
			$haystacks,
			true,
		);
		$data[] = array(
			'c',
			$haystacks,
			true,
		);

		return $data;
	}

	/**
	* @dataProvider get_neighborings_provider
	*/
	public function test_get_neighborings($item = null, $list = null, $expected = null)
	{
		$test = Util_Array::get_neighborings($item, $list);
		$this->assertEquals($expected, $test);
	}

	public function get_neighborings_provider()
	{
		$data = array();

		$list = array(
			'a',
			'b',
			'c',
			'd',
			'e',
		);
		$data[] = array(
			'c',
			$list,
			array('b', 'd'),
		);
		$data[] = array(
			'a',
			$list,
			array(null, 'b'),
		);
		$data[] = array(
			'e',
			$list,
			array('d', null),
		);

		$list = array(
			'a',
			'b',
			'b',
			'c',
			'd',
			'd',
			'e',
			'e',
		);
		$data[] = array(
			'b',
			$list,
			array('a', 'b'),
		);
		$data[] = array(
			'e',
			$list,
			array('d', 'e'),
		);

		return $data;
	}
}
