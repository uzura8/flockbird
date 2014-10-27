<?php

class HttpForbiddenException extends \HttpException
{
	/**
	 * return a response object for the handle method
	 */
	public function response()
	{
		$response = Request::forge('error/403')->execute(array($this->getMessage()))->response();
		
		return $response;
	}
}
