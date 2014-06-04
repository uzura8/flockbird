<?php
namespace Admin;

class Controller_Api extends Controller_Base
{
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
		$response = array('status' => 0);
		try
		{
			if (!$field || !in_array($field, $accepts_fields)) throw new \HttpNotFoundException;
			\Util_security::check_csrf();

			\DB::start_transaction();
			$method = 'update_'.$field;
			$this->$method();
			\DB::commit_transaction();

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
}
