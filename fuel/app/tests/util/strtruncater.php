<?php

/**
 * Util_StrTruncater class tests
 *
 * @group App
 * @group Util
 */
class Test_Util_StrTruncater extends TestCase
{
	/**
	* @dataProvider execute_provider
	*/
	public function test_exeute($string = null, $limit = null, $continuation = null, $is_html = null, $expected = null)
	{
		$truncater = new Util_StrTruncater;
		$test = $truncater->execute($string, $limit, $continuation, $is_html);
		$this->assertEquals($expected, $test);
	}

	public function execute_provider()
	{
		$data = array();

		$test     = 'abcd';
		$expected = 'ab...';
		$data[] = array($test, 2, '...', false, $expected);

		$test     = 'あいうえお';
		$expected = 'あい...';
		$data[] = array($test, 2, '...', false, $expected);

		$test     = 'あいうえお';
		$expected = 'あい...';
		$data[] = array($test, 2, '...', false, $expected);

		$test     = 'aい<b>cd</b>efg';
		$expected = 'aい<b>c・・・';
		$data[] = array($test, 6, '・・・', false, $expected);

		$test     = 'aい<b>cd</b>efg';
		$expected = 'aい<b...';
		$data[] = array($test, 4, '...', false, $expected);

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

		$test     = 'あい&amp;えお&lt;かき';
		$expected = 'あ...';
		$data[] = array($test, 1, '...', true, $expected);

		$test     = 'あい&amp;えお&lt;かき';
		$expected = 'あい&amp;...';
		$data[] = array($test, 3, '...', true, $expected);

		$test     = 'あい&amp;えお&lt;かき';
		$expected = 'あい&amp;え...';
		$data[] = array($test, 4, '...', true, $expected);

		$test     = 'あい&amp;えお&lt;かき';
		$expected = 'あい&amp;えお&lt;か...';
		$data[] = array($test, 7, '...', true, $expected);

		$test     = 'あい&amp;えお&lt;かき';
		$expected = 'あい&amp;えお&lt;かき';
		$data[] = array($test, 8, '...', true, $expected);

		$test     = 'あい&amp;えお&lt;かき';
		$expected = 'あい&amp;えお&lt;かき';
		$data[] = array($test, 13, '...', true, $expected);

		$test     = 'ab&amp;<b>c</b>d&lt;ef';
		$expected = 'ab...';
		$data[] = array($test, 2, '...', true, $expected);

		$test     = 'ab&amp;<b>c</b>d&lt;ef';
		$expected = 'ab&amp;...';
		$data[] = array($test, 3, '...', true, $expected);

		$test     = 'ab&amp;<b>c</b>d&lt;ef';
		$expected = 'ab&amp;<b>c</b>...';
		$data[] = array($test, 4, '...', true, $expected);

		$test     = 'ab&amp;<b>c</b>d&lt;ef';
		$expected = 'ab&amp;<b>c</b>d...';
		$data[] = array($test, 5, '...', true, $expected);

		$test     = 'ab&amp;<b>c</b>d&lt;ef';
		$expected = 'ab&amp;<b>c</b>d&lt;...';
		$data[] = array($test, 6, '...', true, $expected);

		$test     = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$expected = 'あ<div>&amp;</div>...';
		$data[] = array($test, 2, '...', true, $expected);

		$test     = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$expected = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;</a></div>...';
		$data[] = array($test, 3, '...', true, $expected);

		$test     = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$expected = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>...';
		$data[] = array($test, 5, '...', true, $expected);

		$test     = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$expected = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;...';
		$data[] = array($test, 6, '...', true, $expected);

		$test     = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$expected = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$data[] = array($test, 7, '...', true, $expected);

		$test     = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$expected = 'あ<div>&amp;<a href="http://google.com" target="_blank">&lt;え</a><hr>&amp;</div>&amp;き';
		$data[] = array($test, 8, '...', true, $expected);

		return $data;
	}
}
