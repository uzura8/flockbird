<?php
namespace News;

class Site_Util
{
	public static function get_status($is_published, $published_at = null)
	{
		if (!$is_published) return 'closed';
		if (!$published_at) return 'published';
		if ($published_at > date('Y-m-d H:i:s')) return 'reserved';

		return 'published';
	}

	public static function get_status_label_type($status, $is_list_row = false, $default = null)
	{
		switch ($status)
		{
			case 'closed':
				return 'danger';
				break;
			case 'reserved':
				return 'warning';
				break;
			case 'published':
				if ($is_list_row) return '';
				return 'info';
				break;
			default :
				if ($default) return $default;
				return 'info';
				break;
		}
	}

	public static function get_slug()
	{
		$main = date('ymd');
		$suffix = '';
		$slug = $main.$suffix;
		$max = \Config::get('news.max_articles_per_day');
		$i = 0;
		while(Model_News::check_exists4slug($slug))
		{
			if ($i > $max) throw \FuelException('Posted news exceeded the limit on par day.');
			$suffix = \Util_string::get_next_alpha_str($suffix);
			$slug = $main.$suffix;
			$i++;
		}

		return $slug;
	}

	public static function check_editor_enabled($editor_type = null)
	{
		$format_options = (array)conf('form.formats.options', 'news');
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

	public static function convert_format_key2value($editor_type)
	{
		if ($editor_type == 'raw') $editor_type = 'html_editor';

		$options = (array)conf('form.formats.options', 'news');
		foreach ($options as $format_value => $format_type)
		{
			if ($editor_type == $format_type) return $format_value;
		}

		return false;
	}
}
