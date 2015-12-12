<?php
$menus = array();
if (is_enabled('message') && check_acl($uri = 'admin/message/create'))
{
	$menus[] = array('icon_term' => 'message.view', 'attr' => array(
		'class' => 'js-simpleLink',
		'data-uri' => 'admin/message/create/member/'.$member->id,
	));
}
if (check_acl($uri = 'admin/member/delete'))
{
	$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => $uri.'/'.$member->id,
		'data-msg' => term('common.force', 'site.left').'します。よろしいですか？',
	));
}
?>
<?php if ($menus): ?>
<?php echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit')); ?>
<?php endif; ?>
