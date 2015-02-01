<?php
namespace Development;

class Controller_Development extends \Controller
{
	public function before()
	{
		parent::before();
		if (is_prod_env()) throw new \HttpForbiddenException;
	}

	/**
	 * The basic welcome message
	 *
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		return \Response::forge(\View::forge('index'));
	}

	public function action_test()
	{
		return \Response::forge(\View::forge('test'));
	}
}
