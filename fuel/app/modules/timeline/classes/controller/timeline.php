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
		$data = \Timeline\Site_Util::get_list4view(
			\Auth::check() ? $this->u->id : 0,
			0, false, null,
			$this->common_get_list_params(array(
				'desc' => 1,
				'latest' => 1,
				'limit' => conf('articles.limit', 'timeline'),
			), conf('articles.limit_max', 'timeline'), true)
		);

		$this->set_title_and_breadcrumbs(term('site.latest', 'timeline', 'site.list'));
		$this->template->post_footer = \View::forge('_parts/load_timelines');
		$this->template->content = \View::forge('_parts/list', $data);
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
		$data = \Timeline\Site_Util::get_list4view(
			\Auth::check() ? $this->u->id : 0,
			$member->id, false, null,
			$this->common_get_list_params(array(
				'desc' => 1,
				'latest' => 1,
				'limit' => conf('articles.limit', 'timeline'),
			), conf('articles.limit_max', 'timeline'), true)
		);
		if ($member) $data['member'] = $member;

		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('timeline', 'site.list')), null, $member);
		$this->template->post_footer = \View::forge('_parts/load_timelines');
		$this->template->content = \View::forge('_parts/list', $data);
	}

	/**
	 * Mmeber home
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_myhome()
	{
		$this->template->post_header = \View::forge('member/_parts/myhome_header');
		$this->template->post_footer = \View::forge('member/_parts/myhome_footer');
		$this->set_title_and_breadcrumbs(term('page.myhome'), null, null, null, null, true, true);
		$this->template->content = \View::forge('member/myhome', array('public_flag' => $this->member_config->timeline_public_flag));
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
		$timeline = Model_Timeline::check_authority($id);
		$this->check_browse_authority($timeline->public_flag, $timeline->member_id);

		// 既読処理
		if (\Auth::check()) $this->change_notice_status2read($this->u->id, 'timeline', $id);

		$liked_timeline_ids = (conf('like.isEnabled') && \Auth::check()) ?
			\Site_Model::get_liked_ids('timeline', $this->u->id, array($timeline)) : array();
		$this->set_title_and_breadcrumbs(term('timeline', 'site.detail'), null, $timeline->member, 'timeline', null, false, true);
		$this->template->post_footer = \View::forge('_parts/load_timelines');
		$this->template->content = \View::forge('_parts/article', array(
			'timeline_id' => $timeline->id,
			'type' => $timeline->type,
			'member_id' => $timeline->member_id,
			'self_member_id' => \Auth::check() ? $this->u->id : 0,
			'liked_timeline_ids' => $liked_timeline_ids,
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
		try
		{
			\Util_security::check_method('POST');
			\Util_security::check_csrf();
			if (\Input::post('id')) $id = (int)\Input::post('id');

			\DB::start_transaction();
			$timeline = Model_Timeline::check_authority($id, $this->u->id);
			Site_Model::delete_timeline($timeline, $this->u->id);
			\DB::commit_transaction();

			\Session::set_flash('message', term('timeline').'を削除しました。');
			\Response::redirect('timeline/member');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('timeline/'.$id);
	}
}
