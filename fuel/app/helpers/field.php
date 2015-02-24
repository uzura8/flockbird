<?php

function field_public_flag($value = null, $type = 'select', $atter = array(), $name = 'public_flag')
{
	if (!in_array($type, array('select', 'radio')))
	{
		throw new InvalidArgumentException('Second parameter is invalid.');
	}

	if (is_null($value)) $value = conf('public_flag.default');
	$options = Site_Form::get_public_flag_options();

	$data = array(
		'value' => $value,
		'name'  => $name,
		'atter' => $atter,
		'options' => $options,
	);

	if ($type == 'select')
	{
		$atter['class'] = 'form-control';
		$data['atter'] = $atter;

		return render('_parts/field/select', $data);
	}

	return render('_parts/field/radio', $data);
}

function field_select($name = null, $value = null, $options = array(), $attr = array())
{
	$attr['class'] = 'form-control';
	
	return render('_parts/field/select', array(
		'name' => $name,
		'value' => $value,
		'options' => $options,
		'atter' => $attr,
	));
}
