<?php

class Controller_Member_Relation_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * post_update
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function post_update($member_id_to = null, $relation_type = null)
	{
		$this->controller_common_api(function() use($member_id_to, $relation_type) {
			$this->response_body['errors']['message_default'] = sprintf('%sの%sに%sしました。', term('follow'), term('form.update'), term('site.failure'));
			if (!self::check_relation_type($relation_type)) throw new HttpNotFoundException();

			if (!is_null(Input::post('id'))) $member_id_to = (int)Input::post('id');
			$member = Model_Member::check_authority($member_id_to);
			if ($member_id_to == $this->u->id) throw new HttpInvalidInputException;

			$member_relation = Model_MemberRelation::get4member_id_from_to($this->u->id, $member_id_to);
			if (!$member_relation) $member_relation = Model_MemberRelation::forge();
			$prop = 'is_'.$relation_type;
			$status_before = (bool)$member_relation->$prop;
			$status_after  = !$status_before;
			\DB::start_transaction();
			$member_relation->$prop = $status_after;
			$member_relation->member_id_to   = $member_id_to;
			$member_relation->member_id_from = $this->u->id;
			$member_relation->save();
			\DB::commit_transaction();
			$this->response_body['isFollow'] = (int)$status_after;
			$this->response_body['html'] = $status_after ? sprintf('<span class="glyphicon glyphicon-ok"></span> %s', term('followed')) : term('do_follow');
			$this->response_body['attr'] = $status_after ? array('class' => array('add' => 'btn-primary')) : array('class' => array('remove' => 'btn-primary'));
			$this->response_body['message'] = sprintf('%s%sしました。', term('follow'), $status_after ? 'しました。' : 'を解除');

			return $this->response_body;
		});
	}

	private static function check_relation_type($relation_type)
	{
		if (!$relation_type) return false;
		if (!in_array($relation_type, array('follow'))) return false;
		if (!conf(sprintf('memberRelation.%s.isEnabled', $relation_type))) return false;

		return true;
	}
}
