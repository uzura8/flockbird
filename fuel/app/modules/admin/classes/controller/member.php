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
		list($data['list'], $data['pagination']) = \Site_Model::get_pagenation_list('member');

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
			'hide_fallow_btn' => true,
		);
		$this->set_title_and_breadcrumbs($member->name.' さんの詳細', array('admin/member' => term('member.view', 'site.management')));
		$this->template->subtitle = \View::forge('member/_parts/detail_subtitle', array('member' => $member));
		$this->template->content = \View::forge('member/home', $data);
	}

	/**
	 * News delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		$id = (int)$id;
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			$member = \Model_Member::check_authority($id);
			$message = \Site_Member::remove($member);
			\Session::set_flash('message', $message);
		}
		catch(\EmailValidationFailedException $e)
		{
			\Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
			$error_message = 'メール送信エラー';
		}
		catch(\EmailSendingFailedException $e)
		{
			\Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
			$error_message = 'メール送信エラー';
		}
		catch(\Auth\SimpleUserUpdateException $e)
		{
			$is_transaction_rollback = true;
			$error_message = term('member.view').'が存在しません。';
		}
		catch(\Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = \Site_Controller::get_error_message($e, true);
		}
		catch(\FuelException $e)
		{
			$is_transaction_rollback = true;
			if (!$error_message = $e->getMessage()) $error_message = term('site.left').'に失敗しました。';
		}
		if ($error_message)
		{
			if ($is_transaction_rollback && \DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $error_message);
		}

		\Response::redirect(\Site_Util::get_redirect_uri('admin/member'));
	}
}
