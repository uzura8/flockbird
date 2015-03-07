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
	 * get_analysis
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_analysis()
	{
		$this->controller_common_api(function()
		{
			if (!$url = \Input::get('url')) throw new \HttpBadRequestException;
			if (!filter_var($url, FILTER_VALIDATE_URL)) throw new \ValidationFailedException('URLが正しくありません。');

			$cache_conf = conf('post.url2link.displaySummary.cache');
			$response = Site_OpenGraph::get_analized_data(
				$url,
				$cache_conf['isEnabled'],
				$cache_conf['prefix'],
				$cache_conf['expire']
			);

			return $response;
		});
	}
}
