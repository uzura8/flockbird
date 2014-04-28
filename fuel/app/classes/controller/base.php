<?php
class ApiNotAuthorizedException extends \FuelException {}

class Controller_Base extends Controller_Hybrid
{
	protected $is_admin = false;
	protected $auth_driver;
	protected $auth_instance;
	protected $auth = 'check_auth';
	protected $after_auth_uri;

	public function before()
	{
		parent::before();

		if (!defined('IS_ADMIN')) define('IS_ADMIN', $this->check_is_admin_request());
		if (!defined('IS_SP')) define('IS_SP', Agent::is_smartphone());
		if (!defined('IS_API')) define('IS_API', Input::is_ajax());

		$this->auth_instance = Auth::forge($this->auth_driver);
		if (!defined('IS_AUTH')) define('IS_AUTH', $this->check_auth(false));
		$this->check_auth_and_redirect();
		$this->set_current_user();

		if (!IS_API)
		{
			$this->set_title_and_breadcrumbs(PRJ_SITE_NAME);
			$this->template->header_keywords = '';
			$this->template->header_description = '';
		}
	}

	protected function check_auth($is_return_true_for_not_auth_action = true)
	{
		if ($is_return_true_for_not_auth_action && $this->check_not_auth_action()) return true;

		if ($this->auth_instance->check($this->auth_driver) === false) return false;
		if ($this->auth_instance->get_user_id() === false) return false;
		list($driver, $user_id) = $this->auth_instance->get_user_id();
		if (!$user_id) return false;

		return true;
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

	protected function check_auth_and_redirect($redirect_uri = '')
	{
		if ($this->check_not_auth_action()) return;
		if (IS_AUTH) return;

		if (IS_API) throw new ApiNotAuthorizedException;

		if (!$redirect_uri || !Util_string::check_uri_for_redilrect($redirect_uri))
		{
			$redirect_uri = $this->get_login_page_uri();
		}

		Session::set_flash('destination', urlencode(Input::server('REQUEST_URI')));
		Response::redirect($redirect_uri);
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
		$action = IS_API ? sprintf('%s_%s', Str::lower(Request::main()->get_method()), Request::active()->action) : Request::active()->action;
		return in_array($action, $this->check_not_auth_action);
	}

	protected function check_is_admin_request()
	{
		if ($this->is_admin) return true;
		if (Module::loaded('admin') && Request::main()->route->module == 'admin') return true;

		return false;
	}

	protected function get_login_page_uri()
	{
		if (IS_ADMIN) return Config::get('site.login_uri.admin');

		return Config::get('site.login_uri.site');
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

	protected function common_FileTmp_post_upload()
	{
		$_method = \Input::get_post('_method');
		if (isset($_method) && $_method === 'DELETE')
		{
			return $this->delete_upload();
		}

		$response = '';
		try
		{
			//Util_security::check_csrf();
			if (!in_array($this->format, array('html', 'json'))) throw new HttpNotFoundException();

			$thumbnail_size = \Input::post('thumbnail_size');
			if (!\Validation::_validation_in_array($thumbnail_size, array('M', 'S'))) throw new HttpInvalidInputException('Invalid input data');;

			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN);
			$uploadhandler = new MyUploadHandler($options, false);
			$files = $uploadhandler->post(false);
			$files['thumbnail_size'] = $thumbnail_size;
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

	protected function common_FileTmp_delete_upload()
	{
		$response = '';
		try
		{
			Util_security::check_csrf();

			$id = (int)Input::post('id');
			if (!$id || !$file_tmp = Model_FileTmp::check_authority($id, $this->u->id, IS_ADMIN ? 1 : 0))
			{
				throw new HttpNotFoundException;
			}

			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN);
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
