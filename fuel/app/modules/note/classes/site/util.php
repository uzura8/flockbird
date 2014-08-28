<?php
namespace Note;

class Site_Util
{
	public static function get_like_api_uri($note_id)
	{
		return sprintf('note/like/api/update/%d.json', $note_id);
	}

	public static function get_liked_member_api_uri($note_id)
	{
		return sprintf('note/like/api/member/%d.html', $note_id);
	}
}
