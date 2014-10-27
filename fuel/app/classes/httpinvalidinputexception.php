<?php

class HttpInvalidInputException extends \HttpException
{
	/**
	 * return a response object for the handle method
	 */
	public function response()
	{
		$response = Request::forge('error/invalid')->execute(array($this->getMessage()))->response();
		
		return $response;
	}
}
