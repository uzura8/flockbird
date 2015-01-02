<?php
class Site_Test
{
	public static function get_mention_body($mention_member_ids, $body_prefix = null)
	{
		if (!is_array($mention_member_ids)) $mention_member_ids = (array)$mention_member_ids;
		if (is_null($body_prefix)) $body_prefix = 'mention test.';
		$members = \Model_Member::get_basic4ids($mention_member_ids);
		$body_surffix = '';
		foreach ($members as $member)
		{
			$body_surffix .= sprintf(' @%s', $member['name']);
		}

		return $body_prefix.$body_surffix;
	}
}
