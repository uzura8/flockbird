<p><?php echo Html::anchor('admin/profile/create', '<i class="ls-icon-edit"></i> 新規作成', array('class' => 'btn btn-default')); ?></p>
<?php if ($profiles): ?>
<table class="table" id="jqui-sortable">
<tr>
	<th><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th colspan="2">操作</th>
	<th>ID</th>
<?php foreach ($labels as $label): ?>
	<th class="font-size-small"><?php echo $label; ?></th>
<?php endforeach; ?>
</tr>
<?php foreach ($profiles as $profile): ?>
<tr class="jqui-sortable-item" id="<?php echo $profile->id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
	<td><?php echo btn('edit', 'admin/profile/edit/'.$profile->id, '', false, 'xs'); ?></td>
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
