<?php
namespace Contact;

class Site_Util
{
	public static function set_form_fields(\Validation $val, array $conf_fields)
	{
		if (!$conf_fields) return $val;

		foreach ($conf_fields as $name => $props)
		{
			$attr = $props['attr'];
			$rules = \Arr::get($props, 'rules', array());
			if (in_array($props['attr']['type'], array('select', 'radio')) && !empty($props['attr']['options']))
			{
				$rules[] = array('in_array', $props['attr']['options']);
			}
			$val->add($name, $props['label'], $attr, $rules);
		}

		return $val;
	}
}

