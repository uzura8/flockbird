<?php

function icon($icon_key, $class_prefix = 'glyphicon glyphicon-', $tag = 'i')
{
	$attr = array('class' => $class_prefix.$icon_key);

	return html_tag($tag, $attr, '');
}

function icon_label($icon_key, $term = '', $is_raw_label = false, $is_hidden_xs = true, $class_prefix = 'glyphicon glyphicon-', $tag = 'i', $delimitter = ' ')
{
	if (!$term) return icon($icon_key, $class_prefix, $tag);

	if (!$is_raw_label) $term = term($term);
	$label = $is_hidden_xs ? sprintf('<span class="hidden-xs-inline">%s%s</span>', $delimitter, $term) : $term;

	return icon($icon_key, $class_prefix, $tag).$label;
}

function btn($type, $href = '#', $class_name = '', $with_text = false, $size = '', $btn_type = null, $attr = array(), $tag = null, $form_name = null)
{
	if (!$tag) $tag = 'a';
	if (!in_array($tag, array('a', 'button'))) throw new \InvalidArgumentException('Parameter tag is invalid.');

	$label_text = term('form.'.$type);
	switch ($type)
	{
		case 'add':
		case 'do_add':
		case 'create':
		case 'do_create':
			$label_icon = 'plus';
			break;
		case 'edit':
		case 'do_edit':
			$label_icon = 'edit';
			break;
		case 'delete':
		case 'do_delete':
			$label_icon = 'trash';
			if (is_null($btn_type)) $btn_type = 'danger';
			if (!isset($attr['data-msg'])) $attr['data-msg'] = '削除します。よろしいですか？';
			break;
		case 'publish':
		case 'do_publish':
			$label_icon = 'globe';
			//if (is_null($btn_type)) $btn_type = 'warning';
			if (!isset($attr['data-msg'])) $attr['data-msg'] = '公開します。よろしいですか？';
			break;
		case 'unpublish':
		case 'do_unpublish':
			$label_icon  = 'lock';
			if (!isset($attr['data-msg'])) $attr['data-msg'] = '非公開にします。よろしいですか？';
			break;
		case 'preview':
			$label_icon  = 'eye-open';
			$attr['target'] = '_blank';
			break;
		default :
			throw new \InvalidArgumentException("First parameter is invalid.");
			break;
	}

	$label = $with_text ? icon_label($label_icon, $label_text) : icon($label_icon);

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

function btn_dropdown($btn_label, $menus = array(), $btn_size = '', $btn_type = 'default', $is_popover_align_right = false, $attrs = array(), $btn_group_attrs = array())
{
	if (!$btn_type) $btn_type = 'default';
	$data = array(
		'btn_size' => $btn_size,
		'btn_type' => $btn_type,
		'is_popover_align_right' => $is_popover_align_right,
		'attrs' => $attrs,
		'menus' => $menus,
		'btn_group_attrs' => $btn_group_attrs,
	);
	$view = View::forge('_parts/btn_dropdown', $data);
	$view->set_safe('btn_label', $btn_label);

	return $view->render();
}

function anchor_button($href, $icon_key = '', $text = '', $class_attr_add = '', $attr = array(), $is_mini_btn = false)
{
	$class_attrs  = array('btn', 'btn-default');
	if ($is_mini_btn) $class_attrs[] = 'btn-xs';

	$class_attr = implode(' ', $class_attrs);
	if ($class_attr_add) $class_attr .= ' '.$class_attr_add;

	if (!empty($attr['class'])) $class_attr .= ' '.$attr['class'];
	$attr['class'] = $class_attr;

	$element = icon_label($icon_key, $text, true, true);

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

function create_anchor_button($href, $class_attr_add = 'btn-warning', $attr = array(), $is_mini_btn = false)
{
	return anchor_button($href, 'plus', term('form.create'), $class_attr_add, $attr, $is_mini_btn);
}

function edit_anchor_button($href, $class_attr_add = null, $attr = array(), $is_mini_btn = false)
{
	return anchor_button($href, 'edit', term('form.edit'), $class_attr_add, $attr, $is_mini_btn);
}

function label($name, $type = 'default', $attrs = array())
{
 return render('_parts/label', array('name' => $name, 'type' => $type, 'attrs' => $attrs));
}
