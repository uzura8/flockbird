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

function form_input(Validation $val, $name, $default_value = '', $col_sm_size = 12, $label_col_sm_size = 2, $help = '')
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
		'help' => $help,
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

function form_select(Validation $val, $name, $default_value = '', $col_sm_size = 12, $label_col_sm_size = 2, $help = '')
{
	$field = $val->fieldset()->field($name);
	$atter = array(
		'id'    => 'form_'.$name,
		'class' => 'form-control',
	);
	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'options' => $field->get_options(),
		'atter' => $atter,
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'col_sm_size' => $col_sm_size,
		'label_col_sm_size' => $label_col_sm_size,
		'help' => $help,
	);

	return render('_parts/form/select', $data);
}

function form_checkbox(Validation $val, $name, $default_value = '', $label_col_sm_size = 2)
{
	$field = $val->fieldset()->field($name);
	$atter = array(
		'id'    => 'form_'.$name,
		'class' => 'form-control',
	);
	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
	);

	return render('_parts/form/checkbox', $data);
}

function form_radio(Validation $val, $name, $default_value = '', $label_col_sm_size = 2, $is_inline_options = false, $help = '')
{
	$field = $val->fieldset()->field($name);
	$atter = array(
		'id'    => 'form_'.$name,
		'class' => 'form-control',
	);
	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'options' => $field->get_options(),
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
		'is_inline_options' => $is_inline_options,
		'help' => $help,
	);

	return render('_parts/form/radio', $data);
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

function form_public_flag(Validation $val, $default_value = null, $is_select = false, $label_col_sm_size = 2, $with_no_change_option = false, $name = 'public_flag', $is_inline_options = false)
{
	$field = $val->fieldset()->field($name);
	$atter = array();
	if (is_null($default_value)) $default_value = Config::get('site.public_flag.default');
	$label = $field->get_attribute('label', Config::get('term.public_flag.label'));

	$options = $field->get_options();
	if ($with_no_change_option) $options = array(99 => '変更しない') + $options;

	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $label,
		'options' => $options,
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
	);

	if ($is_select)
	{
		$col_sm_size = 6;
		$atter['id'] = 'form_'.$name;
		$atter['class'] = 'form-control';

		$data['atter'] = $atter;
		$data['col_sm_size'] = $col_sm_size;

		return render('_parts/form/select', $data);
	}

	$data['is_inline_options'] = $is_inline_options;

	return render('_parts/form/radio', $data);
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
