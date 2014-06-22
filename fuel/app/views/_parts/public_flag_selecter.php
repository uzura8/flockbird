<?php
list($name, $icon, $btn_color) = get_public_flag_label($public_flag, isset($view_icon_only) ? $view_icon_only : false, 'array', true);
if (!empty($model)) $model_uri = str_replace('_', '/', $model);
?>
<?php if (!empty($disabled_to_update)): ?>
<?php
$atter = array('class' => 'btn btn-default btn-xs public_flag '.$btn_color);
if (!empty($disabled_to_update['message']))
{
	$atter['data-toggle']    = 'tooltip';
	$atter['data-placement'] = 'top';
	$atter['title']          = $disabled_to_update['message'];
	$atter['onclick']        = 'return false;';
}
?>
<?php echo Html::anchor('#', $icon.$name, $atter); ?>
<?php elseif (!empty($is_mycontents)): ?>
<?php if (empty($without_parent_box)): ?><div class="public_flag<?php if (!empty($parent_box_additional_class)): ?> <?php echo $parent_box_additional_class; ?><?php endif; ?> btn-group dropdown-toggle"><?php endif; ?>
	<button class="btn dropdown-toggle btn-default btn-xs <?php echo $btn_color; ?>" type="button" id="<?php if (!empty($model) && !empty($id)): ?>public_flag_<?php echo $model; ?>_<?php echo $id; ?><?php else: ?>public_flag_selector<?php endif; ?>" data-toggle="dropdown" data-public_flag="<?php echo $public_flag; ?>">
		<?php echo $icon.$name; ?> <span class="caret"></span>
	</button>
	<ul class="dropdown-menu" role="menu">
<?php $public_flags = \Site_Util::get_public_flags(); ?>
<?php foreach ($public_flags as $public_flag_value): ?>
<?php $label = get_public_flag_label($public_flag_value, false, 'icon_term'); ?>
<?php if ($public_flag == $public_flag_value): ?>
		<li><span class="disabled"><?php echo $label; ?></span></li>
<?php else: ?>
<?php
$data_atters = array('public_flag' => $public_flag_value);
if (!empty($id)) $data_atters['id'] = $id;
if (!empty($view_icon_only)) $data_atters['icon_only'] = 1;
if (!empty($have_children_public_flag)) $data_atters['have_children_public_flag'] = 1;
if (!empty($is_refresh_after_update_public_flag)) $data_atters['is_refresh'] = 1;
if (!empty($post_uri)) $data_atters['post_uri'] = $post_uri;
if (!empty($model)) $data_atters['model'] = $model;
if (!empty($model_uri)) $data_atters['model_uri'] = $model_uri;
if (!empty($is_use_in_form))
{
	$class_atter = 'select_public_flag';
	$data_atters['is_no_msg'] = 1;
}
else
{
	$class_atter = 'update_public_flag';
	$data_atters['public_flag_original'] = $public_flag;
	if (!empty($child_model)) $data_atters['child_model'] = $child_model;
}
$atter = conv_data_atter($data_atters);
$atter['class'] = $class_atter;
?>
		<li><?php echo Html::anchor('#', $label, $atter); ?></li>
<?php endif; ?>
<?php endforeach; ?>
	</ul>
<?php if (empty($without_parent_box)): ?></div><?php endif; ?>
<?php else: ?>
<span class="btn btn-default btn-xs public_flag <?php echo $btn_color; ?>"><?php echo $icon.$name; ?></span>
<?php endif; ?>
