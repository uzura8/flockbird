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

	protected function get_comment_list($model, $parent_model, $parent_id, $parent_id_prop, $module, $limit = 0, $limit_max = 0)
	{
		$response = '';
		try
		{
			$this->check_response_format(array('json', 'html'));

			$parent_id = (int)$parent_id;
			$parent_obj = $parent_model::check_authority($parent_id);
			$this->check_browse_authority($parent_obj->public_flag, $parent_obj->member_id);

			$default_params = array(
				'latest' => 1,
				'limit' => $limit ?: conf('view_params_default.list.comment.limit'),
			);
			list($limit, $is_latest, $is_desc, $since_id, $max_id)
				= $this->common_get_list_params($default_params, $limit_max ?: conf('view_params_default.list.comment.limit_max'));
			list($list, $next_id, $all_comment_count)
				= $model::get_list(array($parent_id_prop => $parent_id), $limit, $is_latest, $is_desc, $since_id, $max_id, null, false, ($this->format == 'json'));

			$status_code = 200;
			if ($this->format == 'html')
			{
				$data = array(
					'list' => $list,
					'next_id' => $next_id,
					'parent' => $parent_obj,
					'list_more_box_attrs' => array(
						'id' => 'listMoreBox_comment_'.$parent_id,
						'data-uri' => sprintf('%s/comment/api/list/%d.html', $module, $parent_id),
						'data-list' => '#comment_list_'.$parent_id,
					),
					'delete_uri' => sprintf('%s/comment/api/delete.json', $module),
					'counter_selector' => '#comment_count_'.$parent_id,
				);
				if ($since_id) $data['since_id'] = $since_id;
				// html response
				return \Response::forge(\View::forge('_parts/comment/list', $data), $status_code);
			}

			// json response
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

	protected function get_liked_member_list($like_model, $parent_model, $parent_id, $parent_id_prop, $get_uri, $limit = 0, $limit_max = 0)
	{
		$response = '';
		try
		{
			$this->check_response_format(array('json', 'html'));

			$parent_id = (int)$parent_id;
			$parent_obj = $parent_model::check_authority($parent_id);
			$this->check_browse_authority($parent_obj->public_flag, $parent_obj->member_id);

			$default_params = array(
				'desc' => 1,
				'latest' => 1,
				'limit' => $limit ?: conf('view_params_default.like.members.popover.limit'),
			);
			list($limit, $is_latest, $is_desc, $since_id, $max_id)
				= $this->common_get_list_params($default_params, $limit_max ?: conf('view_params_default.like.members.popover.limit_max'));
			$params[$parent_id_prop] = $parent_id;
			list($list, $next_id) = $like_model::get_list($params, $limit, $is_latest, $is_desc, $since_id, $max_id, 'member', ($this->format == 'json'));

			$status_code = 200;
			if ($this->format == 'html')
			{
				$data = array(
					'list' => $list,
					'related_member_table_name' => 'member',
					'next_id' => $next_id,
					'is_simple_list' => true,
					'list_id' => 'liked_member_list_'.$parent_id,
					'get_uri' => $get_uri,
					'no_data_message' => sprintf('%sしている%sはいません', term('form.like'), term('member.view')),
				);
				if ($since_id) $data['since_id'] = $since_id;
				return \Response::forge(\View::forge('_parts/member_list', $data), $status_code);
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
