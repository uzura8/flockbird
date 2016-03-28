<?php

class HttpAccessBlockedException extends \HttpException
{
	/**
	 * return a response object for the handle method
	 */
	public function response()
	{
		$response = Request::forge('error/accessblocked')->execute(array($this->getMessage()))->response();
		
		return $response;
	}
}
