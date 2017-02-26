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

		$this->set_title_and_breadcrumbs(term('site.latest', 'timeline.plural'));
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


		$title = $is_mypage ? t('common.own_for_myself_of', array('label' => t('timeline.plural')))
												: t('common.own_for_member_of', array('label' => t('timeline.plural'), 'name' => $member->name));
		$this->set_title_and_breadcrumbs($title, null, $member);
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
		$this->set_title_and_breadcrumbs(t('page.myhome'), null, null, null, null, true, true);
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
		// 通報リンク
		$this->set_global_for_report_form();

		$liked_timeline_ids = (conf('like.isEnabled') && \Auth::check()) ?
			\Site_Model::get_liked_ids('timeline', $this->u->id, array($timeline)) : array();
		list($ogp_title, $ogp_description) = Site_Util::get_timeline_ogp_contents($timeline->type, $timeline->body);
		$this->set_title_and_breadcrumbs(t('timeline.detail'), null, $timeline->member, 'timeline', null, false, true, array(
			'title' => $ogp_title,
			'description' => $ogp_description,
			'image' => Site_Util::get_timeline_ogp_image_uri(
				$timeline->type,
				$timeline->foreign_id,
				$timeline->id,
				true
			),
		));
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

			\Session::set_flash('message', __('message_delete_complete_for', array('label' => t('timeline.view'))));
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
