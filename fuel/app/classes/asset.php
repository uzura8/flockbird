<?php
/**
 * FuelPHP LessCSS package implementation.
 */

class Asset extends Fuel\Core\Asset
{
	/**
	 * CSS
	 *
	 * Either adds the stylesheet to the group, or returns the CSS tag.
	 *
	 * @access	public
	 * @param	mixed	The file name, or an array files.
	 * @param	array	An array of extra attributes
	 * @param	string	The asset group name
	 * $raw		bool	If set to true the result css tags will include the file contents directly instead of via a link tag.
	 * $is_minify	bool	If set to true minify and combine files at only prod env.
	 * @return	string
	 */
	public static function css($stylesheets = array(), $attr = array(), $group = NULL, $raw = false, $is_minify = false, $is_use_minified_file = false, $is_less_compiled = false)
	{
		if (!is_array($stylesheets)) $stylesheets = (array)$stylesheets;

		if ($is_less_compiled)
		{
			foreach ($stylesheets as $key => $stylesheet)
			{
				$stylesheets[$key] = 'cache/'.$stylesheet;
			}
		}
		if (Fuel::$env == Fuel::PRODUCTION)
		{
			if ($is_minify)
			{
				foreach ($stylesheets as $stylesheet)
				{
					Casset::css($stylesheet, false, $group);
				}
				return;
			}
			if ($is_use_minified_file)
			{
				foreach ($stylesheets as $key => $stylesheet)
				{
					$stylesheets[$key] = Util_File::convert_filename2min($stylesheet);
				}
			}
		}

		return parent::css($stylesheets, $attr, $group, $raw);
	}

	/**
	 * JS
	 *
	 * Either adds the javascript to the group, or returns the script tag.
	 *
	 * @access	public
	 * @param	mixed	The file name, or an array files.
	 * @param	array	An array of extra attributes
	 * @param	string	The asset group name
	 * $raw		bool	If set to true the result css tags will include the file contents directly instead of via a link tag.
	 * $is_minify	bool	If set to true minify and combine files at only prod env.
	 * @return	string
	 */
	public static function js($stylesheets = array(), $attr = array(), $group = NULL, $raw = false, $is_minify = false, $is_use_minified_file = false)
	{
		if (Fuel::$env == Fuel::PRODUCTION)
		{
			if ($is_minify)
			{
				foreach ($stylesheets as $stylesheet)
				{
					Casset::js($stylesheet, false, $group);
				}
				return;
			}
			if ($is_use_minified_file) $stylesheets = Util_File::convert_filename2min($stylesheets);
		}

		return parent::js($stylesheets, $attr, $group, $raw);
	}

	/**
	 * Renders the given group.  Each tag will be separated by a line break.
	 * You can optionally tell it to render the files raw.  This means that
	 * all CSS and JS files in the group will be read and the contents included
	 * in the returning value.
	 *
	 * @param   mixed   the group to render
	 * @param   bool    whether to return the raw file or not
	 * @return  string  the group's output
	 */
	public static function render($group = null, $raw = false, $minify_type = null)
	{
		if (Fuel::$env == Fuel::PRODUCTION && $group && $minify_type)
		{
			if (!in_array($minify_type, array('js', 'css'))) throw new InvalidArgumentException('Third parameter is invalid.');
			$method = 'render_'.$minify_type;
			return Casset::$method($group);
		}

		return parent::render($group, $raw);
	}
}
