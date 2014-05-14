<?php echo form_open(false, false, array('class' => 'form-inline')); ?>
<table class="table">
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
<p><?php echo btn('form.do_edit', null, null, true, null, 'primary', array('id' => 'btn_create', 'data-id' => $profile->id), null, 'button', 'submit', false); ?></p>
<?php echo Form::close(); ?>
