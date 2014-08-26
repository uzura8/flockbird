<?php
$link_count_attr = array(
	'class' => 'js-popover',
	'id' => 'link_like_count_'.$id,
	'data-html' => 1,
	'data-content' => sprintf("<div id='popover_content_like_count_%d'></div>", $id),
	'data-content_id' => sprintf('#popover_content_like_count_%d', $id),
	'data-placement' => 'bottom',
	'data-tmpl' => '#linked_member_simple-template',
	'data-uri' => sprintf('timeline/like/api/member/%d.json', $id),
);

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
<small<?php if (!empty($left_margin)): ?> class="ml10"<?php endif; ?>>
<?php echo anchor('#', icon('form.like').' '.html_tag('span', $count_attr, isset($count) ? $count : ''), false, $link_count_attr); ?>
</small>
<?php if (!empty($link_display_absolute) || Auth::check()): ?>
<small class="ml3"><?php echo anchor('#', empty($is_liked) ? term('form.do_like') : term('form.undo_like'), false, $link_attr); ?></small>
<?php endif; ?>
