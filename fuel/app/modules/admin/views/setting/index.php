<?php $label_col_size = 3; ?>
<div class="well form-horizontal">
	<?php echo form_text(
		$u->email ?: sprintf('<span class="text-danger">%s</span>', term('unset')),
		term('site.email'),
		$label_col_size,
		true,
		array('uri' => 'admin/setting/email', 'text' => icon('edit').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>

	<?php echo form_text(
		$u->password ? sprintf('<span class="text-muted">%s</span>', term('site.set_already')) : sprintf('<span class="text-danger">%s</span>', term('unset')),
		term('site.password'),
		$label_col_size,
		true,
		array('uri' => 'admin/setting/password', 'text' => icon('edit').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>

</div>
