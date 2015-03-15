<?php
namespace Thread;

class Site_Model
{
	public static function get_list($limit, $page = 1, $self_member_id = 0)
	{
		$data = Model_Thread::get_pager_list(array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list(0, $self_member_id),
			'limit'    => $limit,
			'order_by' => array('created_at' => 'desc'),
		), $page);
		$data['liked_thread_ids'] = (conf('like.isEnabled') && $self_member_id) ?
			\Site_Model::get_liked_ids('thread', $self_member_id, $data['list']) : array();

		return $data;
	}
}
