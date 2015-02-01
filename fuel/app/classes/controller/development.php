<?php
/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Development extends Controller
{
	public function before()
	{
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
if (1) {
$isActive = 1;
$isExit   = 0;
$isEcho   = 0;
$is_html  = 0;
$isAdd    = 1;
$a = '';
if ($isActive) {$fhoge = "/tmp/test.log";$_type = 'wb';if ($isAdd) $_type = 'a';$fp = fopen($fhoge, $_type);ob_start();if ($is_html) echo '<pre>';
//if () var_dump(__LINE__, $a);// !!!!!!!
//var_dump(__LINE__, $e->getMessage());// !!!!!!!
var_dump(__LINE__);// !!!!!!!
if ($is_html) echo '</pre>';$out=ob_get_contents();fwrite( $fp, $out . "\n" );ob_end_clean();fclose( $fp );if ($isEcho) echo $out;if ($isExit) exit;}}
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//		parent::before();
		if (!is_prod_env()) throw new HttpExceptionForbidden;
	}

	/**
	 * The basic welcome message
	 *
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		return Response::forge(View::forge('development/index'));
	}

	public function action_test()
	{
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
if (1) {
$isActive = 1;
$isExit   = 0;
$isEcho   = 0;
$is_html  = 0;
$isAdd    = 1;
$a = '';
if ($isActive) {$fhoge = "/tmp/test.log";$_type = 'wb';if ($isAdd) $_type = 'a';$fp = fopen($fhoge, $_type);ob_start();if ($is_html) echo '<pre>';
//if () var_dump(__LINE__, $a);// !!!!!!!
//var_dump(__LINE__, $e->getMessage());// !!!!!!!
var_dump(__LINE__);// !!!!!!!
if ($is_html) echo '</pre>';$out=ob_get_contents();fwrite( $fp, $out . "\n" );ob_end_clean();fclose( $fp );if ($isEcho) echo $out;if ($isExit) exit;}}
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		return Response::forge(View::forge('development/index'));
	}
}
