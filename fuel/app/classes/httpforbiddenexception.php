<?php

class HttpForbiddenException extends \HttpException
{
	/**
	 * return a response object for the handle method
	 */
	public function response()
	{
		$response = Request::forge('error/403')->execute()->response();
		
		// This will add the execution time and memory usage to the output.
		// Comment this out if you don't use it.
		$bm = Profiler::app_total();
		$response->body(
			str_replace(
				array('{exec_time}', '{mem_usage}'),
				array(round($bm[0], 4), round($bm[1] / pow(1024, 2), 3)),
				$response->body()
			)
		);
		
		return $response;
	}
}
