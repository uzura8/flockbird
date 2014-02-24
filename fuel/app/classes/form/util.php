<?php

class Form_Util
{
	public static function get_model_field($table, $column, $namespace = '')
	{
		$properties = Util_Orm::get_prop($table, $column, $namespace);
		if (!$properties || empty($properties['form']))
		{
			throw new \InvalidArgumentException('Second parameter is invalid.');
		}

		return array(
			'label' => !empty($properties['label']) ? $properties['label'] : '',
			'attributes' => $properties['form'],
			'rules' => !empty($properties['validation']) ? $properties['validation'] : array(),
		);
	}
}
