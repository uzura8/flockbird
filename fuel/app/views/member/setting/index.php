<?php $label_col_size = 4; ?>
<div class="well form-horizontal">
	<?php echo form_text(
		!empty($u->member_auth->email) ? $u->member_auth->email : sprintf('<span class="text-danger">%s</span>', t('site.unset')),
		t('site.email'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/email', 'text' => icon('cog').' '.t('form.edit'), 'is_safe_text' => true)
	); ?>

<?php if (!$u->check_registered_oauth(true)): ?>
	<?php echo form_text(
		!empty($u->member_auth->password) ? sprintf('<span class="text-muted">%s</span>', t('site.set_already')) : sprintf('<span class="text-danger">%s</span>', t('site.unset')),
		t('site.password'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/password', 'text' => icon('cog').' '.t('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

<?php if (conf('address.isEnabled', 'member')): ?>
	<?php echo form_text(
		sprintf('<span class="text-muted">%s</span>', __('member_address_setting_description')),
		__('member_address_setting'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/address', 'text' => icon('cog').' '.t('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

<?php if (is_enabled('notice')): ?>
	<?php echo form_text(
		sprintf('<span class="text-muted">%s</span>', __('site_lead_notice_setting')),
		term('notice', 'site.setting'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/notice', 'text' => icon('cog').' '.t('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

<?php if (is_enabled('timeline')): ?>
	<?php echo form_text(
		\Timeline\Form_MemberConfig::get_viewType_options($member_config->timeline_viewType),
		term('timeline.view', 'site.display', 'site.setting'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/timeline/viewtype', 'text' => icon('cog').' '.t('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

<?php if (conf('memberRelation.accessBlock.isEnabled')): ?>
	<?php echo form_text(
		sprintf('<span class="text-muted">%s</span>', __('site_lead_access_block_settig')),
		__('site_title_access_block_settig'),
		$label_col_size,
		true,
		array('uri' => 'member/relation/list/access_block', 'text' => icon('cog').' '.t('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

<?php if (is_enabled_i18n()): ?>
	<?php echo form_text(
		Form_MemberConfig::get_lang_value_label($u->id),
		term('site.lang', 'site.setting'),
		$label_col_size,
		true,
		array('uri' => 'member/setting/lang', 'text' => icon('cog').' '.t('form.edit'), 'is_safe_text' => true)
	); ?>
<?php endif; ?>

</div>

<div class="list-group">
	<?php echo Html::anchor(
		$this->u->check_registered_oauth() ? 'member/leave/confirm' : 'member/leave',
		__('member_title_leave_service'),
		array('class' => 'list-group-item list-group-item-danger')
	); ?>
</div>
