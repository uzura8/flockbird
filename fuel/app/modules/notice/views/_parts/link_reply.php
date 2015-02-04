<?php
$link_attr_default = array(
	'class' => 'js-insert_text',
	'data-text' => '@'.$member_name,
	'data-open' => '#commentPostBox_'.$target_id,
	'data-hide' => '#link_show_comment_form_'.$target_id,
	'data-input' => '#textarea_comment_'.$target_id,
	//'data-open'  => empty($target_id) ? '#commentPostBox' : '#commentPostBox_'.$target_id,
	//'data-hide'  => empty($target_id) ? '#link_show_comment_form' : '#link_show_comment_form_'.$target_id,
	//'data-input' => empty($target_id) ? '#textarea_comment' : '#textarea_comment_'.$target_id,
);
if (!isset($link_attr)) $link_attr = array();
$link_attr = Util_Toolkit::convert_to_attr($link_attr, $link_attr_default);
?>
<?php if ((Auth::check() || !empty($link_display_absolute)) && empty($link_hide_absolute)): ?>
<small<?php if (!empty($left_margin)): ?> class="ml10"<?php endif; ?>>
<?php echo anchor('#', icon_label('form.do_reply'), false, $link_attr); ?>
</small>
<?php endif; ?>
