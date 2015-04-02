<?php
class Util_Parser
{
	protected static $twig_string_parser;

	public static function get_twig_string_parser()
	{
		if (!static::$twig_string_parser)
		{
			$path_items = array('fuel', 'vendor', 'twig', 'twig', 'lib', 'Twig', 'Autoloader.php');
			require_once FBD_BASEPATH.implode(DS, $path_items);
			$loader = new Twig_Loader_String();
			static::$twig_string_parser = new Twig_Environment($loader);
		}

		return static::$twig_string_parser;
	}
}
