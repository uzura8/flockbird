<?php
namespace Note;

class Site_Model
{
	public static function get_list($limit, $page = 1, $self_member_id = 0, $target_member_obj = null, $is_mypage = false, $is_draft = 0)
	{
		if ($target_member_obj && !$target_member_obj instanceof \Model_Member) throw new InvalidArgumentException('forth parameter is invalid.');

		$is_published = \Util_toolkit::reverse_bool($is_draft, true);
		$data = Model_Note::get_pager_list(array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list(
				$target_member_obj ? $target_member_obj->id : 0,
				$self_member_id ?: 0,
				$is_mypage,
				array(array('is_published', $is_published))
			),
			'limit'    => $limit,
			'order_by' => array('created_at' => 'desc'),
		), $page);
		$data['is_draft']  = $is_draft;
		$data['member']    = $target_member_obj;
		$data['is_mypage'] = $is_mypage;
		$data['liked_note_ids'] = (conf('like.isEnabled') && $self_member_id) ?
			\Site_Model::get_liked_ids('note', $self_member_id, $data['list']) : array();

		return $data;
	}
}

