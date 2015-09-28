<?php if (check_acl($uri = 'admin/member/delete')): ?>
<?php
$menus = array(
	array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => $uri.'/'.$member->id,
		'data-msg' => term('common.force', 'site.left').'します。よろしいですか？',
	)),
);
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
<?php endif; ?>
