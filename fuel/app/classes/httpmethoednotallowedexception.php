<?php

class HttpMethodNotAllowedException extends \HttpException
{
	/**
	 * return a response object for the handle method
	 */
	public function response()
	{
		$response = Request::forge('error/405')->execute(array($this->getMessage()))->response();
		
		return $response;
	}
}
