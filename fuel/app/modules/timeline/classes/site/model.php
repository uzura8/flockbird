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

	public static function save_timeline($member_id, $public_flag = null, $type = null, $body = null, Model_TimelineData $timeline_data = null, $foreign_table = null, $foreign_id = null, $foreign_column = null)
	{
		$timeline = Model_Timeline::forge();
		$timeline->member_id = $member_id;
		$timeline->public_flag = $public_flag;
		$timeline->is_deleted = 0;
		$timeline->save();

		if (!$timeline_data) $timeline_data = Model_TimelineData::forge();
		$timeline_data->timeline_id = $timeline->id;
		$timeline_data->member_id = $member_id;
		$timeline_data->body = $body;
		$timeline_data->type = $type ?: Site_Util::get_timeline_type($body, $foreign_table);
		if ($foreign_table) $timeline_data->foreign_table = $foreign_table;
		if ($foreign_id) $timeline_data->foreign_id = $foreign_id;
		$timeline_data->save();

		return array($timeline, $timeline_data);
	}
}
