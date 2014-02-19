<?php

class Controller_Member_Profile extends Controller_Member
{
	protected $check_not_auth_action = array(
		'index',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber_profile index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index($id = null)
	{
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($id);
		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('profile')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('member/profile/_parts/profile_subtitle') : '';
		$this->template->content = View::forge('member/profile/index', array('member' => $member, 'is_mypage' => $is_mypage));
	}

	/**
	 * Mmeber_profile edit
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_edit()
	{
		$member_profile = new Site_MemberProfile('config', $this->u->id, $this->u->name);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			try
			{
				$member_profile->validate_public_flag();
				$member_profile->remove_unique_restraint_for_updated_value();
				if (!$member_profile->validate()) throw new \FuelException($member_profile->get_validation_errors());
				\DB::start_transaction();
				$member_profile->seve(true);
				\DB::commit_transaction();

				\Session::set_flash('message', term('profile').'を編集しました。');
				\Response::redirect('member/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs(term('profile').term('form.edit'), array('member/profile' => '自分の'.term('profile')), $this->u);
		$this->template->content = View::forge('member/profile/edit', array(
			'val' => $member_profile->get_validation($this->u->name),
			'profiles' => $member_profile->get_profiles(),
			'public_flags' => $member_profile->get_member_profile_public_flags(),
		));
	}
}
