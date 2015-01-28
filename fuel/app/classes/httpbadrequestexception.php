<?php

class HttpBadRequestException extends \HttpException
{
	/**
	 * return a response object for the handle method
	 */
	public function response()
	{
		$response = Request::forge('error/common', array('message' => '不正なリクエストです。'))
			->execute(array($this->getMessage()))
			->response();
		
		return $response;
	}
}
