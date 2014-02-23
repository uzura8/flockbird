<?php

class Form_Util
{
	public static function get_model_field($table, $column, $namespace = '')
	{
		$model = Site_Model::get_model_name($table, $namespace);
		$model_obj = $model::forge();
		$properties = $model_obj::property($column);
		unset($model_obj);
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
