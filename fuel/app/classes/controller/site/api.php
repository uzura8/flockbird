<?php
class DisableToUpdateException extends \FuelException {}

class Controller_Site_Api extends Controller_Base_Site
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}


	/**
	 * 以下、共通 controller
	 * 
	 */

	protected function get_comment_list($model, $parent_model, $parent_id, $parent_id_prop, $api_uri_path_prefix, $limit = 0, $limit_max = 0, $parent_obj_member_id_relateds = array())
	{
		$response = '0';
		try
		{
			$this->check_response_format(array('json', 'html'));

			$parent_id = (int)$parent_id;
			$parent_obj = $parent_model::check_authority($parent_id);
			$auther_member_ids = array();
			if ($parent_obj_member_id_relateds)
			{
				$auther_member_ids = Util_Orm::get_related_table_values_recursive($parent_obj, $parent_obj_member_id_relateds);
			}
			else
			{
				$auther_member_ids[] = $parent_obj->member_id;
			}
			foreach ($auther_member_ids as $member_id) $this->check_browse_authority($parent_obj->public_flag, $member_id);

			$member_profile_image_size = Input::get('image_size') ?: 'M';
			if (!in_array($member_profile_image_size, array('SS', 'S', 'M'))) $member_profile_image_size = 'M';
			$default_params = array(
				'latest' => 1,
				'limit' => $limit ?: conf('view_params_default.list.comment.limit'),
			);
			list($limit, $is_latest, $is_desc, $since_id, $max_id)
				= $this->common_get_list_params($default_params, $limit_max ?: conf('view_params_default.list.comment.limit_max'));
			list($list, $next_id, $all_comment_count)
				= $model::get_list(array($parent_id_prop => $parent_id), $limit, $is_latest, $is_desc, $since_id, $max_id, null, false, ($this->format == 'json'));

			if (conf('like.isEnabled'))
			{
				$model_like = $model.'Like';
				$comment_table_name = sprintf('%s_comment', str_replace('/', '_', $api_uri_path_prefix));
				$liked_ids = \Auth::check() ? \Site_Model::get_liked_ids($comment_table_name, $this->u->id, $list, null, $model.'Like') : array();
			}

			$status_code = 200;
			if ($this->format == 'html')
			{
				$data = array(
					'image_size' => $member_profile_image_size,
					'list' => $list,
					'next_id' => $next_id,
					'parent' => $parent_obj,
					'auther_member_ids' => $auther_member_ids,
					'list_more_box_attrs' => array(
						'id' => 'listMoreBox_comment_'.$parent_id,
						'data-uri' => sprintf('%s/comment/api/list/%d.html', $api_uri_path_prefix, $parent_id),
						'data-list' => '#comment_list_'.$parent_id,
					),
					'delete_uri' => sprintf('%s/comment/api/delete.json', $api_uri_path_prefix),
					'counter_selector' => '#comment_count_'.$parent_id,
				);
				if ($since_id) $data['since_id'] = $since_id;
				if (conf('like.isEnabled')) $data['liked_ids'] = $liked_ids;
				// html response

				return \Response::forge(\View::forge('_parts/comment/list', $data), $status_code);
			}

			$list_array = array();
			foreach ($list as $key => $obj)
			{
				$row = $obj->to_array();
				$row['body'] = convert_body($row['body'], array(
					'nl2br' => false,
					'is_truncate' => false,
					'mention2link' => true,
				));
				$row['member'] = Model_Member::get_one_basic4id($row['member_id']);
				if (conf('like.isEnabled'))
				{
					$row['get_like_members_uri'] = sprintf('%s/comment/like/api/member/%d.html', $api_uri_path_prefix, $row['id']);
					$row['post_like_uri'] = sprintf('%s/comment/like/api/update/%d.json', $api_uri_path_prefix, $row['id']);
					$row['is_liked'] = (\Auth::check() && in_array($row['id'], $liked_ids)) ? 1 : 0;
					$row['comment_table'] = $comment_table_name;
				}
				$list_array[] = $row;
			}
			$parent_member_id = $parent_obj_member_id_relateds ? array_shift($auther_member_ids) : $parent_obj->member_id;
			// json response
			$response = array(
				'status' => 1,
				'list' => $list_array,
				'count' => $all_comment_count,
				'next_id' => $next_id,
				'parent' => array('id' => $parent_id, 'member_id' => $parent_member_id),
				'get_uri' => sprintf('%s/comment/api/list/%d.json', $api_uri_path_prefix, $parent_id),
				'delete_uri' => sprintf('%s/comment/api/delete.json', $api_uri_path_prefix),
				'image_size' => array(
					'key' => $member_profile_image_size,
					'value' => conf('upload.types.img.types.m.sizes.'.$member_profile_image_size),
				),
			);
			if ($since_id) $response['since_id'] = $since_id;
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

	protected function get_liked_member_list($like_model, $parent_model, $parent_id, $parent_id_prop, $get_uri, $public_flag_related_table = null, $limit = 0, $limit_max = 0, $parent_obj_member_id_relateds = array())
	{
		$response = '';
		try
		{
			if (!conf('like.isEnabled')) throw new \HttpNotFoundException();
			$this->check_response_format(array('json', 'html'));

			$parent_id = (int)$parent_id;
			$parent_obj = $parent_model::check_authority($parent_id);
			$auther_member_ids = array();
			if ($parent_obj_member_id_relateds)
			{
				$auther_member_ids = Util_Orm::get_related_table_values_recursive($parent_obj, $parent_obj_member_id_relateds);
			}
			else
			{
				$auther_member_ids[] = $parent_obj->member_id;
			}
			foreach ($auther_member_ids as $member_id)
			{
				$this->check_browse_authority($public_flag_related_table ? $parent_obj->{$public_flag_related_table}->public_flag : $parent_obj->public_flag, $member_id);
			}

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
