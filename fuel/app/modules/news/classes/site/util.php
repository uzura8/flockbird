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

	public static function get_status_label_type($status, $default = null)
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
				return 'info';
				break;
			default :
				if ($default) return $default;
				return 'info';
				break;
		}
	}
}
