<?php
namespace Note;

class Site_Util
{
	public static function get_like_api_uri($note_id)
	{
		return sprintf('note/api/like/%d.json', $note_id);
	}
}
