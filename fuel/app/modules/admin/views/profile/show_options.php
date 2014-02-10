<p><?php echo Html::anchor('admin/profile/edit_options/'.$profile->id, '<i class="ls-icon-edit"></i> 編集', array('class' => 'btn btn-default')); ?></p>
<table class="table" id="jqui-sortable">
<tr>
	<th class="small"><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th class="small">削除</th>
	<th class="small">ID</th>
	<th>項目名</th>
</tr>
<?php foreach ($profile_options as $profile_option): ?>
<?php echo render('profile/option/_parts/table_row', array('profile_option' => $profile_option)); ?>
<?php endforeach; ?>
</table>

<div class="well">
<?php echo Form::open(array('class' => 'form-inline')); ?>
<div class="form-group">
	<?php echo Form::input('label', '', array('id' => 'input_label', 'class' => 'form-control input-xlarge')); ?>
</div>
<div class="form-group">
	<?php echo Form::button('button', '追加する', array('class' => 'btn btn-default', 'id' => 'btn_create', 'data-id' => $profile->id)); ?>
</div>
<?php echo Form::close(); ?>
</div><!-- well -->
