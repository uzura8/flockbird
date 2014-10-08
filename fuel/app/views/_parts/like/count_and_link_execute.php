<?php
if (!isset($attr_prefix)) $attr_prefix = '';

$class_name_modal = $attr_prefix.'modal_like_count';
$class_name = $attr_prefix.'link_like_count';
$link_count_attr = array(
	'class' => 'js-modal '.$class_name,
	'id' => $class_name.'_'.$id,
	'data-target' => '#'.$class_name_modal.'_'.$id,
	'data-uri' => $get_member_uri,
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
<small<?php if (!empty($left_margin)): ?> class="ml10"<?php endif; ?>>
<?php echo anchor('#', icon('form.like').' '.html_tag('span', $count_attr, isset($count) ? $count : ''), false, $link_count_attr); ?>
</small>
<?php echo render('_parts/modal', array(
	'block_attrs' => $modal_block_attrs,
	'is_display_header_close_btn' => true,
	'is_display_footer_close_btn' => true,
	'title' => sprintf('%sした%s', term('form.like'), term('member.view')),
	'size' => 'sm',
)); ?>

<?php // execute like
$class_name = $attr_prefix.'link_like';
$link_attr_default = array(
	'class' => array('js-like', $class_name),
	'id' => $class_name.'_'.$id,
	'data-id' => $id,
	'data-uri' => $post_uri,
	'data-count' => '#'.$id_name_count,
);
if (!isset($link_attr)) $link_attr = array();
$link_attr = Util_Toolkit::convert_to_attr($link_attr, $link_attr_default);
?>
<?php if (!empty($link_display_absolute) || Auth::check()): ?>
<small class="ml3"><?php echo anchor('#', empty($is_liked) ? term('form.do_like') : term('form.undo_like'), false, $link_attr); ?></small>
<?php endif; ?>
