<?php

/**
 * Str class tests
 *
 * @group App
 */
class Test_Str extends TestCase
{
	/**
	* @dataProvider truncate_provider
	*/
	public function test_truncate($string = null, $limit = null, $continuation = null, $is_html = null, $expected = null)
	{
		$test = Str::truncate($string, $limit, $continuation, $is_html);
		$this->assertEquals($expected, $test);
	}

	public function truncate_provider()
	{
		$data = array();

		$test     = 'abcd';
		$expected = 'ab...';
		$data[] = array($test, 2, '...', false, $expected);

		$test     = 'あいうえお';
		$expected = 'あい...';
		$data[] = array($test, 2, '...', false, $expected);

		$test     = 'ab<b>cd</b>efg';
		$expected = 'ab<b>c</b>...';
		$data[] = array($test, 3, '...', true, $expected);

		$test     = 'あ<b>いうえ</b>おかき';
		$expected = 'あ<b>いう</b>...';
		$data[] = array($test, 3, '...', true, $expected);

		$test     = 'あ<div>い<b>うえ</b>お</div>かき';
		$expected = 'あ<div>い<b>う</b></div>...';
		$data[] = array($test, 3, '...', true, $expected);

		$test     = 'あ<div>い<b>うえ</b><hr>お</div>かき';
		$expected = 'あ<div>い<b>うえ</b></div>...';
		$data[] = array($test, 4, '...', true, $expected);

		$test     = 'あ<div>い<b>うえ</b><hr>お</div>かき';
		$expected = 'あ<div>い<b>うえ</b><hr>お</div>...';
		$data[] = array($test, 5, '...', true, $expected);

		$test     = 'あ<b>い</b>う<a href="http://google.com/" target="_blank">えお</a>かき';
		$expected = 'あ<b>い</b>う<a href="http://google.com/" target="_blank">え</a>...';
		$data[] = array($test, 4, '...', true, $expected);

		$test     = 'あ<b>い</b>う<a href="http://google.com/" target="_blank">えお</a>かき<br />くけこ';
		$expected = 'あ<b>い</b>う<a href="http://google.com/" target="_blank">えお</a>かき<br />く...';
		$data[] = array($test, 8, '...', true, $expected);

//		$test     = 'ab&amp;<b>c</b>d&lt;ef';
//		$expected = 'ab&amp;<b>c</b>d&lt;...';
//		$data[] = array($test, 6, '...', true, $expected);
//
//		$test     = 'あい&amp;えお&lt;かき';
//		$expected = 'あい&amp;えお&lt;...';
//		$data[] = array($test, 6, '...', true, $expected);

		return $data;
	}
}
