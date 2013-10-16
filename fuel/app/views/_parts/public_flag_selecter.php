<?php
list($name, $icon, $btn_color) = get_public_flag_label($public_flag, isset($view_icon_only) ? $view_icon_only : false);
if (!empty($model)) $model_uri = str_replace('_', '/', $model);
?>
<?php if (isset($disabled_to_update) && $disabled_to_update): ?>
<a class="btn btn-mini public_flag<?php echo $btn_color; ?>"<?php if (!empty($disabled_to_update['message'])): ?> data-toggle="tooltip" data-placement="top" title="<?php echo $disabled_to_update['message']; ?>"<?php endif; ?>><?php echo $icon.$name; ?></a>
<?php elseif (!empty($is_mycontents)): ?>
<?php if (empty($without_parent_box)): ?><div class="btn-group public_flag<?php if (!empty($parent_box_additional_class)): ?> <?php echo $parent_box_additional_class; ?><?php endif; ?>"><?php endif; ?>
	<button class="btn dropdown-toggle btn-mini<?php echo $btn_color; ?>" id="<?php if (!empty($model) && !empty($id)): ?>public_flag_<?php echo $model; ?>_<?php echo $id; ?><?php else: ?>public_flag_selector<?php endif; ?>" data-toggle="dropdown">
		<?php echo $icon.$name; ?><span class="caret"></span>
	</button>
	<ul class="dropdown-menu pull-right">
<?php $public_flags = \Site_Util::get_public_flags(); ?>
<?php foreach ($public_flags as $public_flag_value): ?>
<?php $label = get_public_flag_label($public_flag_value, false, true); ?>
<?php if ($public_flag == $public_flag_value): ?>
		<li><span class="disabled"><?php echo $label; ?></span></li>
<?php else: ?>
<?php if (!empty($is_use_in_form) && $is_use_in_form): ?>
		<li><a href="#" class="select_public_flag" data-public_flag="<?php echo $public_flag_value; ?>" <?php if (!empty($view_icon_only)): ?> data-icon_only="1"<?php endif; ?><?php if (!empty($have_children_public_flag)): ?> data-have_children_public_flag="1"<?php endif; ?><?php if (!empty($is_refresh_after_update_public_flag)): ?> data-is_refresh="1"<?php endif; ?>><?php echo $label; ?></a></li>
<?php else: ?>
		<li><a href="#" class="update_public_flag" data-id="<?php echo $id; ?>" data-public_flag="<?php echo $public_flag_value; ?>" data-public_flag_original="<?php echo $public_flag; ?>" data-model="<?php echo $model; ?>" data-model_uri="<?php echo $model_uri; ?>"<?php if (!empty($view_icon_only)): ?> data-icon_only="1"<?php endif; ?><?php if (!empty($have_children_public_flag)): ?> data-have_children_public_flag="1"<?php endif; ?><?php if (!empty($is_refresh_after_update_public_flag)): ?> data-is_refresh="1"<?php endif; ?><?php if (!empty($child_model)): ?> data-child_model="<?php echo $child_model; ?>"<?php endif; ?>><?php echo $label; ?></a></li>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
	</ul>
<?php if (empty($without_parent_box)): ?></div><?php endif; ?>
<?php else: ?>
<span class="btn btn-mini public_flag<?php echo $btn_color; ?>"><?php echo $icon.$name; ?></span>
<?php endif; ?>
