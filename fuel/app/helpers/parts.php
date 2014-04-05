<?php

function icon($icon_key, $class_prefix = 'glyphicon glyphicon-', $tag = 'span')
{
	$attr = array('class' => $class_prefix.$icon_key);

	return html_tag($tag, $attr, '');
}

function btn($type, $href = '#', $class_name = '', $with_text = false, $size = '', $btn_type = 'default', $attr = array(), $exception_label = '')
{
	switch ($type)
	{
		case 'edit':
			$label_text = '編集';
			$label_icon  = 'glyphicon glyphicon-edit';
			break;
		case 'delete':
			$label_text = '削除';
			$label_icon  = 'glyphicon glyphicon-trash';
			break;
		default :
			throw new \InvalidArgumentException("First parameter must be 'edit' or 'delete'.");
			break;
	}

	$label  = sprintf('<i class="%s"></i>', $label_icon);
	$label .= $with_text ? ' '.$label_text : '';

	$class_items   = array();
	$class_items[] = 'btn';
	$class_items[] = 'btn-'.$btn_type;
	if ($class_name) $class_items[] = $class_name;
	if ($size) $class_items[] = 'btn-'.$size;
	if (isset($attr['class'])) $class_items[] = $attr['class'];
	$attr['class'] = implode(' ', $class_items);

	return Html::anchor($href, $label, $attr);
}

function btn_dropdown($btn_label, $menus = array(), $btn_size = '', $btn_type = 'default', $attrs = array(), $btn_group_attrs = array())
{
	if (!$btn_type) $btn_type = 'default';
	return render('_parts/btn_dropdown', array(
		'btn_label' => $btn_label,
		'btn_size' => $btn_size,
		'btn_type' => $btn_type,
		'attrs' => $attrs,
		'menus' => $menus,
		'btn_group_attrs' => $btn_group_attrs,
	));
}

function anchor_button($href, $icon_class = '', $text = '', $class_attr_add = '', $attr = array(), $is_mini_btn = false, $is_sp = false, $is_force_btn = false, $is_force_loud_color = false)
{
	$class_attrs  = array('btn', 'btn-default');
	if ($is_mini_btn) $class_attrs[] = 'btn-xs';

	if ($is_sp && !$is_force_btn)
	{
		$class_attrs = array();
		if (!$is_force_loud_color) $class_attrs = array('cl-modest');
	}
	$class_attr = implode(' ', $class_attrs);
	if ($class_attr_add) $class_attr .= ' '.$class_attr_add;

	if (!empty($attr['class'])) $class_attr .= ' '.$attr['class'];
	$attr['class'] = $class_attr;

	$element = '';
	if ($icon_class) $element = sprintf('<i class="%s"></i>', $icon_class);
	if ($text) $element .= ' '.$text;

	return Html::anchor($href, $element, $attr);
}

function alert($message, $type = 'info', $with_dismiss_btn = false)
{
 return render('_parts/alerts', array('message' => $message, 'type' => $type, 'with_dismiss_btn' => $with_dismiss_btn));
}

function small_tag($str, $is_enclose_small_tag = true)
{
	return sprintf('%s%s%s', $is_enclose_small_tag ? '<small>' : '', $str, $is_enclose_small_tag ? '</small>' : '');
}
