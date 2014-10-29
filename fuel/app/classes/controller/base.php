<?php
class ApiNotAuthorizedException extends \FuelException {}
class WrongPasswordException extends \FuelException {}

class Controller_Base extends Controller_Hybrid
{
	protected $is_admin = false;
	protected $auth_driver;
	protected $auth_instance;
	protected $auth = 'check_auth';
	protected $after_auth_uri;
	protected $acl_has_access = true;

	public function before()
	{
		parent::before();

		if (!defined('IS_SSL')) define('IS_SSL', Input::protocol() == 'https');
		if (!defined('IS_ADMIN')) define('IS_ADMIN', $this->check_is_admin_request());
		if (!defined('IS_SP')) define('IS_SP', \MyAgent\Agent::is_mobile_device());
		if (!defined('IS_API')) define('IS_API', Input::is_ajax());

		$this->check_ssl_required_request_and_redirect();
		$this->check_remote_ip();
		$this->auth_instance = Auth::forge($this->auth_driver);
		if (!defined('IS_AUTH')) define('IS_AUTH', $this->check_auth(false));
		$this->check_auth_and_response();
		$this->set_current_user();
		$this->set_template_default_data();
	}

	protected function set_template_default_data()
	{
		if (IS_API) return;

		$this->set_title_and_breadcrumbs(PRJ_SITE_NAME);
		$this->template->header_keywords = '';
		$this->template->header_description = '';
	}

	protected function check_ssl_required_request_and_redirect()
	{
		if (IS_SSL) return;
		if (!PRJ_SSL_MODE || !in_array(PRJ_SSL_MODE, array('ALL', 'PARTIAL'))) return;

		$ssl_url = Uri::create(Uri::string_with_query(), array(), array(), true);
		if (PRJ_SSL_MODE == 'ALL') Response::redirect($ssl_url);

		$module = Site_Util::get_module_name();
		if ($module && in_array($module, conf('ssl_required.modules')))
		{
			Response::redirect($ssl_url);
		}
		if (Site_Util::check_ssl_required_uri(Uri::string(), false, false))
		{
			Response::redirect($ssl_url);
		}
	}

	protected function check_remote_ip()
	{
		$module = Site_Util::get_module_name();
		if (empty($GLOBALS['_PRJ_ACCESS_ACCEPT_IPS'][$module])) return;
		if (in_array(\Input::ip(), $GLOBALS['_PRJ_ACCESS_ACCEPT_IPS'][$module])) return;

		if (IS_API)
		{
			$response = new Response(null, 403);
			$response->send();
		}

		Response::redirect('error/403');
	}

	protected function check_auth($is_return_true_for_not_auth_action = true)
	{
		if ($is_return_true_for_not_auth_action && $this->check_not_auth_action()) return true;
		if ($this->auth_instance->check($this->auth_driver) === false) return false;
		if ($this->auth_instance->get_user_id() === false) return false;
		list($driver, $user_id) = $this->auth_instance->get_user_id();
		if (!$user_id) return false;

		if (IS_ADMIN && false == $this->check_acl($is_return_true_for_not_auth_action))
		{
			$this->acl_has_access = false;

			return false;
		}

		return true;
	}

	protected function check_acl($is_return_true_for_not_auth_action = true)
	{
		if ($is_return_true_for_not_auth_action && $this->check_not_auth_action()) return true;

		return \Auth::has_access(sprintf('%s.%s', \Site_Util::get_action_path(), \Input::method()));
	}

	protected function check_auth_and_response()
	{
		$status_code = null;
		try
		{
			$this->check_auth_and_redirect();
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\ApiNotAuthorizedException $e)
		{
			$status_code = 401;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}
		if ($status_code)
		{
			$response = new Response(null, $status_code);
			$response->send();
		}
	}

	protected function set_current_user()
	{
		$this->u = null;
		View::set_global('u', $this->u);
		if ($this->auth_instance->get_user_id() === false) return;

		list($driver, $user_id) = $this->auth_instance->get_user_id();
		$this->u = $this->get_current_user($user_id);
		View::set_global('u', $this->u);
	}

	protected function check_auth_and_redirect($is_check_not_auth_action = true)
	{
		if ($is_check_not_auth_action && $this->check_not_auth_action()) return;
		if (IS_AUTH) return;

		if (IS_API) throw new ApiNotAuthorizedException;
		if (!$this->acl_has_access) throw new HttpForbiddenException;

		Session::set_flash('destination', urlencode(Uri::string_with_query()));
		Response::redirect($this->get_login_page_uri());
	}

	public function login_succeeded($destination = null)
	{
		Session::set_flash('message', 'ログインしました');
		$redirect_uri = urldecode($destination);
		if ($redirect_uri && Util_string::check_uri_for_redilrect($redirect_uri))
		{
			Response::redirect($redirect_uri);
		}
		Response::redirect($this->after_auth_uri);
	}

	protected function check_not_auth_action()
	{
		return in_array(Site_Util::get_action_name(IS_API), $this->check_not_auth_action);
	}

	protected function check_is_admin_request()
	{
		if ($this->is_admin) return true;
		if (Module::loaded('admin') && Request::main()->route->module == 'admin') return true;

		return false;
	}

	protected function get_login_page_uri()
	{
		if (IS_ADMIN) return conf('login_uri.admin');

		return conf('login_uri.site');
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
			list($title_name, $title_label) = static::get_title_parts($title);
			$this->template->title = $is_no_title ? '' : View::forge('_parts/page_title', array('name' => $title_name, 'label' => $title_label));
		}
		$this->template->header_title = $title_name ? site_title($title_name) : '';

		if ($info) $this->template->header_info = View::forge('_parts/information', $info);

		$this->template->breadcrumbs = $is_no_breadcrumbs ? array() :
			static::get_breadcrumbs($title_name, $middle_breadcrumbs, $member_obj, $member_obj ? $this->check_is_mypage($member_obj->id) : false, $module);
	}

	protected static function get_title_parts($title = array())
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

		return array($title_name, $title_label);
	}

	public function common_get_list_params($defaults = array(), $limit_max = 0)
	{
		$limit     = (int)\Input::get('limit', isset($defaults['limit']) ? $defaults['limit'] : conf('view_params_default.list.limit'));
		$is_latest = (bool)\Input::get('latest', isset($defaults['latest']) ? $defaults['latest'] : 0);
		$is_desc   = (bool)\Input::get('desc', isset($defaults['desc']) ? $defaults['desc'] : 0);
		$since_id  = (int)\Input::get('since_id', isset($defaults['since_id']) ? $defaults['since_id'] : 0);
		$max_id    = (int)\Input::get('max_id', isset($defaults['max_id']) ? $defaults['max_id'] : 0);

		if (!$limit_max) $limit_max = conf('view_params_default.list.limit_max');
		if ($limit > $limit_max) $limit = $limit_max;
		if (\Input::get('limit') == 'all') $limit = $limit_max;

		return array($limit, $is_latest, $is_desc, $since_id, $max_id);
	}

	public function common_get_pager_list_params($limit_default, $limit_max = 0, $limit_param_name = 'limit', $page_param_name = 'page')
	{
		$page = (int)\Input::get($page_param_name, 1);
		$limit = (int)\Input::get($limit_param_name, $limit_default);
		if ($limit > $limit_max) $limit = $limit_max;

		return array($limit, $page);
	}


	/**
	 * 以下、site, admin 共通 controller
	 * 
	 */

	protected function common_FileTmp_get_upload()
	{
		$response = '';
		try
		{
			if (!in_array($this->format, array('html', 'json'))) throw new HttpNotFoundException();

			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN);
			$uploadhandler = new MyUploadHandler($options, false);
			$file = $uploadhandler->get(false);
			$status_code = 200;

			if ($this->format == 'html')
			{
				$response = View::forge('filetmp/_parts/upload_image', $file);
				return Response::forge($response, $status_code);
			}
			$response = $files;
		}
		catch(FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	protected function common_FileTmp_post_upload($upload_type = 'img')
	{
		$_method = \Input::param('_method');
		if (isset($_method) && $_method === 'DELETE')
		{
			return $this->common_FileTmp_delete_upload($upload_type);
		}

		$response = '';
		try
		{
			//Util_security::check_csrf();
			if (!in_array($this->format, array('html', 'json'))) throw new HttpNotFoundException();

			if ($upload_type == 'img')
			{
				$thumbnail_size = \Input::post('thumbnail_size');
				if (!\Validation::_validation_in_array($thumbnail_size, array('M', 'S'))) throw new HttpInvalidInputException('Invalid input data');;
			}

			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN, true, null, 0, true, $upload_type);
			$uploadhandler = new MyUploadHandler($options, false);
			$files = $uploadhandler->post(false);
			$files['upload_type'] = $upload_type;
			if ($upload_type == 'img') $files['thumbnail_size'] = $thumbnail_size;
			$status_code = 200;

			if ($this->format == 'html')
			{
				$response = View::forge('filetmp/_parts/upload_images', $files);
				return Response::forge($response, $status_code);
			}
			$response = $files;
		}
		catch(FuelException $e)
		{
			$status_code = 400;
		}

		return $this->response($response, $status_code);
	}

	protected function common_FileTmp_delete_upload($upload_type = 'img')
	{
		$response = '';
		try
		{
			Util_security::check_csrf();

			$id = (int)Input::post('id');
			$file_tmp = Model_FileTmp::check_authority($id, $this->u->id, null, 'member_id', IS_ADMIN ? 1 : 0);

			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN, true, null, 0, true, $upload_type);
			$uploadhandler = new MyUploadHandler($options, false);
			$response = $uploadhandler->delete(false, $file_tmp);
			$status_code = 200;
		}
		catch(HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(FuelException $e)
		{
			$status_code = 400;
		}

		return $this->response($response, $status_code);
	}
}
