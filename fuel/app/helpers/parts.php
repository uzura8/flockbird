<?php

function icon($icon_key, $class_prefix = 'glyphicon glyphicon-', $tag = 'i')
{
	$attr = array('class' => $class_prefix.$icon_key);

	return html_tag($tag, $attr, '');
}

function icon_label($icon_term, $type = 'both', $is_hidden_xs = true, $absolute_icon_key = null, $class_prefix = 'glyphicon glyphicon-', $tag = 'i', $delimitter = ' ')
{
	if (empty($type)) $type = 'both';
	if (!in_array($type, array('both', 'icon', 'label'))) throw new \InvalidArgumentException('Second parameter is invalid.');

	$term = term($icon_term);
	$label = $is_hidden_xs ? sprintf('<span class="hidden-xs-inline">%s%s</span>', $delimitter, $term) : $delimitter.$term;
	if ($type == 'label') return $label;

	if ($absolute_icon_key)	
	{
		$icon_key = $absolute_icon_key;
	}
	else
	{
		$icon_key = Config::get('icon.'.$icon_term);
	}
	$icon = $icon_key ? icon($icon_key, $class_prefix, $tag) : '';
	if ($type == 'icon') return $icon;

	return $icon.$label;
}

function btn($type, $href = '#', $class_name = '', $with_text = true, $size = '', $btn_type = null, $attr = array(), $tag = null, $form_name = null, $with_caret = false)
{
	if (!$tag) $tag = 'a';
	if (!in_array($tag, array('a', 'button'))) throw new \InvalidArgumentException('Parameter tag is invalid.');

	$icon_label_key = 'form.'.$type;
	$label = icon_label($icon_label_key, $with_text ? 'both' : 'icon');
	if (!$label) throw new \InvalidArgumentException('First parameter is invalid.');
	if ($with_caret) $label .= '<span class="caret"></span>';

	switch ($type)
	{
		case 'delete':
		case 'do_delete':
			if (is_null($btn_type)) $btn_type = 'danger';
			break;
		case 'create':
			if (is_null($btn_type)) $btn_type = 'warning';
			break;
		case 'preview':
			$attr['target'] = '_blank';
			break;
	}
	if (empty($attr['data-msg']) && $msg = Site_Util::get_confirm_msg($type))
	{
		$attr['data-msg'] = $msg;
	}

	if (is_null($btn_type)) $btn_type = 'default';
	$class_items   = array();
	$class_items[] = 'btn';
	$class_items[] = 'btn-'.$btn_type;
	if ($class_name) $class_items[] = $class_name;
	if ($size) $class_items[] = 'btn-'.$size;
	if (isset($attr['class'])) $class_items[] = $attr['class'];
	$attr['class'] = implode(' ', $class_items);

	if ($tag == 'button')
	{
		if (!$form_name) $form_name = 'button';
		return Form::button($form_name, $label, $attr);
	}

	return Html::anchor($href, $label, $attr);
}

function btn_dropdown($type = null, $menus = array(), $btn_with_text = true, $btn_size = '', $btn_type = 'default', $is_popover_align_right = false, $btn_group_attrs = array())
{
	if (!$btn_type) $btn_type = 'default';
	$data = array(
		'type' => $type,
		'btn_with_text' => $btn_with_text,
		'btn_size' => $btn_size,
		'btn_type' => $btn_type,
		'is_popover_align_right' => $is_popover_align_right,
		'menus' => $menus,
		'btn_group_attrs' => $btn_group_attrs,
	);
	$view = View::forge('_parts/btn_dropdown', $data);

	return $view->render();
}

function anchor($href, $text, $is_admin, $attr = array(), $is_absolute_ext_uri = false)
{
	if ($is_absolute_ext_uri || Site_Util::check_ext_uri($href, $is_admin))
	{
		$attr['target'] = '_blank';
		$text .= ' '.icon('new-window');
	}

	return Html::anchor($href, $text, $attr);
}

function anchor_icon($href, $icon_term, $attr = array(), $type = 'both', $is_hidden_xs = false, $absolute_icon_key = null, $is_admin = false, $is_absolute_ext_uri = false, $class_prefix = 'glyphicon glyphicon-', $tag = 'i', $delimitter = ' ')
{
	$element = icon_label($icon_term, $type, $is_hidden_xs, $absolute_icon_key, $class_prefix, $tag, $delimitter);

	return anchor($href, $element, $is_admin, $attr, $is_absolute_ext_uri);
}

function alert($message, $type = 'info', $with_dismiss_btn = false)
{
 return render('_parts/alerts', array('message' => $message, 'type' => $type, 'with_dismiss_btn' => $with_dismiss_btn));
}

function small_tag($str, $is_enclose_small_tag = true)
{
	return sprintf('%s%s%s', $is_enclose_small_tag ? '<small>' : '', $str, $is_enclose_small_tag ? '</small>' : '');
}

function label($name, $type = 'default', $attrs = array())
{
 return render('_parts/label', array('name' => $name, 'type' => $type, 'attrs' => $attrs));
}
