<?php

class Form_Util
{
	public static function get_model_field($table, $column, $namespace = '', $label = '')
	{
		$props = Util_Orm::get_prop($table, $column, $namespace);
		if (!$props || empty($props['form']))
		{
			throw new \InvalidArgumentException('Second parameter is invalid.');
		}
		if (!$label) $label = !empty($props['label']) ? $props['label'] : '';

		return array(
			'label' => $label,
			'attributes' => $props['form'],
			'rules' => !empty($props['validation']) ? Util_Array::convert_for_callback($props['validation']) : array(),
		);
	}
}
