<?php $label_col_size = 4; ?>
<div class="well form-horizontal">
	<?php echo form_text(
		!empty($u->member_auth->email) ? $u->member_auth->email : sprintf('<span class="text-danger">%s</span>', term('site.unset')),
		term('site.email'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/email', 'text' => icon('cog').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>

<?php if (!$u->check_registered_oauth(true)): ?>
	<?php echo form_text(
		!empty($u->member_auth->password) ? sprintf('<span class="text-muted">%s</span>', term('site.set_already')) : sprintf('<span class="text-danger">%s</span>', term('site.unset')),
		term('site.password'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/password', 'text' => icon('cog').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

<?php if (is_enabled('notice')): ?>
	<?php echo form_text(
		sprintf('<span class="text-muted">%sを%sを%sします</span>', term('notice'), term('form.recieve', 'site.item'), term('site.setting')),
		term('notice', 'site.setting'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/notice', 'text' => icon('cog').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

<?php if (is_enabled('timeline')): ?>
	<?php echo form_text(
		\Timeline\Form_MemberConfig::get_viewType_options($member_config->timeline_viewType),
		term('timeline', 'site.display', 'site.setting'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/timeline/viewtype', 'text' => icon('cog').' '.term('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

</div>

<div class="list-group">
	<?php echo Html::anchor(
		$this->u->check_registered_oauth() ? 'member/leave/confirm' : 'member/leave',
		term('site.leave'),
		array('class' => 'list-group-item list-group-item-danger')
	); ?>
</div>
