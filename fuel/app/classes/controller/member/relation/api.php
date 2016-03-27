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
			$this->response_body['errors']['message_default'] = sprintf('%sに%sしました。', term('form.update'), term('site.failure'));
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
			$response_data = self::get_response_data($relation_type, $status_after);
			$this->response_body = array_merge($this->response_body, $response_data);

			return $this->response_body;
		});
	}

	private static function get_response_data($relation_type, $status_after)
	{
		$relation_type_camelized_upper = Inflector::camelize($relation_type);
		$relation_type_camelized_lower = Inflector::camelize($relation_type, true);
		switch ($relation_type)
		{
			case 'follow':
				$icon_term = $status_after ? 'followed' : 'do_follow';
				$attr = $status_after ? array('class' => array('add' => 'btn-primary')) : array('class' => array('remove' => 'btn-primary'));
				break;
			default :
				$icon_term = sprintf('%sdo_%s', $status_after ? 'un' : '', $relation_type_camelized_lower);
				$attr = array();
				break;
		}

		return array(
			'is'.$relation_type_camelized_upper => (bool)$status_after,
			'message' => sprintf('%s%s', term($relation_type_camelized_lower), $status_after ? 'しました。' : 'を解除しました。'),
			'html' => icon_label($icon_term, 'both', false),
			'attr' => $attr,
		);
	}

	private static function check_relation_type($relation_type)
	{
		if (!$relation_type) return false;
		if (!in_array($relation_type, array('follow', 'access_block'))) return false;
		if (!conf(sprintf('memberRelation.%s.isEnabled', Inflector::camelize($relation_type, true)))) return false;

		return true;
	}
}
