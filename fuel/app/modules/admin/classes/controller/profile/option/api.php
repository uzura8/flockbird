<?php
namespace Admin;

class Controller_Profile_Option_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

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
			$profile_option->sort_order = \Model_ProfileOption::get_next_sort_order();

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
	public function post_delete($profile_option_id = null)
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();
			$profile_option_id = (int)$profile_option_id;
			if (\Input::post('id')) $profile_option_id = (int)\Input::post('id');
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
	 * Api post_update
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_update($field = null)
	{
		$this->common_post_update($field, array('sort_order'));
	}

	protected function update_sort_order()
	{
		if (!$ids = \Util_Array::cast_values(explode(',', \Input::post('ids')), 'int', true))
		{
			throw new \HttpInvalidInputException('Invalid input data.');
		}
		\Site_Model::update_sort_order($ids, \Model_ProfileOption::forge());
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
