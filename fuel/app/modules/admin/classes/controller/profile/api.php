<?php
namespace Admin;

class Controller_Profile_Api extends Controller_Api
{
	public function before()
	{
		parent::before();
	}

	/**
	 * update_sort_order
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_update_sort_order()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			if (!$profile_ids = \Util_Array::cast_values(explode(',', \Input::post('ids')), 'int', true))
			{
				throw new \HttpInvalidInputException('Invalid input data.');
			}
			$sort_order = 0;
			$sort_order_interval = conf('sort_order.interval');
			\DB::start_transaction();
			foreach ($profile_ids as $profile_id)
			{
				if (!$profile = \Model_Profile::query()->where('id', $profile_id)->get_one())
				{
					throw new \HttpInvalidInputException('Invalid input data.');
				}
				$profile->sort_order = $sort_order;
				$profile->save();
				$sort_order += $sort_order_interval;
			}
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
