<?php
namespace Timeline;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list'
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get timeline list
	 * 
	 * @access  public
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list()
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function()
		{
			$member_id = (int)\Input::get('member_id', 0);
			list($is_mypage, $member) = $member_id ? $this->check_auth_and_is_mypage($member_id, true) : array(null, false);
			$is_mytimeline = \Auth::check() ? (bool)\Input::get('mytimeline', 0) : false;
			$timeline_viewType = $is_mytimeline ? $this->member_config->timeline_viewType : null;
			$is_display_load_before_link = (bool)\Input::get('before_link', false);

			$data = \Timeline\Site_Util::get_list4view(
				get_uid(),
				$member_id, $is_mytimeline, $timeline_viewType,
				$this->common_get_list_params(array(
					'desc'   => 1,
					'latest' => 1,
					'limit'  => conf('articles.limit', 'timeline'),
				), conf('articles.limit_max', 'timeline'), true)
			);
			if ($member) $data['member'] = $member;
			if ($is_mytimeline) $data['mytimeline'] = true;
			$data['is_display_load_before_link'] = $is_display_load_before_link;

			$this->set_response_body_api($data, '_parts/list');
		});
	}

	/**
	 * Get timeline edit menu
	 * 
	 * @access  public
	 * @param   int  $id  timeline id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_get_menu_common
	 */
	public function get_menu($id = null)
	{
		$this->api_get_menu_common('timeline', $id, true, 'timelineBox_');
	}

	/**
	 * Change timeline follow status
	 * 
	 * @access  public
	 * @param   int  $id  timeline id
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_update_follow_status($id = null)
	{
		$this->controller_common_api(function() use($id)
		{
			$this->response_body['errors']['message_default'] = term('form.fallow').'状態の変更に失敗しました。';
			$id = intval(\Input::post('id') ?: $id);
			$timeline = Model_Timeline::check_authority($id);
			$this->check_browse_authority($timeline->public_flag, $timeline->member_id);
			if ($timeline->member_id == $this->u->id) throw new \HttpBadRequestException;// 自分のタイムラインはフォロー解除不可

			\DB::start_transaction();
			$is_registerd = (int)Model_MemberFollowTimeline::change_registered_status4unique_key(array(
				'member_id' => $this->u->id,
				'timeline_id' => $timeline->id,
			));
			\DB::commit_transaction();

			$data = array(
				'result'  => $is_registerd,
				'message' => $is_registerd ? term('follow').'しました。' : term('follow').'を解除しました。',
				'html'    => icon_label($is_registerd ? 'followed' : 'do_follow', 'both', false),
			);
			$this->set_response_body_api($data);
		});
	}

	/**
	 * Create timeline
	 * 
	 * @access  public
	 * @param   int     $parent_id  target parent id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_create()
	{
		$this->controller_common_api(function()
		{
			$this->response_body['errors']['message_default'] = term('timeline').'の'.term('form.post').'に失敗しました。';
			$moved_files = array();
			$album_image_ids = array();

			$timeline = Model_Timeline::forge();
			$val = \Validation::forge();
			$val->add_model($timeline);
			if (!$val->run()) throw new \ValidationFailedException($val->show_errors());
			$post = $val->validated();
			$file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);
			if (!strlen($post['body']) && !$file_tmps)
			{
				throw new \ValidationFailedException('Data is empty.');
			}
			$type_key = 'normal';
			$album_id = (int)\Input::post('album_id', 0);
			if ($file_tmps && $album_id)
			{
				$album = \Album\Model_Album::check_authority($album_id, $this->u->id);
				if (\Album\Site_Util::check_album_disabled_to_update($album->foreign_table, true))
				{
					throw new \ValidationFailedException('Album id is invalid.');
				}
				$type_key = 'album_image';
			}

			try
			{
				\DB::start_transaction();
				if ($file_tmps)
				{
					if (!$album_id)
					{
						$type_key = 'album_image_timeline';
						$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'timeline');
					}
					list($moved_files, $album_image_ids) = \Site_FileTmp::save_images($file_tmps, $album_id, 'album_id', 'album_image', $post['public_flag']);
				}
				else
				{
					$album_id = null;
				}
				$timeline = \Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], $type_key, $album_id, null, $post['body'], $timeline, $album_image_ids);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);
			}
			catch(\Exception $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				throw $e;
			}

			$data = array(
				'id'      => $timeline->id,
				'message' => term('timeline').'を'.term('form.post').'しました。',
			);
			$this->set_response_body_api($data);
		});
	}

	/**
	 * Delete timeline
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('timeline', $id);
	}

	/**
	 * Update public_flag
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_update_public_flag($id = null)
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($id)
		{
			$id = intval(\Input::post('id') ?: $id);
			$timeline = Model_Timeline::check_authority($id, $this->u->id);
			list($public_flag, $model) = \Site_Util::validate_params_for_update_public_flag($timeline->public_flag);

			\DB::start_transaction();
			if (Site_Util::check_type($timeline->type, 'album_image_timeline'))
			{
				$album_image_ids = Model_TimelineChildData::get_foreign_ids4timeline_id($timeline->id);
				\Album\Model_AlbumImage::update_multiple_each($album_image_ids, array('public_flag' => $public_flag));
			}
			$timeline->public_flag = $public_flag;
			$timeline->save();
			\DB::commit_transaction();

			$data = array(
				'model'              => $model,
				'id'                 => $id,
				'public_flag'        => $public_flag,
				'is_mycontents'      => true,
				'without_parent_box' => true,
			);
			$this->set_response_body_api($data, '_parts/public_flag_selecter');
		});
	}
}
