<?php
namespace Notice;

class Site_Util
{
	public static function get_accept_timeline_foreign_tables()
	{
		return array(
			'note',
			'note_comment',
			'album',
			'album_image',
			'album_image_comment',
		);
	}
}
