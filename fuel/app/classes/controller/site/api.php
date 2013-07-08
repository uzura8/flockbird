<?php

class Controller_Site_Api extends Controller_Base_Api
{
	public function before()
	{
		parent::before();
		$this->set_current_user();
	}

	public function auth_check_api()
	{
		if (!$this->auth_check(false)) throw new \SiteApiNotAuthorizedException;
	}

	private function set_current_user()
	{
		$auth = Auth::instance();
		$this->u = Auth::check() ? Model_Member::find()->where('id', $auth->get_member_id())->related('memberauth')->get_one() : null;

		View::set_global('u', $this->u);
	}

	protected function check_auth_api_and_is_mypage($member_id = 0)
	{
		$is_mypage = false;
		$member    = null;

		if (!$member_id)
		{
			$this->auth_check_api();

			$is_mypage = true;
			$member = $this->u;
		}
		elseif ($this->check_is_mypage($member_id))
		{
			$is_mypage = true;
			$member = $this->u;
		}
		elseif (!$member = Model_Member::check_authority($member_id))
		{
			throw new \HttpNotFoundException;
		}

		return array($is_mypage, $member);
	}

	protected function check_is_mypage($member_id)
	{
		return (Auth::check() && $member_id == $this->u->id);
	}

	/**
	 * Api login
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_login()
	{
		if ($this->format != 'html') throw new HttpNotFoundException();

		$destination = Input::get('destination', '');

		$response = '';
		try
		{
			$form = Site_Form::login($destination, 'login-popover', false, 'input-xlarge');
			$response = View::forge('site/_parts/login');
			$response->set_safe('html_form', $form->build('site/login'));
			$status_code = 200;

			return Response::forge($response, $status_code);
		}
		catch(Exception $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}

class SiteApiNotAuthorizedException extends \FuelException {}
