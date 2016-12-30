<?php
echo render('_parts/list_group', array('items' => array(array(
	'link' => 'member/setting',
	'text' => t('form.back_to', array('label' => t('site.setting'))),
))));
?>
