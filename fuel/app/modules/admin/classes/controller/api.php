<?php
namespace Admin;

class Controller_Api extends Controller_Base
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}


	/**
	 * 以下、admin_api 共通 controller
	 * 
	 */
	protected function common_post_update($field, $accepts_fields)
	{
		$this->controller_common_api(function() use($field, $accepts_fields)
		{
			if (!$field || !in_array($field, $accepts_fields)) throw new \HttpNotFoundException;

			\DB::start_transaction();
			$method = 'update_'.$field;
			$result = (bool)$this->$method();
			\DB::commit_transaction();
			$this->set_response_body_api(array('result' => $result));
		});
	}
}
