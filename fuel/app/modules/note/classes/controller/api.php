<?php
namespace Note;

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

			list($limit, $page) = $this->common_get_pager_list_params(conf('view_params_default.list.limit'), conf('view_params_default.list.limit_max'));
			$member_id = (int)\Input::get('member_id', 0);

			$member    = null;
			$is_mypage = false;

			if ($member_id) list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id, true);
			$is_draft = $is_mypage ? \Util_string::cast_bool_int(\Input::get('is_draft', 0)) : 0;
			$is_published = \Util_toolkit::reverse_bool($is_draft, true);
			$data = Model_Note::get_pager_list(array(
				'related'  => 'member',
				'where'    => \Site_Model::get_where_params4list(
					$member_id,
					\Auth::check() ? $this->u->id : 0,
					$is_mypage,
					array(array('is_published', $is_published))
				),
				'limit'    => $limit,
				'order_by' => array('created_at' => 'desc'),
			), $page);
			$data['is_draft'] = $is_draft;
			$data['liked_note_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
				\Site_Model::get_liked_ids('note', $this->u->id, $data['list'], 'Note') : array();

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
	 * Note get_menu
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_menu($id = null)
	{
		$response = '';
		try
		{
			$this->check_response_format('html');

			$is_detail = (bool)\Input::get('is_detail', 0);
			$id = (int)$id;
			$note = Model_Note::check_authority($id);
			$this->check_browse_authority($note->public_flag, $note->member_id);

			$menus = array();
			if ($note->member_id == $this->u->id)
			{
				if (!$is_detail) $menus[] = array('tag' => 'divider');
				if (!$note->is_published)
				{
					$menus[] = array('icon_term' => 'form.do_publish', 'attr' => array(
						'class' => 'js-simplePost',
						'data-uri' => 'note/publish/'.$id,
						'data-msg' => term('form.publish').'しますか？',
					));
				}
				$menus[] = array('href' => 'note/edit/'.$id, 'icon_term' => 'form.do_edit');
				$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
					'class' => $is_detail ? 'js-simplePost' : 'js-ajax-delete',
					'data-uri' => $is_detail ? 'note/delete/'.$note->id : 'note/api/delete/'.$id.'.json',
					'data-msg' => term('form.delete').'します。よろしいですか。',
					'data-parent' => 'article_'.$id,
				));
			}
			elseif (is_enabled('notice'))
			{
				$is_watched = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id('note', $id, $this->u->id);
				$menus[] = array('icon_term' => $is_watched ? 'form.do_unwatch' : 'form.do_watch', 'attr' => array(
					'class' => 'js-update_toggle',
					'data-uri' => 'member/notice/api/update_watch_status/note/'.$id,
					//'data-msg' => $is_watched ? term('form.watch').'を解除しますか？' : term('form.watch').'しますか？',
				));
			}

			$response = \View::forge('_parts/dropdown_menu', array('menus' => $menus, 'is_ajax_loaded' => true));
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
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Note delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete($id = null)
	{
		$response = array('status' => 0);
		try
		{
			$this->check_response_format('json');
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');
			\DB::start_transaction();
			$note = Model_Note::check_authority($id, $this->u->id);
			$note->delete_with_relations();
			\DB::commit_transaction();

			$response['status'] = 1;
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

	/**
	 * Note update public_flag
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function post_update_public_flag($id = null)
	{
		$response = '0';
		try
		{
			$this->check_response_format('html');
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');
			$note = Model_Note::check_authority($id, $this->u->id);

			list($public_flag, $model) = \Site_Util::validate_params_for_update_public_flag($note->public_flag);

			\DB::start_transaction();
			$note->update_public_flag_with_relations($public_flag);
			\DB::commit_transaction();

			$data = array('model' => $model, 'id' => $id, 'public_flag' => $public_flag, 'is_mycontents' => true, 'without_parent_box' => true);
			$response = \View::forge('_parts/public_flag_selecter', $data);
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
