<?php
if (!isset($attr_prefix)) $attr_prefix = '';

$class_name_modal = $attr_prefix.'modal_like_count';
$class_name = $attr_prefix.'link_like_count';
$link_count_attr = array(
	'class' => 'js-modal '.$class_name,
	'id' => $class_name.'_'.$id,
	'data-target' => '#modal_like_member',
	'data-uri' => $get_member_uri,
	'data-is_list' => 1,
);
$modal_block_attrs = array(
	'id' => $class_name_modal.'_'.$id,
);

$class_name_count = $attr_prefix.'like_count';
$id_name_count = $class_name_count.'_'.$id;
$count_attr_default = array(
	'class' => array($class_name_count),
	'id' => $id_name_count,
	'data-id' => $id,
);
if (!isset($count_attr)) $count_attr = array();
$count_attr = Util_Toolkit::convert_to_attr($count_attr, $count_attr_default);
?>
<small>
<?php echo anchor('#', icon('form.like').' '.html_tag('span', $count_attr, isset($count) ? $count : ''), false, $link_count_attr); ?>
</small>

<?php // execute like
$class_name = $attr_prefix.'link_like';
$link_attr_default = array(
	'class' => array('js-like', $class_name, 'mr3'),
	'id' => $class_name.'_'.$id,
	'data-id' => $id,
	'data-uri' => $post_uri,
	'data-count' => '#'.$id_name_count,
);
if (!isset($link_attr)) $link_attr = array();
$link_attr = Util_Toolkit::convert_to_attr($link_attr, $link_attr_default);
?>
<?php if (!empty($link_display_absolute) || Auth::check()): ?>
<small class="mr10"><?php echo anchor('#', empty($is_liked) ? term('form.do_like') : term('form.undo_like'), false, $link_attr); ?></small>
<?php endif; ?>
