<?php
if (empty($btn_type)) $btn_type = 'default';
if (empty($btn_size)) $btn_size = 'sm';
if (!isset($attrs)) $attrs = array();
$default_attrs = array(
	'class' => array('btn', 'btn-'.$btn_type, 'btn-'.$btn_size, 'dropdown-toggle'),
	'type' => 'button',
	'data-toggle' => 'dropdown',
);
$attrs = Util_toolkit::convert_to_attr($attrs, $default_attrs);

$btn_name = !empty($attrs['id']) ? $attrs['id'] : '';
$btn_label .= ' <span class="caret"></span>';

if (!isset($btn_group_attrs)) $btn_group_attrs = array();
$default_btn_group_attrs = array('class' => 'btn-group');
$btn_group_attrs = Util_toolkit::convert_to_attr($btn_group_attrs, $default_btn_group_attrs);
?>
<div <?php echo array_to_attr($btn_group_attrs); ?>>
	<?php echo Form::button($btn_name, $btn_label, $attrs); ?>
	<ul class="dropdown-menu" role="menu">
<?php foreach ($menus as $menu): ?>
<?php if ($menu['tag'] == 'a' || $menu['tag'] == 'anchor'): ?>
		<li><?php echo Html::anchor(isset($menu['href']) ? $menu['href'] : '#', $menu['label'], $menu['attr']); ?></li>
<?php elseif ($menu['tag'] == 'divider'): ?>
		<li class="divider"></li>
<?php else: ?>
		<li><?php echo html_tag($menu['tag'], isset($menu['attr']) ? $menu['attr'] : array(), $menu['label']); ?></li>
<?php endif; ?>
<?php endforeach; ?>
	</ul>
</div>
