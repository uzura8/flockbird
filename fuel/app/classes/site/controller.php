<?php
class Site_Controller
{
	public static function get_title_parts($title = array())
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

	public static function get_error_message($message = null, $is_db_error = false, $default_message = null)
	{
		if (is_null($default_message) && $is_db_error) $default_message = 'データベースエラーが発生しました。';
		if ($is_db_error && is_prod_env()) return $default_message;

		if ($message)
		{
			if (is_string($message))
			{
				return $message;
			}
			elseif (is_callable(array($message, 'getMessage')))
			{
				$message = $message->getMessage();
			}
		}
		if (!$message && $is_db_error && $error_info = DB::error_info())
		{
			$message = sprintf('unified_code:[%s] platform_code:[%s] message: %s', $error_info[0], $error_info[1], $error_info[2]);
		}

		return $message ?: $default_message;
	}

	public static function supply_response_body($response_body = array(), $http_status = null)
	{
		if (!$response_body || !$http_status) return $response_body;
		if (!is_array($response_body)) return $response_body;

		if (isset($response_body['message']) && empty($response_body['message'])) unset($response_body['message']);

		if (in_array($http_status, array(200, 201, 202)))
		{
			$response_body['status'] = 1;
			if (isset($response_body['errors'])) unset($response_body['errors']);

			return $response_body;
		}

		if (empty($response_body['errors']['code'])) $response_body['errors']['code'] = $http_status;
		if (!empty($response_body['errors']['message'])) return $response_body;

		switch ($http_status)
		{
			case 401:
				$message = sprintf('%sの取得に失敗しました。%s後、再度実行してください。', term('site.auth', 'site.info'), term('site.login'));
				break;
			case 400:
			case 403:
			case 404:
			case 405:
				$message = '不正なリクエストです。';
				break;
			case 500:
				$message = !empty($response_body['errors']['message_default']) ? $response_body['errors']['message_default'] : 'サーバ'.term('form.error').'が発生しました。';
				break;
			default :
				$message = !empty($response_body['errors']['message_default']) ? $response_body['errors']['message_default'] : term('form.error').'が発生しました。';
				break;
		}
		if (!empty($response_body['errors']['message_default']))
		{
			if ($http_status != 401) $message = $response_body['errors']['message_default'];
			unset($response_body['errors']['message_default']);
		}
		if (!isset($response_body['errors'])) $response_body['errors'] = array();
		$response_body['errors']['message'] = $message;

		return $response_body;
	}
}
