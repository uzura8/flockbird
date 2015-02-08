<?php
namespace Thread;

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
			$data = Model_Thread::get_pager_list(array(
				'related'  => 'member',
				'where'    => \Site_Model::get_where_params4list(
					$member_id,
					\Auth::check() ? $this->u->id : 0,
					$is_mypag
				),
				'limit'    => $limit,
				'order_by' => array('created_at' => 'desc'),
			), $page);
			$data['liked_thread_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
				\Site_Model::get_liked_ids('thread', $this->u->id, $data['list'], 'Thread') : array();

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
	 * Thread get_menu
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
			$thread = Model_Thread::check_authority($id);
			$this->check_browse_authority($thread->public_flag, $thread->member_id);

			$menus = array();
			if ($thread->member_id == $this->u->id)
			{
				if (!$is_detail) $menus[] = array('tag' => 'divider');
				$menus[] = array('href' => 'thread/edit/'.$id, 'icon_term' => 'form.do_edit');
				$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
					'class' => $is_detail ? 'js-simplePost' : 'js-ajax-delete',
					'data-uri' => $is_detail ? 'thread/delete/'.$thread->id : 'thread/api/delete/'.$id.'.json',
					'data-msg' => term('form.delete').'します。よろしいですか。',
					'data-parent' => 'article_'.$id,
				));
			}
			elseif (is_enabled('notice'))
			{
				$is_watched = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id('thread', $id, $this->u->id);
				$menus[] = array('icon_term' => $is_watched ? 'form.do_unwatch' : 'form.do_watch', 'attr' => array(
					'class' => 'js-update_toggle',
					'data-uri' => 'member/notice/api/update_watch_status/thread/'.$id,
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
	 * Thread delete
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
			$thread = Model_Thread::check_authority($id, $this->u->id);
			$thread->delete();
			//$thread->delete_with_relations();
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
	 * Thread update public_flag
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
			$thread = Model_Thread::check_authority($id, $this->u->id);

			list($public_flag, $model) = \Site_Util::validate_params_for_update_public_flag($thread->public_flag, 'public');

			\DB::start_transaction();
			$thread->public_flag = $public_flag;
			$thread->save();
			\DB::commit_transaction();

			$data = array('model' => $model, 'id' => $id, 'public_flag' => $public_flag, 'option_type' => 'public', 'is_mycontents' => true, 'without_parent_box' => true);
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
			$status_code = 500;
		}

		$this->response($response, $status_code);
	}
}
