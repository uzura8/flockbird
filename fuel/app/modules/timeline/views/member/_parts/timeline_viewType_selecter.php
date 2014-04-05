<?php
$viewType_options = \Timeline\Form_MemberConfig::get_viewType_options(null, true);
$menus = array();
foreach ($viewType_options as $value => $label)
{
	$menu = array('label' => $label);
	if ($value == $timeline_viewType)
	{
		$menu['tag'] = 'span';
		$menu['attr'] = array('class' => 'disabled');
	}
	else
	{
		$menu['tag'] = 'a';
		$menu['attr'] = array('class' => 'timeline_viewType', 'data-value' => $value, 'data-member_id' => $id);
	}
	$menus[] = $menu;
}
echo btn_dropdown(\Timeline\Form_MemberConfig::get_viewType_options($timeline_viewType, true), $menus, null, null, null, array('class' => 'text-left'));
?>
