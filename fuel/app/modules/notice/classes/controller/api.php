<?php
namespace Notice;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Get notice list
	 * 
	 * @access  public
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list()
	{
		$this->controller_common_api(function()
		{
			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('notice.articles.limit'), \Config::get('notice.articles.limit_max'));
			$data = Model_NoticeStatus::get_pager_list4member_id($this->u->id, $limit, $page);
			$list_array = array();
			foreach ($data['list'] as $key => $obj)
			{
				$list_array[] = Site_Model::convert_notice_status_to_array_for_view($obj, $this->u->id);
			}
			// json response
			$data['list'] = $list_array;
			$data['is_detail'] = (bool)\Input::get('is_detail', 0);
			$this->set_response_body_api($data);
		});
	}

	/**
	 * Update watch status
	 * 
	 * @access  public
	 * @param   string  $foreign_table  target related table
	 * @param   int     $foreign_id  target related table id
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_update_watch_status($foreign_table = null, $foreign_id = null)
	{
		$this->controller_common_api(function() use($foreign_table, $foreign_id)
		{
			if (!is_enabled('notice')) throw new \HttpNotFoundException();
			if (\Input::post('foreign_table')) $foreign_table = \Input::post('foreign_table');
			$foreign_id = intval(\Input::post('foreign_id') ?: $foreign_id);
			if (!$foreign_table || !$foreign_id) throw new \HttpNotFoundException();
			if (!in_array($foreign_table, Site_Util::get_accept_foreign_tables())) throw new \HttpNotFoundException();

			$this->response_body['errors']['message_default'] = __('message_change_failed_for', array(
				'label' => t('common.status_of_simple', array('label' => t('form.watch'))),
			));
			$model = \Site_Model::get_model_name($foreign_table);
			$foreign_obj = $model::check_authority($foreign_id);
			$member_id = ($foreign_table == 'album_image') ? $foreign_obj->album->member_id : $foreign_obj->member_id;
			$this->check_browse_authority($foreign_obj->public_flag, $member_id);
			if ($member_id == $this->u->id) throw new \HttpBadRequestException;

			\DB::start_transaction();
			$is_registerd = (bool)Model_MemberWatchContent::change_registered_status4unique_key(array(
				'foreign_table' => $foreign_table,
				'foreign_id' => $foreign_id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$data = array(
				'result' => $is_registerd,
				'message' => $is_registerd ? __('notice_add_watch_list') : __('notice_remove_watch_list'),
				'html' => icon_label($is_registerd ? 'form.do_unwatch' : 'form.do_watch', 'both', false),
			);
			$this->set_response_body_api($data);
		});
	}

	/**
	 * Update status already read all
	 * 
	 * @access  public
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_read_all()
	{
		$this->controller_common_api(function()
		{
			if (!is_enabled('notice')) throw new \HttpNotFoundException();

			\DB::start_transaction();
			$updated_count = \Notice\Site_Util::change_all_status2read4member_id($this->u->id);
			\DB::commit_transaction();

			$data = array(
				'result' => (bool)$updated_count,
				'updated_count' => $updated_count,
			);
			$this->set_response_body_api($data);
		});
	}
}
