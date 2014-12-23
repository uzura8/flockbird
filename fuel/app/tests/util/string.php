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

	/**
	* @dataProvider normalize_platform_dependent_chars_provider
	*/
	public function test_normalize_platform_dependent_chars($target = null, $expected = null)
	{
		$test = Util_String::normalize_platform_dependent_chars($target);
		$this->assertEquals($expected, $test);
	}

	public function normalize_platform_dependent_chars_provider()
	{
		$data = array();
		$data[] = array('', '');
		$data[] = array('№', 'No.');
		$data[] = array('①と②と③', '(1)と(2)と(3)');
		$data[] = array('ⅠとⅡとⅢ', 'IとIIとIII');
		$data[] = array('㈱と㌘', '(株)とグラム');

		return $data;
	}

	/**
	* @dataProvider mb_strpos_n_provider
	*/
	public function test_mb_strpos_n($test = null, $needle = null, $num = null, $expected = null)
	{
		$test = Util_String::mb_strpos_n($test, $needle, $num);
		$this->assertEquals($expected, $test);
	}

	public function mb_strpos_n_provider()
	{
		$data = array();

		$test = <<<EOL
あ
い
うう
え

EOL;
		$data[] = array($test, "\n", 3, 6);

		return $data;
	}

	/**
	* @dataProvider truncate_lines_provider
	*/
	public function test_truncate_lines($str = null, $num = null, $trimmarker = null, $is_rtrim = null, $expected = null)
	{
		list($test, $is_truncated) = Util_String::truncate_lines($str, $num, $trimmarker, $is_rtrim);
		$this->assertEquals($expected, $test);
	}

	public function truncate_lines_provider()
	{
		$data = array();
		$test = <<<EOL
あ
い
うう
え


oo

EOL;
		$expected = <<<EOL
あ
い
うう ...
EOL;
		$data[] = array($test, 3, '...', true, $expected);

		$expected = <<<EOL
あ
い
うう
え

...
EOL;
		$data[] = array($test, 6, '...', false, $expected);

		$expected = <<<EOL
あ
い
うう
え ...
EOL;
		$data[] = array($test, 6, '...', true, $expected);

		$expected = <<<EOL
あ
い
うう
え


oo

EOL;
		$data[] = array($test, 10, '...', true, $expected);

		return $data;
	}
}
