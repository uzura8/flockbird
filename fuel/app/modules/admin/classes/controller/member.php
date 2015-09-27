<?php
namespace Admin;

class Controller_Member extends Controller_Admin
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * The index action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{		
		$data = array();

		$query = \Model_Member::query();
		$config = array(
			'uri_segment' => 'page',
			'total_items' => $query->count(),
			'per_page' => conf('articles.member.list.limit', 'admin'),
			'num_links' => 4,
			'show_first' => true,
			'show_last' => true,
		);
		$pagination = \Pagination::forge('mypagination', $config);
		$data['list'] = $query->related('member_auth')
			->order_by('id', 'desc')
			->rows_limit($pagination->per_page)
			->rows_offset($pagination->offset)
			->get();
		$data['pagination'] = $pagination->render();

		$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs(term('member.view', 'site.management'));
		$this->template->content = \View::forge('member/list', $data);
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	Public function action_list()
	{	
		$this->action_index();
	}

	/**
	 * The detail action.
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$id = (int)$id;
		$member = \Model_Member::check_authority($id);
		$member_profiles = \Model_MemberProfile::get4member_id($member->id, true);
		$data = array(
			'is_mypage' => true,
			'access_from' => 'self',
			'member' => $member,
			'member_profiles' => $member_profiles,
			'display_type' => 'detail',
			'is_hide_fallow_btn' => true,
		);
		$this->set_title_and_breadcrumbs($member->name.' さんの詳細', array('admin/member' => term('member.view', 'site.management')));
		$this->template->content = \View::forge('member/home', $data);
	}
}
