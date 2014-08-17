<?php
$count_attr_default = array(
	'class' => array('like_count'),
	'id' => 'like_count_'.$id,
	'data-id' => $id,
);
if (!isset($count_attr)) $count_attr = array();
$count_attr = Util_Toolkit::convert_to_attr($count_attr, $count_attr_default);

$link_attr_default = array(
	'class' => array('js-like'),
	'id' => 'link_like_'.$id,
	'data-id' => $id,
	'data-uri' => $uri,
	'data-count' => '#like_count_'.$id,
);
if (!isset($link_attr)) $link_attr = array();
$link_attr = Util_Toolkit::convert_to_attr($link_attr, $link_attr_default);
?>
<small<?php if (!empty($left_margin)): ?> class="ml10"<?php endif; ?>><?php echo icon('form.like'); ?> <?php echo html_tag('span', $count_attr, isset($count) ? $count : ''); ?></small>
<?php if (!empty($link_display_absolute) || Auth::check()): ?>
<small class="ml3"><?php echo anchor('#', term('form.do_like'), false, $link_attr); ?></small>
<?php endif; ?>
