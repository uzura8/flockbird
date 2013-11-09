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
		if ($this->format != 'html') throw new \HttpNotFoundException();

		$member_id     = (int)\Input::get('member_id', 0);
		$is_mytimeline = (bool)\Input::get('mytimeline', 0);
		$limit         = \Input::get('limit') == 'all' ? \Config::get('timeline.articles.max_limit', 50) : (int)\Input::get('limit', \Config::get('timeline.articles.limit'));
		$before_id     = (int)\Input::get('before_id', 0);
		$after_id      = (int)\Input::get('after_id', 0);
		$is_over       = (bool)\Input::get('is_over', 0);

		$last_id = $before_id ?: $after_id;

		$response = '';
		try
		{
			$is_mypage = false;
			$member = null;
			if ($member_id)
			{
				if (!$member = \Model_Member::check_authority($member_id)) 	throw new \HttpNotFoundException;;
				$is_mypage = $this->check_is_mypage($member_id);
			}
			if ($is_mytimeline && !\Auth::check()) $is_mytimeline = false;

			list($list, $is_next) = Site_Model::get_list(\Auth::check() ? $this->u->id : 0, $member_id, $is_mypage, $is_mytimeline, $last_id, $is_over, $limit);
			$data = array('list' => $list, 'is_next' => $is_next);
			if ($member) $data['member'] = $member;
			if ($is_mytimeline) $data['mytimeline'] = true;
			$response = \View::forge('_parts/timeline/list', $data);
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
	 * Api post_create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_create()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$timeline = Model_Timeline::forge();
			$val = \Validation::forge();
			$val->add_model($timeline);
			//$val->add('public_flag', \Config::get('term.public_flag.label'))->add_rule('public_flag');
			if (!$val->run()) throw new \FuelException($val->show_errors());
			$post = $val->validated();

			\DB::start_transaction();
			$values = array(
				'public_flag' => $post['public_flag'],
				'body' => $post['body'],
			);
			$timeline = \Timeline\Site_Model::save_timeline($this->u->id, $values, 'normal', $timeline);
			\DB::commit_transaction();

			$response['status'] = 1;
			$response['id'] = $timeline->id;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
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
	public function post_delete()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$timeline = Model_Timeline::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			$timeline->delete();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			\DB::rollback_transaction();
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
			if (!$id || !$timeline = Model_Timeline::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			list($public_flag, $model) = \Site_Util::validate_params_for_update_public_flag($timeline->public_flag);

			\DB::start_transaction();
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
