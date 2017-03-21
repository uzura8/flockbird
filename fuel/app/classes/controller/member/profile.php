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
		list($is_mypage, $member, $access_from) = $this->check_auth_and_is_mypage($id);
		$member_profiles = Model_MemberProfile::get4member_id($member->id, true);
		$this->set_title_and_breadcrumbs(
			$is_mypage ? t('member.profile_of_myself') : t('member.profile_of', array('label' => $member->name)),
			$is_mypage ? array('member/me' => t('page.mypage')) : array(
				'member/list' => term('member.list'),
				'member/'.$id => t('member.page_of', array('label' => $member->name)),
			),
			$is_mypage ? $member : null, null, array(), false, false, array(
				'title' => t('member.profile_of', array('label' => $member->name)),
				'image' => Site_Util::get_image_uri4file_name($member->get_image(), 'P_L', 'profile'),
			)
		);

		$data = array(
			'member' => $member,
			'is_mypage' => $is_mypage,
			'access_from' => $access_from,
			'member_profiles' => $member_profiles,
		);
		// Report link
		$data['report_data'] = $this->set_global_for_report_form() ? array(
			'member_id' => $member->id,
			'uri' => 'member/profile/'.$member->id,
			'type' => 'member_profile',
			'content' => '',
		) : array();

		$this->template->content = View::forge('member/profile/index', $data);
	}

	/**
	 * Mmeber_profile edit
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_edit($type = null)
	{
		list($type, $is_regist) = self::validate_type($type, $this->u->id);
		$form_member_profile = new Form_MemberProfile($type == 'regist' ? 'regist-config' : 'config', $this->u->group, $this->u);
		$form_member_profile->set_validation();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			try
			{
				$form_member_profile->validate(true);
				\DB::start_transaction();
				$form_member_profile->seve();
				if ($is_regist) Model_MemberConfig::delete_value($this->u->id, 'terms_un_agreement');
				\DB::commit_transaction();
				$message = $is_regist ? __('message_registered_complete') : __('message_edit_complete_for', array('label' => t('profile')));
				$redirect_uri = $is_regist ? $this->after_auth_uri : 'member/profile';
				\Session::set_flash('message', $message);
				\Response::redirect($redirect_uri);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs(
			t($is_regist ? 'site.register_for' : 'form.edit_for', array('label' => t('profile'))),
			$is_regist ? array() : array(
				'member/me' => t('page.mypage'),
				'member/profile' => t('member.profile_of_myself'),
			),
			$is_regist ? null : $this->u
		);
		$this->template->content = View::forge('member/profile/edit', array(
			'is_regist' => $is_regist,
			'val' => $form_member_profile->get_validation(),
			'member_public_flags' => $form_member_profile->get_member_public_flags(),
			'profiles' => $form_member_profile->get_profiles(),
			'member_profile_public_flags' => $form_member_profile->get_member_profile_public_flags(),
		));
	}

	private static function validate_type($type, $member_id)
	{
		if (!$type) $type = 'config';
		if (!in_array($type, array('config', 'regist'))) throw new HttpNotFoundException;

		$terms_un_agreement = (bool)Model_MemberConfig::get_value($member_id, 'terms_un_agreement');
		$is_regist = $type == 'regist' && $terms_un_agreement;

		return array($type, $terms_un_agreement);
	}
}
