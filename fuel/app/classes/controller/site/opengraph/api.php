<?php

class Controller_Site_OpenGraph_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_analysis',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api get_analysis
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_analysis()
	{
		$response = '';
		try
		{
			$this->check_response_format('json');
			$url = \Input::get('url');
			if (!$url || !filter_var($url, FILTER_VALIDATE_URL))
			{
				throw new \HttpBadRequestException;
			}

			$cache_conf = conf('post.url2link.displaySummary.cache');
			$response = Site_OpenGraph::get_analized_data(
				$url,
				$cache_conf['isEnabled'],
				$cache_conf['prefix'],
				$cache_conf['expire']
			);
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\HttpBadRequestException $e)
		{
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
			$status_code = 500;
		}

		$this->response($response, $status_code);
	}
}
