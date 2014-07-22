<?php

class Uri extends Fuel\Core\Uri
{

	/**
	 * Returns the full uri with query as a string
	 *
	 * @return  string
	 */
	public static function string_with_query()
	{
		$return = static::string();
		if ($query = \Input::server('QUERY_STRING'))
		{
			$return .= '?'.$query;
		}

		return $return;
	}

	/**
	 * Returns the full uri path as a string
	 *
	 * @return  string
	 */
	public static function base_path($path = null)
	{
		return PRJ_URI_PATH.$path;
	}

	/**
	 * Creates a url with the given uri, including the base url
	 *
	 * @param   string  $uri            The uri to create the URL for
	 * @param   array   $variables      Some variables for the URL
	 * @param   array   $get_variables  Any GET urls to append via a query string
	 * @param   bool    $secure         If false, force http. If true, force https
	 * @return  string
	 */
	public static function create($uri = null, $variables = array(), $get_variables = array(), $secure = null)
	{
		if (is_null($secure) && PRJ_SSL_MODE) $secure = Site_Util::check_ssl_required_uri($uri, PRJ_SSL_MODE == 'ALL');

		return parent::create($uri, $variables, $get_variables, $secure);
	}

	/**
	 * Gets the base URL, including the index_file if wanted.
	 *
	 * @param   bool    $include_index  Whether to include index.php in the URL
	 * @param   bool    $absolute_protocol  If false, change to requested protocol.
	 * @return  string
	 */
	public static function base($include_index = true, $absolute_protocol = false)
	{
		$url = \Config::get('base_url');
		if (!$absolute_protocol && Input::protocol() == 'https')
		{
			$url = preg_replace('#^http://#', 'https://', $url);
		}

		if ($include_index and \Config::get('index_file'))
		{
			$url .= \Config::get('index_file').'/';
		}

		return $url;
	}
}
