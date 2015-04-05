<?php echo render('_parts/member_contents_box', array(
	'member'      => $thread->member,
	'id'          => $thread->id,
	'public_flag' => $thread->public_flag,
	'public_flag_option_type' => 'public',
	'model'       => 'thread',
	'size'        => 'M',
	'date'        => array(
		'datetime' => $thread->created_at,
		'label'    => term('form.create_simple', 'site.datetime'),
	)
)); ?>

<?php if (Auth::check()): ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_'.$thread->id,
	'class' => array('dropdown', 'boxBtn', 'edit'),
);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('thread/api/menu/%d.html?is_detail=1', $thread->id),
	'data-member_id' => $thread->member_id,
	'data-menu' => '#menu_'.$thread->id,
	'data-loaded' => 0,
);
echo btn_dropdown('noterm.dropdown', array(), false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
<?php endif; ?>
