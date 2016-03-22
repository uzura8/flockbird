<?php
$count_attr_default = array(
	'class' => array('comment_count'),
	'id' => 'comment_count_'.$id,
	'data-id' => $id,
);
if (!isset($count_attr)) $count_attr = array();
$count_attr = Util_Toolkit::convert_to_attr($count_attr, $count_attr_default);

$link_attr_default = array(
	'class' => array('link_show_comment'),
	'id' => 'link_show_comment_'.$id,
	'data-id' => $id,
);
if (!isset($link_attr)) $link_attr = array();
$link_attr = Util_Toolkit::convert_to_attr($link_attr, $link_attr_default);
?>
<small class="mr8"><?php echo icon('comment'); ?> <?php echo html_tag('span', $count_attr, isset($count) ? $count : ''); ?></small>
<?php if ((Auth::check() || !empty($link_display_absolute)) && empty($link_hide_absolute)): ?>
<small class="mr10"><?php echo anchor('#', term('form.comment'), false, $link_attr); ?></small>
<?php endif; ?>
