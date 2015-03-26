<?php
/**
 * FuelPHP LessCSS package implementation.
 */

class Asset extends \Less\Asset
{
	/**
	 * Either adds the Less stylesheet to the group, or returns the CSS tag.
	 *
	 * @param array|string $stylesheets The file name, or an array files.
	 * @param array $attr An array of extra attributes
	 * @param string $group The asset group name
	 * @param bool $raw Whether to return the raw file or not
	 * @param bool $is_force_compile Compile by force regardless of enviroment
	 * @return object|string Rendered asset or current instance when adding to group
	 */
	public static function less($stylesheets = array(), $attr = array(), $group = NULL, $raw = false, $is_force_compile = false)
	{
		return static::instance()->less($stylesheets, $attr, $group, $raw, $is_force_compile);
	}
}
