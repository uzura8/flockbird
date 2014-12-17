<?php
namespace Notice;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api list
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_list()
	{
		$response = '0';
		try
		{
			$this->check_response_format('json');

			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('notice.articles.limit'), \Config::get('notice.articles.limit_max'));
			$data = Model_NoticeStatus::get_pager_list4member_id($this->u->id, $limit, $page);

			$status_code = 200;
			$list_array = array();
			foreach ($data['list'] as $key => $obj)
			{
				$row = $obj->to_array();
				$row['members_count'] = Model_NoticeMemberFrom::get_count4notice_id($row['notice_id'], $this->u->id);
				$row['members'] = array();
				$notice_member_froms = Model_NoticeMemberFrom::get4notice_id($row['notice_id'], \Config::get('notice.noticeMemberFrom.limit'), $this->u->id);
				foreach ($notice_member_froms as $notice_member_from)
				{
					$row['members'][] = \Model_Member::get_basic_data($notice_member_from->member_id);
				}
				$row['is_read'] = (int)$row['is_read'];
				$list_array[] = $row;
			}
			// json response
			$response = array(
				'status' => 1,
				'list' => $list_array,
				'page' => $data['page'],
				'next_page' => $data['next_page'],
				'is_detail' => (bool)\Input::get('is_detail', 0),
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

	/**
	 * Notice post update_watch_status
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function post_update_watch_status($foreign_table = null, $foreign_id = null)
	{
		$this->response_body['error_messages']['default'] = term('form.watch').'状態の変更に失敗しました。';
		try
		{
			if (!is_enabled('notice')) throw new \HttpNotFoundException();
			$this->check_response_format('json');
			\Util_security::check_csrf();

			if (\Input::post('foreign_table')) $foreign_table = \Input::post('foreign_table');
			$foreign_id = (int)$foreign_id;
			if (\Input::post('foreign_id')) $foreign_id = (int)\Input::post('foreign_id');
			if (!$foreign_table || !$foreign_id) throw new \HttpNotFoundException();
			if (!in_array($foreign_table, Site_Util::get_accept_foreign_tables())) throw new \HttpNotFoundException();

			$model = \Site_Model::get_model_name($foreign_table);
			$foreign_obj = $model::check_authority($foreign_id);
			$member_id = ($foreign_table == 'album_image') ? $foreign_obj->album->member_id : $foreign_obj->member_id;
			$this->check_browse_authority($foreign_obj->public_flag, $member_id);
			if ($member_id == $this->u->id) throw new \HttpForbiddenException;

			\DB::start_transaction();
			$is_registerd = (bool)Model_MemberWatchContent::change_registered_status4unique_key(array(
				'foreign_table' => $foreign_table,
				'foreign_id' => $foreign_id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$this->response_body['status'] = (int)$is_registerd;
			$this->response_body['message'] = $is_registerd ? term('form.watch').'対象に追加しました。' : term('form.watch').'を解除しました。';
			$this->response_body['html'] = icon_label($is_registerd ? 'form.do_unwatch' : 'form.do_watch', 'both', false);
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
		catch(\Database_Exception $e)
		{
			$status_code = 500;
		}
		catch(\FuelException $e)
		{
			$status_code = 500;
		}
		if ($status_code == 500)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$this->response_body['error_messages']['500'] = $this->response_body['error_messages']['default'];
		}

		$this->response($this->response_body, $status_code);
	}
}
