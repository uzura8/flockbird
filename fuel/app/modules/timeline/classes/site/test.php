<?php
namespace Timeline;

class Site_Test
{
	public static function setup_timeline($member_id, $body = null)
	{
		if (is_null($body)) $body = 'This is test.';

		return Site_Model::save_timeline($member_id, PRJ_PUBLIC_FLAG_ALL, 'normal', null, null, $body);
	}
}
