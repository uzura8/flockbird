<?php
namespace Admin;

class Controller_Profile_Option_Api extends Controller_Admin
{
	public function before()
	{
		parent::before();
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
			if (!in_array($this->format, array('html', 'json'))) throw new \HttpNotFoundException();
			\Util_security::check_csrf();
			$profile_id = (int)\Input::post('id');
			$profile = $this->check_id_and_get_profile($profile_id);
			$profile_option = \Model_ProfileOption::forge();

			// Lazy validation
			$label = trim(\Input::post('label', ''));
			if (!strlen($label)) throw new \HttpInvalidInputException;

			$profile_option->label = $label;
			$profile_option->profile_id = $profile_id;
			$profile_option->sort_order = \Site_Model::get_next_sort_order('profile_option');

			\DB::start_transaction();
			$profile_option->save();
			\DB::commit_transaction();

			$status_code = 200;
			if ($this->format == 'html')
			{
				$response = \View::forge('_parts/table/simple_row_sortable', array(
					'id' => $profile_option->id,
					'name' => $profile_option->label,
					'delete_uri' => 'admin/profile/option/api/delete.json',
				));

				return \Response::forge($response, $status_code);
			}
			else
			{
				$response['status'] = 1;
				$response['id'] = $profile_option->id;
			}
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
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
	 * Api post delete
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
			$profile_option_id = (int)\Input::post('id');
			if (!$profile_option_id || !$profile_option = \Model_ProfileOption::find($profile_option_id, array('related' => 'profile')))
			{
				throw new \HttpNotFoundException;
			}
			if (!$profile_option->profile || !in_array($profile_option->profile->form_type, \Site_Profile::get_form_types_having_profile_options()))
			{
				throw new \HttpInvalidInputException;
			}

			\DB::start_transaction();
			$profile_option->delete();
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

			if (!$profile_option_ids = \Util_Array::cast_values(explode(',', \Input::post('ids')), 'int', true))
			{
				throw new \HttpInvalidInputException('Invalid input data.');
			}
			$sort_order = 0;
			$sort_order_interval = \Config::get('site.sort_order.interval');
			\DB::start_transaction();
			foreach ($profile_option_ids as $profile_option_id)
			{
				if (!$profile_option = \Model_ProfileOption::query()->where('id', $profile_option_id)->get_one())
				{
					throw new \HttpInvalidInputException('Invalid input data.');
				}
				$profile_option->sort_order = $sort_order;
				$profile_option->save();
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

	private function check_id_and_get_profile($profile_id)
	{
		if (!$profile_id || !$profile = \Model_Profile::find($profile_id))
		{
			throw new \HttpNotFoundException;
		}
		if (!in_array($profile->form_type, \Site_Profile::get_form_types_having_profile_options()))
		{
			throw new \HttpInvalidInputException;
		}

		return $profile;
	}
}
