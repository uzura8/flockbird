<?php
class ApiNotAuthorizedException extends \FuelException {}
class ValidationFailedException extends \FuelException {}

class Controller_Common extends Controller_Hybrid
{
	protected $response_body;
	protected $api_accept_formats = array('json');
	protected $api_not_check_csrf = false;

	public function before()
	{
		parent::before();

		if (!defined('IS_SSL')) define('IS_SSL', Input::protocol() == 'https');
		if (!defined('IS_SP')) define('IS_SP', \MyAgent\Agent::is_mobile_device());
		if (!defined('IS_API')) define('IS_API', Site_Util::check_is_api());

		$this->check_ssl_required_request_and_redirect();
		$this->check_remote_ip();
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

	protected function force_response($body = null, $status = 200)
	{
		$response = new Response($body, $status);
		$response->send(true);
		exit;
	}

	protected function controller_common_api(callable $func)
	{
		$this->response_body = Site_Controller::get_api_response_body_default();

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
		catch(\HttpAccessBlockedException $e)
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
			$this->response_body['errors']['message'] = $e->getMessage() ?: __('message_update_prohibited');
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
			if (!empty($e)) Util_Toolkit::log_error(is_prod_env() ? $e->getMessage() : $e->__toString());
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

		$load_position = \Input::get('position', 'replace');
		if (!in_array($load_position, array('append', 'prepend', 'replace'))) $load_position = 'replace';

		return array($limit, $page, $load_position);
	}

	public function check_response_format($accept_formats = array())
	{
		if (!$accept_formats) return true;

		if (!is_array($accept_formats)) $accept_formats = (array)$accept_formats;
		if (!in_array($this->format, $accept_formats)) throw new \HttpNotFoundException();

		return true;
	}
}
