<?php echo form_open(false, false, array('class' => 'form-inline')); ?>
<table class="table" id="jqui-sortable">
<tr>
	<th class="small">ID</th>
	<th>項目名</th>
</tr>
<?php foreach ($profile_options as $profile_option): ?>
<tr<?php if (!strlen($vals[$profile_option->id])): ?> class="has-error"<?php endif; ?>>
	<td><?php echo $profile_option->id; ?></td>
	<td>
		<?php echo Form::input(sprintf('labels[%d]', $profile_option->id), $vals[$profile_option->id], array(
			'id' => 'input_labels_'.$profile_option->id,
			'class' => 'form-control input-xlarge'
		)); ?>
<?php if (!strlen($vals[$profile_option->id])): ?>
		<span class="error_msg">未入力です。</span>
<?php endif; ?>
	</td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Form::button('submit', '編集する', array('type' => 'submit', 'class' => 'btn btn-default', 'id' => 'btn_create', 'data-id' => $profile->id)); ?>
<?php echo Form::close(); ?>
