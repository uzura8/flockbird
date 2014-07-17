<?php
class DisableToUpdatePublicFlagException extends \FuelException {}

class Controller_Site_Api extends Controller_Base_Site
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	public function common_get_list_params($defaults = array(), $limit_max = 0)
	{
		$last_id   = (int)\Input::get('last_id', isset($defaults['last_id']) ? $defaults['last_id'] : 0);
		$is_before = (bool)\Input::get('is_before', isset($defaults['is_before']) ? $defaults['is_before'] : 0);
		$class_id  = (int)\Input::get('class_id', isset($defaults['class_id']) ? $defaults['class_id'] : 0);
		$is_desc   = (bool)\Input::get('desc', isset($defaults['desc']) ? $defaults['desc'] : 0);
		$limit     = (int)\Input::get('limit', isset($defaults['limit']) ? $defaults['limit'] : conf('view_params_default.list.comment.limit'));
		$limit_id  = (int)\Input::get('limit_id', 0);

		if (!$limit_max) $limit_max = conf('view_params_default.list.comment.max_limit', 50);
		if ($limit > $limit_max) $limit = $limit_max;
		if (\Input::get('limit') == 'all') $limit = $limit_max;

		$params = array();
		if ($last_id)
		{
			$operator = $is_before ? '<' : '>';
			$params[] = array('id', $operator, $last_id);
		}
		if ($limit_id)
		{
			$operator = $is_before ? '>' : '<';
			$params[] = array('id', $operator, $limit_id);
		}

		return array($limit, $params, $is_desc, $class_id, $last_id, $is_before);
	}
}
