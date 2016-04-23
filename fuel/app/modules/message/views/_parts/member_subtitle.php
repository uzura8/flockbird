<?php if (!empty($report_data)): ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_message_member',
	'class' => array('dropdown', 'edit'),
);
$menus = array(
	array(
		'icon_term' => 'form.post_report',
		'attr' => array(
			'class' => 'js-modal',
			'data-target' => '#modal_report',
			'data-uri' => 'contact/report/api/form.html',
			'data-get_data' => json_encode($report_data),
		),
	),
);
echo btn_dropdown('noterm.dropdown', $menus, false, 'xs', null, true, $dropdown_btn_group_attr, null, false);
?>
<?php endif; ?>

