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
	protected $response_body;
	protected $check_not_auth_action = array();
	protected $api_accept_formats = array('json');
	protected $api_not_check_csrf = false;

	public function before()
	{
		parent::before();

		if (!defined('IS_SSL')) define('IS_SSL', Input::protocol() == 'https');
		if (!defined('IS_ADMIN')) define('IS_ADMIN', $this->check_is_admin_request());
		if (!defined('IS_SP')) define('IS_SP', \MyAgent\Agent::is_mobile_device());
		if (!defined('IS_API')) define('IS_API', Site_Util::check_is_api());

		$this->set_default_data();
		$this->check_ssl_required_request_and_redirect();
		$this->check_remote_ip();
		$this->auth_instance = Auth::forge($this->auth_driver);
		if (!defined('IS_AUTH')) define('IS_AUTH', $this->check_auth(false));
		$this->check_auth_and_redirect();
		$this->set_current_user();
		$this->check_required_setting_and_redirect();
		self::setup_assets();
	}

	protected function set_default_data()
	{
		if (IS_API)
		{
			$this->response_body = Site_Controller::get_api_response_body_default();
			return;
		}

		$this->template->layout = 'normal';
		$this->set_title_and_breadcrumbs(FBD_SITE_NAME);
		$this->template->header_keywords = '';
		$this->template->header_description = '';
		$this->template->use_angularjs = false;
		View::set_global('renderd_views', array());
	}

	protected function check_ssl_required_request_and_redirect()
	{
		if (IS_SSL) return;
		if (!FBD_SSL_MODE || !in_array(FBD_SSL_MODE, array('ALL', 'PARTIAL'))) return;

		$ssl_url = Uri::create(Uri::string_with_query(), array(), array(), true);
		if (FBD_SSL_MODE == 'ALL') Response::redirect($ssl_url);

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
		if (empty($GLOBALS['_FBD_ACCESS_ACCEPT_IPS'][$module])) return;
		if (in_array(\Input::ip(), $GLOBALS['_FBD_ACCESS_ACCEPT_IPS'][$module])) return;

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

	protected function set_current_user()
	{
		$this->u = null;
		View::set_global('u', $this->u);
		if ($this->auth_instance->get_user_id() === false) return;

		list($driver, $user_id) = $this->auth_instance->get_user_id();
		$this->u = $this->get_current_user($user_id);
		View::set_global('u', $this->u);
	}

	protected function check_required_setting_and_redirect()
	{
		if (IS_ADMIN) return;
		if (IS_API)   return;
		if (!IS_AUTH) return;
		if (check_current_uri('auth/logout')) return;
		if (Site_Util::check_error_response()) return;

		// Force register email.
		if (conf('member.setting.email.forceRegister.isEnabled')
			&& !check_current_uris(conf('member.setting.email.forceRegister.accessableUri'))
			&& empty($this->u->member_auth->email))
		{
			Session::set_flash('message', sprintf('%sが%sです。%sしてください。', term('site.email'), term('site.unregisterd'), term('site.registration')));
			Response::redirect('member/setting/email/regist');
		}

		// Force register required profiles.
		if (conf('member.profile.forceRegisterRequired.isEnabled')
			&& !check_current_uris(conf('member.profile.forceRegisterRequired.accessableUri'))
			&& !Site_Member::check_saved_member_profile_required($this->u))
		{
			Session::set_flash('message', sprintf('%sの%sがあります。%sしてください。', term('site.unregisterd'), term('profile'), term('site.registration')));
			Response::redirect('member/profile/edit/regist');
		}
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
		if (conf('base.isDisplayTopPageWithoutAuth') && check_current_uri(Config::get('routes._root_'), true))
		{
			return true;
		}

		if (conf('base.isClosed') && !check_current_uri($this->get_login_page_uri())) return false;

		return in_array(Site_Util::get_action_name(IS_API), $this->check_not_auth_action);
	}

	protected function check_is_admin_request()
	{
		if ($this->is_admin) return true;
		if (is_enabled('admin') && Request::main()->route->module == 'admin') return true;

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

	protected function controller_common_api(callable $func)
	{
		try
		{
			$this->check_response_format($this->api_accept_formats);
			if (Input::method() != 'GET' && !$this->api_not_check_csrf)
			{
				Util_security::check_csrf();
			}
			$this->response_body = $func() ?: $this->response_body;// execute main.
			if (Site_Model::check_is_orm_obj($this->response_body)) throw new \FuelException('Response body not allowed Orm obj.');
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\ApiNotAuthorizedException $e)
		{
			$status_code = 401;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\HttpMethodNotAllowed $e)
		{
			$status_code = 405;
		}
		catch(\HttpBadRequestException $e)
		{
			$status_code = 400;
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\ValidationFailedException $e)
		{
			$this->response_body['errors']['message'] = Site_Controller::get_error_message($e);
			$status_code = 400;
		}
		catch(\DisableToUpdateException $e)
		{
			$this->response_body['errors']['message'] = $e->getMessage() ?: term('form.update').'が禁止されています。';
			$status_code = 400;
		}
		catch(\Database_Exception $e)
		{
			$this->response_body['errors']['message'] = Site_Controller::get_error_message($e, true);
			$status_code = 500;
		}
		catch(\FuelException $e)
		{
			$status_code = 500;
		}
		catch(\Exception $e)
		{
			$status_code = 500;
		}
		if ($status_code == 500)
		{
			if (!empty($e)) Util_Toolkit::log_error($e->getMessage());
			if (\DB::in_transaction()) \DB::rollback_transaction();
		}
		$response_body = Site_Controller::supply_response_body($this->response_body, $status_code, $this->format);

		return self::response($response_body, $status_code);
	}

	protected function set_response_body_api($data, $view_file = null, $safe_datas = array())
	{
		if (!$view_file)
		{
			$this->response_body = $data;
			return;
		}

		$view = View::forge($view_file, $data);
		if ($safe_datas)
		{
			foreach ($safe_datas as $key => $safe_data)
			{
				$view->set_safe($key, $safe_data);
			}
		}
		$html = $view->render();

		if ($this->format == 'html')
		{
			$this->response_body = $html;
		}
		else
		{
			$this->response_body['html'] = $html;
		}
	}

	protected function set_title_and_breadcrumbs($title = array(), $middle_breadcrumbs = array(), $member_obj = null, $module = null, $info = array(), $is_no_breadcrumbs = false, $is_no_title = false, $ogp_data = array())
	{
		$common = array(
			'title' =>  FBD_SITE_NAME,
			'description' =>  FBD_SITE_DESCRIPTION,
		);
		$title_name = '';
		$this->template->title = '';

		if ($title) list($title_name, $title_label) = Site_Controller::get_title_parts($title);
		if (!$is_no_title && $title_name)
		{
			$this->template->title = View::forge('_parts/page_title', array('name' => $title_name, 'label' => $title_label));
			$common['title'] = $title_name;
		}

		$this->template->header_title = site_title($title_name);
		if ($info) $this->template->header_info = View::forge('_parts/information', $info);

		$this->template->breadcrumbs = $is_no_breadcrumbs ? array() :
			static::get_breadcrumbs($title_name, $middle_breadcrumbs, $member_obj, $member_obj ? $this->check_is_mypage($member_obj->id) : false, $module);

		if (!empty($ogp_data['title'])) $common['title'] = $ogp_data['title'];
		if (!empty($ogp_data['description'])) $common['description'] = $ogp_data['description'];

		View::set_global('common', $common);
	}

	protected static function get_breadcrumbs($title_name = '', $middle_breadcrumbs = array(), $member_obj = null, $is_mypage = false, $module = null)
	{
		$breadcrumbs = IS_ADMIN ? array('admin' => term('admin.view', 'page.top')) : array('/' => term('page.top'));
		if ($member_obj)
		{
			if ($is_mypage)
			{
				$breadcrumbs['/member'] = term('page.myhome');
				if ($module) $breadcrumbs[sprintf('/%s/member/', $module)] = '自分の'.term($module, 'site.list');
			}
			else
			{
				$name = $member_obj->name.'さんのページ';
				$breadcrumbs['/member/'.$member_obj->id] = $name;
				if ($module)
				{
					$key = sprintf('/%s/member/%d', $module, $member_obj->id);
					$breadcrumbs[$key] = term($module, 'site.list');
				}
			}
		}
		if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
		$breadcrumbs[''] = $title_name;

		return $breadcrumbs;
	}

	public function common_get_list_params($defaults = array(), $limit_max = 0, $is_return_assoc = false)
	{
		$limit     = (int)\Input::get('limit', isset($defaults['limit']) ? $defaults['limit'] : conf('view_params_default.list.limit'));
		$is_latest = (bool)\Input::get('latest', isset($defaults['latest']) ? $defaults['latest'] : 0);
		$is_desc   = (bool)\Input::get('desc', isset($defaults['desc']) ? $defaults['desc'] : 0);
		$since_id  = (int)\Input::get('since_id', isset($defaults['since_id']) ? $defaults['since_id'] : 0);
		$max_id    = (int)\Input::get('max_id', isset($defaults['max_id']) ? $defaults['max_id'] : 0);

		if (!$limit_max) $limit_max = conf('view_params_default.list.limit_max');
		if ($limit > $limit_max) $limit = $limit_max;
		if (\Input::get('limit') == 'all') $limit = $limit_max;

		if ($is_return_assoc)
		{
			return array(
				'limit' => $limit,
				'is_latest' => $is_latest,
				'is_desc' => $is_desc,
				'since_id' => $since_id,
				'max_id' => $max_id,
			);
		}

		return array($limit, $is_latest, $is_desc, $since_id, $max_id);
	}

	public function common_get_pager_list_params($limit_default = null, $limit_max = null, $limit_param_name = 'limit', $page_param_name = 'page')
	{
		if (is_null($limit_default)) $limit_default = conf('view_params_default.list.limit');
		if (is_null($limit_max)) $limit_max = conf('view_params_default.list.limit_max');
		$page = (int)\Input::get($page_param_name, 1);
		$limit = (int)\Input::get($limit_param_name, $limit_default);
		if ($limit_max && $limit > $limit_max) $limit = $limit_max;

		return array($limit, $page);
	}

	public function check_response_format($accept_formats = array())
	{
		if (!$accept_formats) return true;

		if (!is_array($accept_formats)) $accept_formats = (array)$accept_formats;
		if (!in_array($this->format, $accept_formats)) throw new \HttpNotFoundException();

		return true;
	}

	protected static function setup_assets()
	{
		if (!is_dev_env()) return;
		if (IS_API) return;

		$configs = \Config::get('less.less_source_files');
		foreach ($configs as $config)
		{
			//compile less
			Asset::less($config);
		}
	}

	/**
	 * 以下、site, admin 共通 controller
	 * 
	 */

	protected function common_FileTmp_get_upload()
	{
		$this->api_accept_formats = array('html', 'json');
		$this->api_not_check_csrf = true;
		$this->controller_common_api(function() {
			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN);
			$uploadhandler = new MyUploadHandler($options, false);
			$file = $uploadhandler->get(false);
			if ($this->format == 'html')
			{
				$this->response_body = View::forge('filetmp/_parts/upload_image', $file)->render();
			}
			else
			{
				$this->response_body = $file;
			}

			return $this->response_body;
		});
	}

	protected function common_FileTmp_post_upload($upload_type = 'img')
	{
		$_method = \Input::param('_method');
		if (isset($_method) && $_method === 'DELETE')
		{
			return $this->common_FileTmp_delete_upload($upload_type);
		}

		$this->api_accept_formats = array('html', 'json');
		$this->api_not_check_csrf = true;
		$this->controller_common_api(function() use($upload_type) {
			if ($upload_type == 'img')
			{
				$thumbnail_size = \Input::post('thumbnail_size');
				if (!\Validation::_validation_in_array($thumbnail_size, array('M', 'S'))) throw new HttpInvalidInputException('Invalid input data');;
				$insert_target = \Input::post('insert_target');
			}
			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN, true, null, 0, true, $upload_type);
			$uploadhandler = new MyUploadHandler($options, false);
			$files = $uploadhandler->post(false);
			$files['upload_type'] = $upload_type;
			if ($upload_type == 'img')
			{
				$files['thumbnail_size'] = $thumbnail_size;
				$files['insert_target'] = $insert_target;
			}
			if ($this->format == 'html')
			{
				$this->response_body = View::forge('filetmp/_parts/upload_images', $files)->render();
			}
			else
			{
				$this->response_body = $files;
			}

			return $this->response_body;
		});
	}

	protected function common_FileTmp_delete_upload($upload_type = 'img')
	{
		$this->controller_common_api(function() use($upload_type) {
			$id = (int)Input::post('id');
			$file_tmp = Model_FileTmp::check_authority($id, $this->u->id, null, 'member_id', IS_ADMIN ? 1 : 0);

			$options = Site_Upload::get_upload_handler_options($this->u->id, IS_ADMIN, true, null, 0, true, $upload_type);
			$uploadhandler = new MyUploadHandler($options, false);
			\DB::start_transaction();
			$this->response_body = $uploadhandler->delete(false, $file_tmp);
			\DB::commit_transaction();

			return $this->response_body;
		});
	}

	/**
	 * Api delete common controller
	 * 
	 * @access  protected
	 * @param   string  $table         Delete target table
	 * @param   int     $id            Delete target record's id
	 * @param   string  $method        Excecuting method name
	 * @param   string  $content_name  Delete target content name for message
	 * @return  Response(json)  
	 */
	protected function api_delete_common($table, $id = null, $method = null, $content_name = '')
	{
		$this->controller_common_api(function() use($table, $id, $method, $content_name)
		{
			if (!$method) $method = 'delete';
			$id = intval(\Input::post('id') ?: $id);
			$model = Site_Model::get_model_name($table);
			$obj = $model::check_authority($id, IS_ADMIN ? 0 : $this->u->id);

			if (is_enabled('album') && $table == 'album')
			{
				if ($result = \Album\Site_Util::check_album_disabled_to_update($obj->foreign_table))
				{
					throw new \DisableToUpdateException($result['message']);
				}
			}
			\DB::start_transaction();
			if ($table == 'timeline')
			{
				$result = \Timeline\Site_Model::delete_timeline($obj, $this->u->id);
			}
			else
			{
				$result = $obj->{$method}();
			}
			\DB::commit_transaction();
			$target_conntent_name = $content_name ?: Site_Model::get_content_name($table);
			$data = array(
				'result'  => (bool)$result,
				'message' => sprintf('%s%sしました。', $target_conntent_name ? $target_conntent_name.'を' : '', term('form.delete')),
			);

			$this->set_response_body_api($data);
		});
	}

}
