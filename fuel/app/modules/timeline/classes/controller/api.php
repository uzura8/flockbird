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
	 * Api list
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_list()
	{
		$response = '';
		try
		{
			$this->check_response_format('html');

			$default_params = array(
				'desc' => 1,
				'latest' => 1,
				'limit' => conf('timeline.articles.limit'),
			);
			list($limit, $is_latest, $is_desc, $since_id, $max_id)
				= $this->common_get_list_params($default_params, conf('timeline.articles.max_limit'));

			$member_id     = (int)\Input::get('member_id', 0);
			$is_mytimeline = (bool)\Input::get('mytimeline', 0);
			$member = $member_id ? \Model_Member::check_authority($member_id) : null;
			if ($is_mytimeline && !\Auth::check()) $is_mytimeline = false;
			$timeline_viewType = $is_mytimeline ? $this->u->timeline_viewType : null;
			$is_display_load_before_link = (bool)\Input::get('before_link', false);

			list($list, $next_id)
				= Site_Model::get_list(\Auth::check() ? $this->u->id : 0, $member_id, $is_mytimeline, $timeline_viewType, $max_id, $limit, $is_latest, $is_desc, $since_id);
			$liked_timeline_ids = \Auth::check() ? \Timeline\Model_TimelineLike::get_cols('timeline_id', array(
				array('member_id' => $this->u->id),
				array('timeline_id', 'in', \Util_Orm::conv_col2array($list, 'timeline_id'))
			)) : array();

			$data = array('list' => $list, 'next_id' => $next_id, 'liked_timeline_ids' => $liked_timeline_ids);
			if ($member) $data['member'] = $member;
			if ($is_mytimeline) $data['mytimeline'] = true;
			if ($since_id) $data['since_id'] = $since_id;
			if ($is_display_load_before_link) $data['is_display_load_before_link'] = $is_display_load_before_link;
			$response = \View::forge('_parts/list', $data);
			$status_code = 200;

			return \Response::forge($response, $status_code);
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
	 * Api post_create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_create()
	{
		$response = array('status' => 0);
		$file_tmps = array();
		$moved_files = array();
		$album_image_ids = array();
		try
		{
			\Util_security::check_csrf();

			$timeline = Model_Timeline::forge();
			$val = \Validation::forge();
			$val->add_model($timeline);
			if (!$val->run()) throw new \FuelException($val->show_errors());
			$post = $val->validated();

			$file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);

			if (!strlen($post['body']) && !$file_tmps)
			{
				throw new \FuelException('Data is empty.');
			}

			$type_key = 'normal';
			$album_id = (int)\Input::post('album_id', 0);
			if ($file_tmps && $album_id)
			{
				$album = \Album\Model_Album::check_authority($album_id, $this->u->id);
				if (\Album\Site_Util::check_album_disabled_to_update($album->foreign_table, true))
				{
					throw new \FuelException('Album id is invalid.');
				}
				$type_key = 'album_image';
			}

			\DB::start_transaction();
			if ($file_tmps)
			{
				if (!$album_id)
				{
					$type_key = 'album_image_timeline';
					$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'timeline');
				}
				list($moved_files, $album_image_ids) = \Site_FileTmp::save_images($file_tmps, $album_id, 'album_id', 'album_image', 'Album', $post['public_flag']);
			}
			else
			{
				$album_id = null;
			}
			$timeline = \Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], $type_key, $album_id, $post['body'], $timeline, $album_image_ids);
			\DB::commit_transaction();

			// thumbnail 作成 & tmp_file thumbnail 削除
			\Site_FileTmp::make_and_remove_thumbnails($moved_files);

			$response['status'] = 1;
			$response['id'] = $timeline->id;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
			$status_code = 400;
			$response['message'] = $e->getMessage();
		}

		$this->response($response, $status_code);
	}

	/**
	 * Timeline delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete($id = null)
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');

			\DB::start_transaction();
			$timeline = Model_Timeline::check_authority($id, $this->u->id);
			list($result, $deleted_files) = Site_Model::delete_timeline($timeline, $this->u->id);
			\DB::commit_transaction();
			if (!empty($deleted_files)) \Site_Upload::remove_files($deleted_files);

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Timeline update public_flag
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function post_update_public_flag()
	{
		if ($this->format != 'html') throw new \HttpNotFoundException();
		$response = '0';
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
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

			$data = array('model' => $model, 'id' => $id, 'public_flag' => $public_flag, 'is_mycontents' => true, 'without_parent_box' => true);
			$response = \View::forge('_parts/public_flag_selecter', $data);

			return \Response::forge($response, 200);
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
			\DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
