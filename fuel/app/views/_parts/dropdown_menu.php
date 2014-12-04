<?php if ($menus): ?>
<?php foreach ($menus as $menu): ?>
<?php if (empty($menu['tag']) || $menu['tag'] == 'a' || $menu['tag'] == 'anchor'): ?>
<?php
$href  = isset($menu['href']) ? $menu['href'] : '#';
$attr = isset($menu['attr']) ? $menu['attr'] : array();
$label = isset($menu['label']) ? $menu['label'] : '';
?>
<?php 	if (isset($menu['icon_term'])): ?>
<?php 		if (empty($attr['data-msg']) && $msg = Site_Util::get_confirm_msg(str_replace('form.', '', $menu['icon_term']))) $attr['data-msg'] = $msg; ?>
		<li><?php echo anchor_icon($href, $menu['icon_term'], $attr); ?></li>
<?php 	else: ?>
		<li><?php echo anchor($href, $label, false, $attr); ?></li>
<?php 	endif; ?>
<?php elseif ($menu['tag'] == 'disabled'): ?>
		<li><span class="disabled"><?php echo isset($menu['icon_term']) ? icon_label($menu['icon_term'], 'both', false) : $label; ?></span></li>
<?php elseif ($menu['tag'] == 'divider'): ?>
		<li class="divider"></li>
<?php else: ?>
		<li><?php echo html_tag($menu['tag'], $menu['attr'], $menu['label']); ?></li>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
