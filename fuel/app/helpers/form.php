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

function form_input(Validation $val, $name, $default_value = null, $col_sm_size = 12, $label_col_sm_size = 2, $help = '', $optional_public_flag = array())
{
	$field = $val->fieldset()->field($name);
	$input_atter = array(
		'type'  => $field->get_attribute('type'),
		'id'    => 'form_'.$name,
		'class' => 'form-control',
		'placeholder' => $field->get_attribute('placeholder'),
	);
	if (!is_null($field->get_attribute('value')))
	{
		$default_value = $field->get_attribute('value');
	}
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
		'optional_public_flag' => $optional_public_flag,
	);

	return render('_parts/form/input', $data);
}

function form_input_datetime(Validation $val, $name, $default_value = null, $format = null, $col_sm_size = 6, $label_col_sm_size = 2, $help = '', $optional_public_flag = array())
{
	if (is_null($format)) $format = 'YYYY-MM-DD HH:mm';

	$field = $val->fieldset()->field($name);
	$input_atter = array(
		'type'  => $field->get_attribute('type'),
		'id'    => 'form_'.$name,
		'class' => 'form-control',
		'placeholder' => $field->get_attribute('placeholder'),
	);
	if (!is_null($field->get_attribute('value')))
	{
		$default_value = $field->get_attribute('value');
	}
	$data = array(
		'val'    => $val,
		'name'   => $name,
		'format' => $format,
		'label'  => $field->get_attribute('label'),
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'input_atter'   => $input_atter,
		'col_sm_size'   => $col_sm_size,
		'label_col_sm_size' => $label_col_sm_size,
		'help' => $help,
		'optional_public_flag' => $optional_public_flag,
	);

	return render('_parts/form/input_datetime', $data);
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

function form_textarea(Validation $val, $name, $default_value = null, $label_col_sm_size = 2, $is_autogrow = true, $help = '', $optional_public_flag = array(), $use_wysiwyg_editor = false)
{
	$field = $val->fieldset()->field($name);
	$atter = array(
		'id'    => 'form_'.$name,
		'rows'  => $field->get_attribute('rows'),
		'class' => 'form-control',
		'placeholder' => $field->get_attribute('placeholder'),
	);
	if ($is_autogrow) $atter['class'] .= ' autogrow';
	if ($use_wysiwyg_editor)
	{
		//$atter['id'] = 'summernote';
		$label_col_sm_size = 12;
	}
	if (!is_null($field->get_attribute('value')))
	{
		$default_value = $field->get_attribute('value');
	}
	elseif (!strlen($default_value) && $use_wysiwyg_editor)
	{
		$default_value = "<br>\n";
	}

	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'default_value' => $default_value,
		'atter' => $atter,
		'is_required' => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
		'help' => $help,
		'optional_public_flag' => $optional_public_flag,
	);

	return render('_parts/form/textarea', $data);
}

function form_select(Validation $val, $name, $default_value = '', $col_sm_size = 12, $label_col_sm_size = 2, $help = '', $optional_public_flag = array())
{
	$field = $val->fieldset()->field($name);
	$atter = array(
		'id'    => 'form_'.$name,
		'class' => 'form-control',
	);
	if (!is_null($field->get_attribute('value')))
	{
		$default_value = $field->get_attribute('value');
	}
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
		'optional_public_flag' => $optional_public_flag,
	);

	return render('_parts/form/select', $data);
}

function form_checkbox(Validation $val, $name, $default_value = null, $label_col_sm_size = 2, $layout_type = 'block', $help = '', $optional_public_flag = array(), $is_small_tag = false)
{
	$field = $val->fieldset()->field($name);
	$atter = array('id' => 'form_'.$name);
	if (!is_null($field->get_attribute('value')))
	{
		$default_value = $field->get_attribute('value');
	}
	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'options' => $field->get_options(),
		'atter' => $atter,
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
		'layout_type' => $layout_type,
		'help' => $help,
		'optional_public_flag' => $optional_public_flag,
		'is_small_tag' => $is_small_tag,
	);

	return render('_parts/form/checkbox', $data);
}

function form_radio(Validation $val, $name, $default_value = null, $label_col_sm_size = 2, $layout_type = 'block', $help = '', $optional_public_flag = array())
{
	if (!in_array($layout_type, array('block', 'inline', 'grid'))) throw new InvalidArgumentException('Fifth parameter is invalid.');

	$field = $val->fieldset()->field($name);
	$atter = array(
		'id'    => 'form_'.$name,
	);
	if (!is_null($field->get_attribute('value')))
	{
		$default_value = $field->get_attribute('value');
	}
	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $field->get_attribute('label'),
		'options' => $field->get_options(),
		'atter' => $atter,
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
		'layout_type' => $layout_type,
		'help' => $help,
		'optional_public_flag' => $optional_public_flag,
	);

	return render('_parts/form/radio', $data);
}

function form_button($term_key = '', $type = 'submit', $name = '', $atter = array(), $offset_size = 2)
{
	if (!$term_key) $term_key = 'form.submit';
	$label = icon_label($term_key, 'both', false);

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

function form_anchor($href, $anchor_label, $atter = array(), $offset_size = 2, $secure = null, $is_enclose_small_tag = false, $label = '')
{
	$data = array(
		'href'   => $href,
		'atter'  => $atter,
		'secure' => $secure,
		'offset_size' => $offset_size,
		'is_enclose_small_tag' => $is_enclose_small_tag,
		'label' => $label,
	);
	$view = \View::forge('_parts/form/anchor', $data);
	$view->set_safe('anchor_label', $anchor_label);

	return $view;
}

function form_anchor_delete($post_uri, $anchor_label = null, $attr = null, $offset_size = 2, $secure = null, $is_enclose_small_tag = false)
{
	if (is_null($anchor_label)) $anchor_label = icon_label('form.do_delete', 'both', false);
	if (is_null($attr))  $attr = array('id' => 'btn_delete', 'class' => 'btn btn-default btn-danger js-simplePost');
	$attr['data-uri'] = $post_uri;
	$attr['data-msg'] = '削除します。よろしいですか？';

	$data = array(
		'href'   => '#',
		'atter'  => $attr,
		'secure' => $secure,
		'offset_size' => $offset_size,
		'is_enclose_small_tag' => $is_enclose_small_tag,
	);
	$view = \View::forge('_parts/form/anchor', $data);
	$view->set_safe('anchor_label', $anchor_label);

	return $view;
}

function form_public_flag(Validation $val, $default_value = null, $is_select = false, $label_col_sm_size = 2, $with_no_change_option = false, $name = 'public_flag', $is_inline_options = false)
{
	$field = $val->fieldset()->field($name);
	$atter = array('id' => 'form_'.$name);
	if (is_null($default_value)) $default_value = $field->get_attribute('value', conf('public_flag.default'));
	$label = $field->get_attribute('label', term('public_flag.label'));

	$options = $field->get_options();
	if ($with_no_change_option) $options = array(99 => '変更しない') + $options;

	$data = array(
		'val'   => $val,
		'name'  => $name,
		'label' => $label,
		'options' => $options,
		'atter' => $atter,
		'default_value' => $default_value,
		'is_required'   => $field->get_attribute('required') == 'required',
		'label_col_sm_size' => $label_col_sm_size,
		'optional_public_flag' => false,
	);

	if ($is_select)
	{
		$col_sm_size = 6;
		$atter['class'] = 'form-control';

		$data['atter'] = $atter;
		$data['col_sm_size'] = $col_sm_size;

		return render('_parts/form/select', $data);
	}
	$data['layout_type'] = 'block';

	return render('_parts/form/radio', $data);
}

function form_date(Validation $val, $label, $name_month, $name_day, $label_col_sm_size = 2, $help = '', $optional_public_flag = array(), $def_val_month = null, $def_val_day = null)
{
	$fields = array();
	$atters = array();
	$options = array();
	$names = array('month', 'day');
	foreach ($names as $name)
	{
		$val_name = 'name_'.$name;
		$val_name = 'name_'.$name;
		$fields[$name] = $val->fieldset()->field($$val_name);
		$atters[$name] = array(
			'id'    => 'form_'.$name,
			'class' => 'form-control',
		);
		if ($fields[$name]->get_attribute('required') == 'required') $atters[$name]['required'] = 'required';
		$val_name = 'def_val_'.$name;
		if (!is_null($fields[$name]->get_attribute('value')))
		{
			$$val_name = $fields[$name]->get_attribute('value');
		}
		elseif (is_null($$val_name))
		{
			$$val_name = 1;
		}
		$options[$name] = !is_null($fields[$name]->get_options()) ? $fields[$name]->get_options() : Form_Util::get_int_options(1, ($name == 'month') ? 12 : 31);
	}
	$data = array(
		'val'   => $val,
		'name_month' => $name_month,
		'name_day' => $name_day,
		'def_val_month' => $def_val_month,
		'def_val_day' => $def_val_day,
		'label' => $label,
		'options' => $options,
		'atters' => $atters,
		'is_required' => (!empty($atters['month']['required']) && !empty($atters['day']['required'])),
		'label_col_sm_size' => $label_col_sm_size,
		'help' => $help,
		'optional_public_flag' => $optional_public_flag,
	);

	return render('_parts/form/date', $data);
}

function form_text($value, $label, $label_col_sm_size = 2, $is_safe_value = false, $optional_link = array())
{
	$data = array(
		'value' => $value,
		'label' => $label,
		'label_col_sm_size' => $label_col_sm_size,
		'optional_link' => $optional_link,
	);
	$view = View::forge('_parts/form/text', $data);
	if ($is_safe_value) $view->set_safe('value', $value);
	if (!empty($optional_link['is_safe_text'])) $view->set_safe('optional_link_text', $optional_link['text']);

	return $view->render();
}

function form_upload_files($files, $hide_form = false, $is_raw_form = false, $is_horizontal = true, $thumbnail_size = 'M', $selects = array(), $model = 'album', $label = null, $offset_size = 2, $upload_type = 'img')
{
	return render('_parts/form/upload_files', array(
		'files' => $files,
		'is_raw_form' => $is_raw_form,
		'hide_form' => $hide_form,
		'is_horizontal' => $is_horizontal,
		'thumbnail_size' => $thumbnail_size,
		'selects' => $selects,
		'model' => $model,
		'label' => $label,
		'offset_size' => $offset_size,
		'upload_type' => $upload_type,
	));
}

function form_required_tag($mark = '*')
{
	return '<span class="required">'.$mark.'</span>';
}
