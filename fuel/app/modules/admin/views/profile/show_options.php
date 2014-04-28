<p><?php echo Html::anchor('admin/profile/edit_options/'.$profile->id, '<i class="ls-icon-edit"></i> 編集', array('class' => 'btn btn-default')); ?></p>
<table class="table" id="jqui-sortable">
<tr>
	<th class="small"><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th class="small"><?php echo term('form.delete'); ?></th>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th>項目名</th>
</tr>
<?php foreach ($profile_options as $profile_option): ?>
<?php echo render('_parts/table/simple_row_sortable', array(
	'id' => $profile_option->id,
	'name' => $profile_option->label,
	'delete_uri' => 'admin/profile/option/api/delete.json',
)); ?>
<?php endforeach; ?>
</table>

<?php echo render('_parts/form/simple_post', array(
	'input_name' => 'label',
	'btn_attr' => array('data-id' => $profile->id, 'data-uri' => 'admin/profile/option/api/create.html'),
)); ?>
