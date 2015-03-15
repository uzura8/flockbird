<?php
namespace Admin;

class Controller_Profile_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Update profile
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

		return \Site_Model::update_sort_order($ids, \Model_Profile::forge());
	}
}
