<?php
namespace Timeline;

class Controller_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member'
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Timeline like get member
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_member($parent_id = null)
	{
		$response = '';
		try
		{
			if (!in_array($this->format, array('json', 'html'))) throw new \HttpNotFoundException();
			$timeline_id = (int)$parent_id;
			if (!$timeline_id || !$timeline = Model_timeline::check_authority($timeline_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

			list($limit, $params, $is_desc, $class_id) = $this->common_get_list_params(array('desc' => 1), conf('view_params_default.list.limit.limit_max'));
			list($list, $next_id) = Model_TimelineLike::get_list(array('timeline_id' => $timeline_id), $limit, 'member', ($this->format == 'json'), false, $is_desc);

			$status_code = 200;
			if ($this->format == 'html')
			{
				return \Response::forge(\View::forge('_parts/member_list', array(
					'list' => $list,
					'related_member_table_name' => 'member',
					'next_id' => $next_id,
					'is_simple_list' => true,
					'list_id' => 'liked_member_list_timeline_'.$timeline_id,
					'get_uri' => Site_Util::get_liked_member_api_uri($timeline->type, $timeline->id, $timeline->foreign_id),
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

	public function get_list()
	{
		if ($this->format != 'html') throw new \HttpNotFoundException();

		$page = (int)\Input::get('page', 1);

		$response = '';
		try
		{
			$sort = conf('member.view_params.list.sort');
			$data = \Site_Model::get_simple_pager_list('member', $page, array(
				'order_by' => array($sort['property'] => $sort['direction']),
				'limit'    => conf('member.view_params.list.limit'),
			));

			$response = \View::forge('member/_parts/list', $data);
			$status_code = 200;

			return \Response::forge($response, $status_code);
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Timeline like post update
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function post_update($id = null)
	{
		$response = array('status' => 0);
		try
		{
			if (!conf('like.isEnabled')) throw new \HttpNotFoundException();
			if ($this->format != 'json') throw new \HttpNotFoundException();
			\Util_security::check_csrf();

			$timeline_id = (int)$id;
			if (\Input::post('id')) $timeline_id = (int)\Input::post('id');
			if (!$timeline_id || !$timeline = Model_Timeline::check_authority($timeline_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

			\DB::start_transaction();
			$is_liked = (bool)Model_TimelineLike::change_registered_status4unique_key(array(
				'timeline_id' => $timeline->id,
				'member_id' => $timeline->member_id
			));
			\DB::commit_transaction();

			$response['status'] = (int)$is_liked;
			$response['count'] = Model_TimelineLike::get_count4timeline_id($timeline->id);
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
