<?php echo render('_parts/member_contents_box', array(
	'member'      => $note->member,
	'id'          => $note->id,
	'public_flag' => $note->public_flag,
	'model'       => 'note',
	'size'        => 'M',
	'date'        => array(
		'datetime' => $note->published_at ? $note->published_at : $note->updated_at,
		'label'    => $note->published_at ? term('site.datetime') : term('form.updated', 'site.datetime'),
	)
)); ?>

<?php if (Auth::check()): ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_menu_'.$note->id,
	'class' => array('dropdown', 'boxBtn', 'edit'),
);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('note/api/menu/%d.html?is_detail=1', $note->id),
	'data-detail_uri' => 'note/'.$note->id,
	//'data-parent' => 'article_'.$note->id,
	'data-member_id' => $note->member_id,
	'data-loaded' => 0,
);
echo btn_dropdown('noterm.dropdown', array(), false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
<?php endif; ?>
