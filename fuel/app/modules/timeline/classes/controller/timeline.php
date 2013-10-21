<?php
namespace Timeline;

class Controller_Timeline extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'member',
		'detail',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Timeline index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Timeline list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		list($list, $is_next) = Site_Model::get_list(\Auth::check() ? $this->u->id : 0);
		$this->set_title_and_breadcrumbs(sprintf('最新の%s一覧', \Config::get('term.timeline')));
		$this->template->post_footer = \View::forge('_parts/timeline/load_timelines');
		$this->template->content = \View::forge('_parts/timeline/list', array('list' => $list, 'is_next' => $is_next));
	}

	/**
	 * Timeline member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		$member_id = (int)$member_id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);
		list($list, $is_next) = Site_Model::get_list(\Auth::check() ? $this->u->id : 0, $member_id, $is_mypage);

		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('term.timeline')), null, $member);
		$this->template->post_footer = \View::forge('_parts/timeline/load_timelines');
		$this->template->content = \View::forge('_parts/timeline/list', array('member' => $member, 'list' => $list, 'is_next' => $is_next));
	}

	/**
	 * Timeline detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		if (!$timeline = Model_Timeline::check_authority($id)) throw new \HttpNotFoundException;
		$this->check_public_flag($timeline->public_flag, $timeline->member_id);

		$timeline_data = Model_TimelineData::find('first', array(
			'where' => array('timeline_id' => $timeline->id),
			'related' => array('member')
		));
		$this->set_title_and_breadcrumbs(\Config::get('term.timeline').'詳細', null, $timeline->member, 'timeline', null, false, true);
		$this->template->post_footer = \View::forge('_parts/timeline/load_timelines');
		$this->template->content = \View::forge('_parts/timeline/article', array(
			'timeline' => $timeline,
			'timeline_data' => $timeline_data,
			'is_convert_nl2br' => true,
			'delete_uri' => 'timeline/delete/'.$id,
		));
	}

	/**
	 * Timeline delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf();

		if (!$timeline = Model_Timeline::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		$timeline->delete();

		\Session::set_flash('message', \Config::get('term.timeline').'を削除しました。');
		\Response::redirect('member');
	}
}
