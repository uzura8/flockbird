<?php

class Controller_Member_Setting_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * post_config
	 * 
	 * @access  public
	 * @return  Response (json, html)
	 */
	public function post_config($name = null)
	{
		$this->api_accept_formats = array('json', 'html');
		$this->controller_common_api(function() use($name) {
			if (!is_null(Input::post('name'))) $name = Input::post('name');
			$value = Input::post('value');
			self::check_name_format_for_update_config($name);
			$this->check_response_format_for_update_config($name);
			$this->response_body['message'] = self::get_success_message($name);
			$this->response_body['errors']['message_default'] = self::get_error_message_default($name);

			$member_id = (int)$this->u->id;
			if (!$member_config = Model_MemberConfig::get_one4member_id_and_name($member_id, $name))
			{
				$member_config = Model_MemberConfig::forge();
				$member_config->member_id = $member_id;
				$member_config->name = $name;
			}
			$current_value = isset($member_config->value) ? $member_config->value : null;
			$value = self::validate_posted_value($name, $current_value);
			$member_config->value = $value;
			\DB::start_transaction();
			$member_config->save();
			\DB::commit_transaction();

			$response_body = self::get_response_for_update_config($name, array('id' => $member_id, $name => $value));
			$this->response_body = $this->format == 'html' ? $response_body : array(
				'html' => $response_body,
				'message' => sprintf('%sを%sしました。', term('site.display', 'site.setting'), term('form.update')),
			);
		});
	}

	private function check_response_format_for_update_config($name)
	{
		switch ($name)
		{
			case 'timeline_public_flag':
				$this->check_response_format('html');
				break;
			case 'timeline_viewType':
				$this->check_response_format(array('html', 'json'));
				break;
			default :
				break;
		}
	}

	private static function check_name_format_for_update_config($name)
	{
		$accept_names = array('timeline_public_flag', 'timeline_viewType');
		if (!in_array($name, $accept_names)) throw new \HttpNotFoundException();
	}

	private static function validate_posted_value($name, $curret_value = null)
	{
		switch ($name)
		{
			case 'timeline_public_flag':
				$value = Site_Util::validate_posted_public_flag($curret_value);
				break;
			case 'timeline_viewType':
				$value = \Timeline\Site_Model::validate_timeline_viewType(\Input::post('value'));
				break;
			default :
				break;
		}

		return $value;
	}

	private static function get_success_message($name)
	{
		switch ($name)
		{
			case 'timeline_viewType':
				return sprintf('%sの%sしました。', term('site.display', 'site.setting'), term('form.update'));
			case 'timeline_public_flag':
			default :
				break;
		}

		return '';
	}

	private static function get_error_message_default($name)
	{
		switch ($name)
		{
			case 'timeline_viewType':
				return sprintf('%sの%sに%sしました。', term('site.display', 'site.setting'), term('form.update'), term('site.failure'));
				break;
			case 'timeline_public_flag':
			default :
				break;
		}

		return '';
	}

	private static function get_response_for_update_config($name, $data = array())
	{
		switch ($name)
		{
			case 'timeline_public_flag':
				$data['public_flag'] = $data[$name];
				$data['view_icon_only'] = (bool)Input::param('icon_only_flag');
				$data['is_mycontents'] = true;
				$data['without_parent_box'] = true;
				$data['is_use_in_form'] = true;
				$data['parent_box_additional_class'] = 'pull-right';
				$data['post_uri'] = 'member/setting/api/config/timeline_public_flag.html';
				unset($data[$name]);

				return View::forge('_parts/public_flag_selecter', $data)->render();
				break;
			case 'timeline_viewType':
				$data['timeline_viewType'] = $data[$name];

				return View::forge('timeline::member/_parts/timeline_viewType_selecter', $data)->render();
				break;
			default :
				break;
		}
	}
}
