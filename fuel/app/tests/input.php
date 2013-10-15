<?php

/**
 * Input class tests
 *
 * @group App
 * @group Input
 */
class Test_Input extends TestCase
{
	public function test_get_post($index = null, $default = null)
	{
		$_POST['hoge'] = 'fuga';
		$_GET['hoge']  = null;
		$test = Input::get_post('hoge');
		$expected = 'fuga';
		$this->assertEquals($expected, $test);

		$_POST['hoge'] = null;
		$_GET['hoge']  = 'fuga';
		$test = Input::get_post('hoge');
		$expected = 'fuga';
		$this->assertEquals($expected, $test);
	}

	public function person_provider()
	{
		return array(
			array('Rintaro', 'male',   '1991/12/14'),
			array('Mayuri',  'female', '1994/2/1'),
			array('Suzuha',  'female', '2017/9/27'),
		);
	}
}
/**
* @dataProvider person_provider
*/
