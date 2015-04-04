<?php
class Form_Util
{
	public static function get_model_field($table, $column, $namespace = '', $label = '', $delete_rules = array())
	{
		$model = Util_Orm::get_model_name($table, $namespace);
		$obj = $model::forge();
		$props = $obj::get_property($column, $delete_rules);
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

	public static function get_field_names(\Validation $val)
	{
		return array_keys($val->Fieldset()->field());
	}

	public static function get_int_options($min, $max)
	{
		$options = array();
		for ($i = $min; $i <= $max; $i++)
		{
			$options[$i] = $i;
		}

		return $options;
	}

	public static function get_year_options($year_from = -100, $year_to = 0)
	{
		$from = (int)date('Y', strtotime($year_from.' year'));
		$to = (int)date('Y', strtotime($year_to.' year'));

		return self::get_int_options($from, $to);
	}
}
