<?php

function form_open($exists_required_fields = false, $is_upload = false, $atter = array(), $hidden = array(), $form_title = '')
{
	$atter_default = array(
		'class'  => 'form-stacked form-horizontal',
		'method' => 'post',
		'id'     => site_get_form_id(),
	);
	$atter = array_merge($atter_default, $atter);
	if ($is_upload) $atter['enctype'] = 'multipart/form-data';

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

function form_input(Validation $val, $name, $default_value = '', $col_sm_size = 12, $label_col_sm_size = 2)
{
	$field = $val->fieldset()->field($name);
	$input_atter = array(
		'type'  => $field->get_attribute('type'),
		'id'    => 'form_'.$name,
		'class' => 'form-control',
	);
	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'input_atter'   => $input_atter,
		'col_sm_size'   => $col_sm_size,
		'label_col_sm_size' => $label_col_sm_size,
	);

	return render('_parts/form/input', $data);
}

function form_file($name, $label = null, $is_required = false, $input_class = 'input-file', $default_value = null, $val_obj = null)
{
	$input_atter = array(
		'type'  => 'file',
		'id'    => 'form_'.$name,
		'class' => $input_class,
	);
	$data = array(
		'name'  => $name,
		'label' => $label,
		'is_required'   => $is_required,
		'input_atter'   => $input_atter,
		'default_value' => $default_value,
		'val' => $val_obj,
	);

	return render('_parts/form/file', $data);
}

function form_textarea(Validation $val, $name, $default_value = '', $label_col_sm_size = 2, $is_autogrow = true)
{
	$field = $val->fieldset()->field($name);
	$atter = array(
		'id'    => 'form_'.$name,
		'rows'  => $field->get_attribute('rows'),
		'class' => 'form-control',
	);
	if ($is_autogrow) $atter['class'] .= ' autogrow';

	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'default_value' => $default_value,
		'atter' => $atter,
		'is_required' => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
	);

	return render('_parts/form/textarea', $data);
}

function form_button($label = '', $type = 'submit', $name = '', $atter = array(), $offset_size = 2)
{
	if (!strlen($label)) $label = term('form.submit');
	$atter_default = array(
		'class' => 'btn btn-default btn-primary',
		'id'    => 'form_'.$type,
		'type'  => $type,
	);
	if (!is_array($atter)) $atter = (array)$atter;
	$atter = array_merge($atter_default, $atter);

	if (!strlen($name)) $name = $type;

	$data = array(
		'name'  => $name,
		'atter' => $atter,
		'offset_size' => $offset_size,
	);
	$view = \View::forge('_parts/form/button', $data);
	$view->set_safe('label', $label);

	return $view;
}

function form_anchor($href, $label, $atter = array(), $offset_size = 2, $secure = null, $is_enclose_small_tag = false)
{
	$data = array(
		'href'   => $href,
		'atter'  => $atter,
		'secure' => $secure,
		'offset_size' => $offset_size,
		'is_enclose_small_tag' => $is_enclose_small_tag,
	);
	$view = \View::forge('_parts/form/anchor', $data);
	$view->set_safe('label', $label);

	return $view;
}

function form_radio_public_flag($val_obj = null, $default_value = null, $with_no_change_option = false)
{
	$data = array(
		'val' => $val_obj,
		'with_no_change_option' => $with_no_change_option,
		'default_value' => isset($default_value) ? $default_value : Config::get('site.public_flag.default'),
	);

	return render('_parts/form/radio_public_flag', $data);
}

function form_text($value, $label, $label_col_sm_size = 2)
{
	$data = array(
		'value' => $value,
		'label' => $label,
		'label_col_sm_size' => $label_col_sm_size,
	);

	return render('_parts/form/text', $data);
}

function form_upload_files($files, $hide_form = false, $is_raw_form = false, $is_horizontal = true, $thumbnail_size = 'M', $selects = array())
{
	return render('_parts/form/upload_files', array(
		'files' => $files,
		'is_raw_form' => $is_raw_form,
		'hide_form' => $hide_form,
		'is_horizontal' => $is_horizontal,
		'thumbnail_size' => $thumbnail_size,
		'selects' => $selects,
	));
}
