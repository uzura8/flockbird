<?php
namespace Notice;

class Site_Util
{
	public static function get_accept_foreign_tables()
	{
		return array(
			'note',
			'album',
			'album_image',
			'timeline',
		);
	}

	public static function get_notice_type($type_key)
	{
		$types = \Config::get('notice.types');
		if (empty($types[$type_key])) throw new \InvalidArgumentException('Parameter is invalid.');

		return $types[$type_key];
	}

	public static function get_notice_body($foreign_table, $type_key)
	{
		return '';
	}
}
