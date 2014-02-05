<p><?php echo Html::anchor('admin/profile/create', '<i class="ls-icon-edit"></i> 新規作成', array('class' => 'btn btn-default')); ?></p>
<?php if ($profiles): ?>
<table class="table">
<tr>
	<th colspan="3">操作</th>
	<th>ID</th>
<?php foreach ($labels as $label): ?>
	<th class="font-size-small"><?php echo $label; ?></th>
<?php endforeach; ?>
</tr>
<?php foreach ($profiles as $profile): ?>
<tr>
	<td><span class="glyphicon glyphicon-sort"></span></td>
	<td><?php echo btn('edit', 'admin/profile/edit/'.$profile->id, '', false, 'xs'); ?></td>
<?php /*
function btn($type, $href = '#', $class_name = '', $with_text = false, $size = '', $btn_type = 'default', $attr = array(), $exception_label = '')
*/ ?>
	<td><?php echo btn('delete', '#', 'btn_profile_delete', false, 'xs', 'default', array('data-id' => $profile->id)); ?></td>
	<td><?php echo $profile->id; ?></td>
	<td><?php echo $profile->caption; ?></td>
	<td><?php echo $profile->name; ?></td>
	<td><?php echo $profile->is_required ? '◯' : '×'; ?></td>
	<td><?php echo $profile->is_edit_public_flag ? '◯' : '×'; ?></td>
	<td><?php echo \Site_Form::get_public_flag_options($profile->default_public_flag); ?></td>
	<td><?php echo $profile->is_unique ? '×' : '◯'; ?></td>
	<td><?php echo \Site_Profile::get_form_type_options($profile->form_type); ?></td>
	<td><?php echo $profile->is_disp_regist ? '◯' : '×'; ?></td>
	<td><?php echo $profile->is_disp_config ? '◯' : '×'; ?></td>
	<td><?php echo $profile->is_disp_search ? '◯' : '×'; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<?php echo term('profile'); ?>項目がありません。
<?php endif; ?>
