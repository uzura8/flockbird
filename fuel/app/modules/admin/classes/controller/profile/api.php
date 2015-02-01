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
		\Site_Model::update_sort_order($ids, \Model_Profile::forge());
	}
}
