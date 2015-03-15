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
	 * Create profile option 
	 * 
	 * @access  public
	 * @return  Response(json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     Controller_Base::controller_common_api
	 */
	public function post_create($profile_id = null)
	{
		$this->api_accept_formats = array('html', 'json');
		$this->controller_common_api(function() use($profile_id)
		{
			$profile_id = intval(\Input::post('id') ?: $profile_id);
			$profile = $this->check_id_and_get_profile($profile_id);
			$profile_option = \Model_ProfileOption::forge();

			// Lazy validation
			$label = trim(\Input::post('label', ''));
			if (!strlen($label)) throw new \ValidationFailedException('入力してください。');

			$profile_option->label = $label;
			$profile_option->profile_id = $profile_id;
			\DB::start_transaction();
			$profile_option->sort_order = \Model_ProfileOption::get_next_sort_order();
			$result = (bool)$profile_option->save();
			\DB::commit_transaction();

			$data = array(
				'result' => $result,
				'id' => $profile_option->id,
			);
			if ($this->format == 'html')
			{
				$data += array(
					'label' => $profile_option->label,
					'delete_uri' => 'admin/profile/option/api/delete.json',
				);
			}
			$this->set_response_body_api($data, $this->format == 'html' ? '_parts/table/simple_row_sortable' : null);
		});
	}

	/**
	 * Delete profile option
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     Controller_Base::controller_common_api
	 */
	public function post_delete($id = null)
	{
		$this->controller_common_api(function() use($id)
		{
			$profile_option_id = \Input::post('id') ?: $id;
			$profile_option = \Model_ProfileOption::check_authority($profile_option_id, 0, 'profile');
			if (!$profile_option->profile || !in_array($profile_option->profile->form_type, \Site_Profile::get_form_types_having_profile_options()))
			{
				throw new \HttpInvalidInputException;
			}
			\DB::start_transaction();
			$result = (bool)$profile_option->delete();
			\DB::commit_transaction();
			$this->set_response_body_api(array('result' => $result));
		});
	}

	/**
	 * Update profile option
	 * 
	 * @access  public
	 * @param   string  $field  Edit field
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     \Admin\Controller_Api::common_post_update
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

		return \Site_Model::update_sort_order($ids, \Model_ProfileOption::forge());
	}

	private function check_id_and_get_profile($profile_id)
	{
		if (!$profile_id || !$profile = \Model_Profile::check_authority($profile_id))
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
