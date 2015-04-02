<?php

class Uri extends Fuel\Core\Uri
{

	/**
	 * Returns the full uri with query as a string
	 *
	 * @return  string
	 */
	public static function string_with_query(array $query_data = array(), $is_return_full_path = false)
	{
		$return = $is_return_full_path ? static::base_path(static::string()) : static::string();
		if ($query_data)
		{
			$return .= '?'.http_build_query($query_data);
		}
		elseif ($query = \Input::server('QUERY_STRING'))
		{
			$return .= '?'.$query;
		}

		return $return;
	}

	public static function create_url($string, $query_data = array(), $return_type = 'string')
	{
		if (!in_array($return_type, array('string', 'root_path', 'url')))
		{
			throw new InvalidArgumentException('Third parameter is invalid.');
		}

		switch ($return_type)
		{
			case 'root_path':
				$return = static::base_path($string);
				break;
			case 'url':
				$return = static::create($string);
				break;
			default :
				$return = $string;
				break;
		}
		if ($query_data)
		{
			$delimitter = (strpos($return, '?') === false) ? '?' : '&';
			$return .= $delimitter.http_build_query($query_data);
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
		return FBD_URI_PATH.$path;
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
		if (is_null($secure) && FBD_SSL_MODE) $secure = Site_Util::check_ssl_required_uri($uri, FBD_SSL_MODE == 'ALL');

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
		if (!$absolute_protocol) $url = static::convert_protocol2resuested($url);

		if ($include_index and \Config::get('index_file'))
		{
			$url .= \Config::get('index_file').'/';
		}

		return $url;
	}

	public static function check_current_is_base_path()
	{
		if (!static::string()) return true;
		if (static::string() == '') return true;

		return false;
	}

	public static function convert_protocol2resuested($url)
	{
		if (Input::protocol() != 'https') return $url;

		return preg_replace('#^http://#', 'https://', $url);
	}
}
