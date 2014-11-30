<?php
class Util_Parser
{
	protected static $twig_string_parser;

	public static function get_twig_string_parser()
	{
		if (!static::$twig_string_parser)
		{
			require_once APPPATH.'vendor/Twig/Autoloader.php';
			$loader = new Twig_Loader_String();
			static::$twig_string_parser = new Twig_Environment($loader);
		}

		return static::$twig_string_parser;
	}
}
