<?php
class ApiNotAuthorizedException extends \FuelException {}

class Controller_Base extends Controller_Hybrid
{
	public function before()
	{
		parent::before();

		if (!defined('IS_ADMIN')) define('IS_ADMIN', Site_Util::get_module_name() == 'admin');
		if (!defined('IS_SP')) define('IS_SP', Agent::is_smartphone());
		if (!defined('IS_API')) define('IS_API', Input::is_ajax());

		if (!IS_API)
		{
			$this->set_title_and_breadcrumbs(PRJ_SITE_NAME);
			$this->template->header_keywords = '';
			$this->template->header_description = '';
		}
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

	public function auth_check_api($is_force_response = false)
	{
		if (!$this->auth_check(true))
		{
			if ($is_force_response)
			{
				$this->force_response(0, 401);
			}
			else
			{
				throw new ApiNotAuthorizedException;
			}
		}
	}

	protected function force_response($body = null, $status = 200)
	{
		$response = new Response($body, $status);
		$response->send(true);
		exit;
	}

	protected function set_title_and_breadcrumbs($title = array(), $middle_breadcrumbs = array(), $member_obj = null, $module = null, $info = array(), $is_no_breadcrumbs = false, $is_no_title = false)
	{
		$title_name = '';
		if ($title)
		{
			if (is_array($title))
			{
				$title_name  = !empty($title['name'])  ? $title['name'] : '';
				$title_label = !empty($title['label']) ? $title['label'] : array();
			}
			else
			{
				$title_name  = $title;
				$title_label = array();
			}
			$this->template->title = $is_no_title ? '' : View::forge('_parts/page_title', array('name' => $title_name, 'label' => $title_label));
		}
		$this->template->header_title = $title_name ? site_title($title_name) : '';

		if ($info) $this->template->header_info = View::forge('_parts/information', $info);

		$breadcrumbs = array();
		if (!$is_no_breadcrumbs)
		{
			$breadcrumbs = array('/' => Config::get('term.toppage'));
			if ($member_obj)
			{
				if ($this->check_is_mypage($member_obj->id))
				{
					$breadcrumbs['/member'] = Config::get('term.myhome');
					if ($module)
					{
						$breadcrumbs[sprintf('/%s/member/', $module)] = '自分の'.\Config::get('term.'.$module).'一覧';
					}
				}
				else
				{
					$prefix = $member_obj->name.'さんの';
					$name = $prefix.Config::get('term.profile');
					$breadcrumbs['/member/'.$member_obj->id] = $name;
					if ($module)
					{
						$key = sprintf('/%s/member/%d', $module, $member_obj->id);
						$breadcrumbs[$key] = $prefix.\Config::get('term.'.$module).'一覧';
					}
				}
			}
			if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
			$breadcrumbs[''] = $title_name;
		}
		$this->template->breadcrumbs = $breadcrumbs;
	}
}
