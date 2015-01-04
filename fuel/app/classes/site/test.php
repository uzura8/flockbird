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

	public static function save_comment($parent_table, $parent_id, $member_id, $body = null)
	{
		$model = Site_Model::get_model_name($parent_table.'_comment');
		$parent_id_prop = $parent_table.'_id';

		if (is_null($body)) $body = 'This is test comment.';
		$comment = $model::forge(array(
			'body' => $body,
			$parent_id_prop => $parent_id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}
}
