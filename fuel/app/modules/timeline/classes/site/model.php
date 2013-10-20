<?php
namespace Timeline;

class Site_Model
{
	public static function get_list($self_member_id = 0, $target_member_id = 0, $is_mypage = false, $is_mytimeline = false, $last_id = 0, $is_over = false, $limit = 0, $sort = array())
	{
		if (!$limit) $limit = (int)\Config::get('timeline.articles.limit');
		if ($limit > \Config::get('timeline.articles.limit_max')) $limit = \Config::get('timeline.articles.limit_max');
		if (empty($sort)) $sort = array('created_at' => 'desc');

		$basic_cond = \Site_Model::get_where_params4list(
			$target_member_id,
			$self_member_id,
			$is_mypage
		);
		$where = $basic_cond;
		if ($is_mytimeline && $self_member_id)
		{
			$where = array();
			$where['and'] = array();
			$where['and'] = $basic_cond;
			$where['and']['or'] = array('member_id', $self_member_id);
		}
		$params = array('where' => $where, 'order_by' => $sort, 'limit' => $limit);

		return \Site_Model::get_pager_list('timeline', $last_id, $params, 'Timeline', true, $is_over);
	}
}
