<?php

function form_open($exists_required_fields = false, $atter = array(), $hidden = array(), $form_title = '')
{
	$atter_default = array(
		'class'  => 'form-stacked form-horizontal',
		'method' => 'post',
		'id'     => site_get_form_id(),
	);
	$atter = array_merge($atter_default, $atter);

	$hidden_default = array(Config::get('security.csrf_token_key') => Util_security::get_csrf());
	$hidden = array_merge($hidden_default, $hidden);

	return render('_parts/form/open', array(
		'exists_required_fields' => $exists_required_fields,
		'atter' => $atter,
		'hidden' => $hidden,
		'title' => $form_title,
	));
}

function form_close()
{
	return render('_parts/form/close');
}

function form_input($val_obj, $name, $label = '', $default_value = '', $is_required = false, $input_class = 'span8', $type = 'text')
{
	$input_atter = array(
		'type'  => $type,
		'id'    => 'form_'.$name,
		'class' => $input_class,
	);
	$data = array(
		'val'   => $val_obj,
		'name'  => $name,
		'label' => $label,
		'default_value' => $default_value,
		'is_required'   => $is_required,
		'input_atter'   => $input_atter,
	);

	return render('_parts/form/input', $data);
}

function form_textarea($val_obj, $name, $label = '', $default_value = '', $is_required = false, $atter = array())
{
	$atter_default = array(
		'class' => 'input-xlarge',
		'id'    => 'form_'.$name,
		'cols'  => 60,
		'rows'  => 10,
	);
	$atter = array_merge($atter_default, $atter);

	$data = array(
		'val'   => $val_obj,
		'name'  => $name,
		'label' => $label,
		'default_value' => $default_value,
		'atter' => $atter,
		'is_required' => $is_required,
	);

	return render('_parts/form/textarea', $data);
}

function form_button($label = '送信', $type = 'submit', $name = '', $atter = array())
{
	$atter_default = array(
		'class' => 'btn',
		'id'    => 'form_'.$type,
		'type'  => $type,
	);
	$atter = array_merge($atter_default, $atter);

	if (!strlen($name)) $name = $type;

	$data = array(
		'name'  => $name,
		'atter' => $atter,
	);
	$view = \View::forge('_parts/form/button', $data);
	$view->set_safe('label', $label);

	return $view;
}

function form_radio_public_flag($val_obj, $default_value = null, $with_no_change_option = false)
{
	$data = array(
		'val' => $val_obj,
		'with_no_change_option' => $with_no_change_option,
		'default_value' => isset($default_value) ? $default_value : Config::get('site.public_flag.default'),
	);

	return render('_parts/form/radio_public_flag', $data);
}
