<?php

class HttpBadRequestException extends \HttpException
{
	/**
	 * return a response object for the handle method
	 */
	public function response()
	{
		$response = Request::forge('error/common', array('message' => __('message_error_bad_request')))
			->execute(array($this->getMessage()))
			->response();
		
		return $response;
	}
}
