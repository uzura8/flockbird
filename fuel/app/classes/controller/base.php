<?php

class Controller_Base extends Controller_Hybrid
{
	public function before()
	{
		parent::before();

		// smartphone アクセス判定
		if (!defined('IS_SP')) define('IS_SP', Agent::is_smartphone());
		if (!defined('IS_API')) define('IS_API', Input::is_ajax());
	}

	protected function check_not_auth_action($is_api = false)
	{
		$action = $is_api ? sprintf('%s_%s', Str::lower(Request::main()->get_method()), Request::active()->action) : Request::active()->action;
		return in_array($action, $this->check_not_auth_action);
	}

	protected function auth_check($is_api = false, $redirect_uri = '', $is_check_not_auth_action = true)
	{
		if ($is_check_not_auth_action && $this->check_not_auth_action($is_api)) return true;
		if (Auth::check()) return true;

		if ($is_api) return false;

		if (!$redirect_uri) $redirect_uri = Site_Util::get_login_page_uri();
		Session::set_flash('destination', urlencode(Input::server('REQUEST_URI')));
		Response::redirect($redirect_uri);
	}

	protected function force_response($body = null, $status = 200)
	{
		$response = new Response($body, $status);
		$response->send(true);
		exit;
	}
}
