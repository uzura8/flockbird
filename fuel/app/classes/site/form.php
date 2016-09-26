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

	public static function get_fieid_attribute(Validation $val, $name, $default_value = null, $is_textarea = false, $optional_attr = array(), $is_return_assoc = false)
	{
		$field = $val->fieldset()->field($name);
		$label = '';
		$input_attr = array();
		$is_required = false;

		if (is_callable(array($field, 'get_attribute')))
		{
			$input_attr = $field->get_attribute();
			$input_attr = Arr::filter_keys($input_attr, array('validation', 'label'), true);
			if ((is_null($default_value) || (empty($default_value) && !strlen($default_value))) && !is_null($field->get_attribute('value')))
			{
				$default_value = $field->get_attribute('value');
			}
			$is_required = $field->get_attribute('required') == 'required';
			$label = $field->get_attribute('label');
		}
		if (!is_array($optional_attr)) $optional_attr = (array)$optional_attr;
		if ($optional_attr) $input_attr += $optional_attr;
		if (empty($input_attr['id'])) $input_attr['id'] = Site_Form::get_field_id($name);
		if (empty($input_attr['class'])) $input_attr['class'] = 'form-control';
		if ($is_textarea) $input_attr['class'] .= ' autogrow';

		$return = array(
			'default_value' => $default_value,
			'label' => $label,
			'is_required' => $is_required,
			'input_attr' => $input_attr,
			'field' => $field,
		);

		return $is_return_assoc ? $return : array_values($return);
	}

	public static function get_label(Validation $val, $name)
	{
		$field = $val->fieldset()->field($name);
		if (!is_callable(array($field, 'get_attribute'))) throw new InvalidArgumentException('First parameter is invalid.');

		return $field->get_attribute('label');
	}

	public static function add_novalue_option(array $options)
	{
		$return_value = array('' => term('form.no_selected_label'));
		foreach ($options as $key => $value)
		{
			$return_value[$key] = $value;
		}

		return $return_value;
	}
}
