<?php

function field_public_flag($value = null, $type = 'select', $atter = array(), $name = 'public_flag')
{
	if (!in_array($type, array('select', 'radio')))
	{
		throw new InvalidArgumentException('Second parameter is invalid.');
	}

	if (is_null($value)) $value = Config::get('site.public_flag.default');
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
