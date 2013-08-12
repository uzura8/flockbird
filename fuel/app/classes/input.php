<?php

class Input extends Fuel\Core\Input
{
	/**
	 * Gets the specified GET or POST variable.
	 *
	 * @param   string  $index    The index to get
	 * @param   string  $default  The default value
	 * @return  string|array
	 */
	public static function get_post($index = null, $default = null)
	{
		if ($post = Input::post($index)) return $post;
		if ($get  = Input::get($index)) return $get;

		return $default;
	}

	/**
	 * Ailias to self::get_post.
	 *
	 * @param   string  $index    The index to get
	 * @param   string  $default  The default value
	 * @return  string|array
	 */
	public static function post_get($index = null, $default = null)
	{
		return self::get_post($index, $default);
	}
}
