<?php

class Controller_Member_Invite_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Delete member_pre record
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     Controller_Base::controller_common_api
	 */
	public function post_cancel($id = null)
	{
		$this->controller_common_api(function() use($id)
		{
			$member_pre_id = Input::post('id') ?: $id;
			$member_pre = Model_MemberPre::check_authority($id, $this->u->id, null, 'invite_member_id');
			DB::start_transaction();
			$result = (bool)$member_pre->delete();
			\DB::commit_transaction();
			$this->set_response_body_api(array('result' => $result));
		});
	}
}
