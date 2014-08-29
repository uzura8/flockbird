<?php
class DisableToUpdatePublicFlagException extends \FuelException {}

class Controller_Site_Api extends Controller_Base_Site
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	public function check_response_format($accept_formats = array())
	{
		if (!$accept_formats) return true;

		if (!is_array($accept_formats)) $accept_formats = (array)$accept_formats;
		if (!in_array($this->format, $accept_formats)) throw new \HttpNotFoundException();

		return true;
	}


	/**
	 * 以下、共通 controller
	 * 
	 */

	protected function get_liked_member_list($like_model, $parent_model, $parent_id, $parent_id_prop, $get_uri)
	{
		$response = '';
		try
		{
			$this->check_response_format(array('json', 'html'));

			$parent_id = (int)$parent_id;
			$parent_obj = $parent_model::check_authority($parent_id);
			$this->check_browse_authority($parent_obj->public_flag, $parent_obj->member_id);

			list($limit, $params, $is_desc, $class_id) = $this->common_get_list_params(array('desc' => 1), conf('view_params_default.list.limit.limit_max'));
			$params[$parent_id_prop] = $parent_id;
			list($list, $next_id) = $like_model::get_list($params, $limit, 'member', ($this->format == 'json'), false, $is_desc);

			$status_code = 200;
			if ($this->format == 'html')
			{
				return \Response::forge(\View::forge('_parts/member_list', array(
					'list' => $list,
					'related_member_table_name' => 'member',
					'next_id' => $next_id,
					'is_simple_list' => true,
					'list_id' => 'liked_member_list_'.$parent_id,
					'get_uri' => $get_uri,
					'no_data_message' => sprintf('%sしている%sはいません', term('form.like'), term('member.view')),
				)), $status_code);
			}

			$response = array(
				'status' => 1,
				'list' => $list,
				'next_id' => $next_id,
			);
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
