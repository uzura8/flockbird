<?php

class Controller_Member_Relation_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	public function post_update($relation_type = null)
	{
		$response = array();
		try
		{
			Util_security::check_csrf();
			if ($this->format != 'json') throw new HttpNotFoundException();
			if (!self::check_relation_type($relation_type)) throw new HttpNotFoundException();

			$member_id_to = (int)Input::post('id');
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

			$response['status'] = (int)$status_after;
			$status_code = 200;
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

	private static function check_relation_type($relation_type)
	{
		if (!$relation_type) return false;
		if (!in_array($relation_type, array('follow'))) return false;
		if (!conf(sprintf('memberRelation.%s.isEnabled', $relation_type))) return false;

		return true;
	}
}
