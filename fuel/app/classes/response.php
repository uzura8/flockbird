<?php

class Response extends Fuel\Core\Response
{
	public static function redirect($uri = null, $method = 'location', $code = 302)
	{
		$url = '';
		is_null($uri) and $uri = \Uri::string();

		// If the given uri is a full URL
		if(preg_match("#^(http|https|ftp)://#i", $uri))
		{
			$url = $uri;
		}
		else
		{
			$is_secure = FBD_SSL_MODE && Site_Util::check_ssl_required_uri($uri, FBD_SSL_MODE == 'ALL');
			$url = $is_secure ? 'https://' : 'http://';
			$url .= FBD_PUNYCODE ?: FBD_DOMAIN;
			$url .= \Uri::base_path($uri);
		}

		parent::redirect($url, $method, $code);
	}
}

