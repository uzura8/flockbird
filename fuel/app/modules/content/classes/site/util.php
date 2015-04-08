<?php
namespace Content;

class Site_Util
{
	public static function check_editor_enabled($editor_type = null)
	{
		$format_options = (array)conf('page.form.formats.options', 'content');
		if (is_null($editor_type))
		{
			foreach ($format_options as $format_value => $format_type)
			{
				if (in_array($format_value, array(1, 2))) return true;
			}

			return false;
		}

		if (is_numeric($editor_type))
		{
			return (bool)$editor_type;
		}

		return in_array($editor_type, array('html_editor', 'markdown'));
	}

	public static function convert_format_key2value(string $editor_type)
	{
		$options = (array)conf('page.form.formats.options', 'content');
		foreach ($options as $format_value => $format_type)
		{
			if ($editor_type == $format_type) return $format_value;
		}

		return false;
	}
}
