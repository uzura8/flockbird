<?php $label_col_size = 4; ?>
<div class="well form-horizontal">
	<?php echo form_text(
		$u->member_auth->email ?: sprintf('<span class="text-danger">%s</span>', term('unset')),
		term('site.email'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/email', 'text' => icon('edit').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>

	<?php echo form_text(
		$u->member_auth->password ? sprintf('<span class="text-muted">%s</span>', term('site.set_already')) : sprintf('<span class="text-danger">%s</span>', term('unset')),
		term('site.password'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/password', 'text' => icon('edit').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>

<?php if (is_enabled('timeline')): ?>
	<?php echo form_text(
		\Timeline\Form_MemberConfig::get_viewType_options($u->timeline_viewType),
		term('timeline', 'site.display', 'site.setting'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/timeline/viewtype', 'text' => icon('edit').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

</div>

<div class="list-group">
	<?php echo Html::anchor('member/leave', term('site.leave'), array('class' => 'list-group-item list-group-item-danger')); ?>
</div>
