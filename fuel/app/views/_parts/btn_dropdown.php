<?php
if (empty($btn_type)) $btn_type = 'default';
if (empty($btn_size)) $btn_size = '';

if (!isset($btn_group_attrs)) $btn_group_attrs = array();
$default_btn_group_attrs = array('class' => 'btn-group');
$btn_group_attrs = Util_toolkit::convert_to_attr($btn_group_attrs, $default_btn_group_attrs);

if (!isset($btn_attrs)) $btn_attrs = array();
$default_btn_attrs = array('data-toggle' => 'dropdown');
$btn_attrs = Util_toolkit::convert_to_attr($btn_attrs, $default_btn_attrs);
$menu_id = !empty($btn_attrs['data-menu']) ? str_replace('#', '', $btn_attrs['data-menu']) : '';

if (empty($btn_with_text)) $btn_with_text = false;
?>
<div <?php echo array_to_attr($btn_group_attrs); ?>>
	<?php echo btn($type, null, 'dropdown-toggle', $btn_with_text, $btn_size, $btn_type, $btn_attrs, null, 'button', null, null, $with_caret); ?>
	<ul class="dropdown-menu<?php if ($is_popover_align_right): ?> pull-right<?php endif; ?>" role="menu"<?php if ($menu_id): ?> id="<?php echo $menu_id; ?>"<?php endif; ?>>
	<?php echo render('_parts/dropdown_menu', array('menus' => $menus)); ?>
	</ul>
</div>
