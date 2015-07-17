<?php
class Site_Form
{
	public static function get_public_flag_options($key = null, $type = 'default', $with_no_change_option = false)
	{
		$options = array();
		if ($with_no_change_option) $options[99] = '変更しない';
		$public_flags = Site_Util::get_public_flags($type);
		foreach ($public_flags as $public_flag)
		{
			$options[$public_flag] = term('public_flag.options.'.$public_flag);
		}

		if (isset($key)) return $options[$key];

		return $options;
	}

	public static function get_public_flag_configs($is_select = false, $type = 'default')
	{
		return array(
			'type'    => $is_select ? 'select' : 'radio',
			'label'   => term('public_flag.label'),
			'options' => self::get_public_flag_options(null, $type),
			'value'   => conf('public_flag.default'),
		);
	}

	public static function get_form_options4config($config_key, $selected_key = null, $is_return_false_not_set_key = false)
	{
		if (!$options = Config::get($config_key)) throw new InvalidArgumentException('First parameter is invalid.');

		if (!is_null($selected_key) && isset($options[$selected_key])) return $options[$selected_key];
		if ($is_return_false_not_set_key) return false;

		return $options;
	}

	public static function get_field_id($name)
	{
		return sprintf('form_%s', str_replace('[]', '', $name));
	}

	public static function get_fieid_attribute(Validation $val, $name, $default_value = null, $is_textarea = false, $optional_attr = array())
	{
		$field = $val->fieldset()->field($name);

		$label = '';
		$is_required = false;
		$input_attr = array(
			'id'    => Site_Form::get_field_id($name),
			'class' => 'form-control',
		);
		if (!is_array($optional_attr)) $optional_attr = (array)$optional_attr;
		if (!$optional_attr) $input_attr += $optional_attr;

		if (is_callable(array($field, 'get_attribute')))
		{
			$default_value = $field->get_attribute('value');
			$is_required = $field->get_attribute('required') == 'required';
			$label = $field->get_attribute('label');
			$input_attr['placeholder'] = $field->get_attribute('placeholder');
			$input_attr['type'] = $field->get_attribute('type');

			if ($is_textarea && !is_null($field->get_attribute('rows')))
			{
				$input_attr['rows'] = $field->get_attribute('rows');
			}
		}

		return array($default_value, $label, $is_required, $input_attr);
	}
}
