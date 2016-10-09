<?php if (empty($in_popover)): ?><div class="well"><?php endif; ?>
<?php
$form_attributes = array('action' => conf('login_uri.site'));
if (!empty($in_popover)) $form_attributes['class'] = '';
$col_sm_size       = empty($in_popover) ? 7 : 12;
$label_col_sm_size = empty($in_popover) ? 3 : 12;
$col_offset_size   = empty($in_popover) ? 3 : 0;
$is_display_form = (empty($in_popover) || (!empty($in_popover) && (!FBD_SSL_MODE || (FBD_SSL_MODE && IS_SSL))));
?>
<?php echo form_open(false, false, $form_attributes, array('destination' => $destination)); ?>
<?php if ($is_display_form): ?>
	<?php echo form_input($login_val, 'email', '', $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_input($login_val, 'password', '', $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_checkbox($login_val, 'rememberme', array(), $label_col_sm_size, 'block', '', array(), !empty($in_popover)); ?>
	<?php echo form_anchor('member/recover/resend_password', t('member.forget_password'), array(), $col_offset_size, null, true); ?>
<?php endif; ?>
<?php if ($is_display_form): ?>
	<?php echo form_button('site.login', 'submit', null, null, $col_offset_size); ?>
<?php else: ?>
	<?php echo form_anchor(
		conf('login_uri.site'),
		icon_label('site.login', 'both', false),
		array('class' => 'btn btn-default btn-primary'),
		$col_offset_size
	); ?>
<?php endif; ?>

<?php if (FBD_FACEBOOK_APP_ID): ?>
	<?php echo form_anchor(
		conf('login_uri.site').'/facebook',
		icon_label(t('member.login_with', array('service' => term('service.facebook.view'))), 'both', false, 'facebook-square', 'fa fa-'),
		array('class' => 'btn btn-default'),
		$col_offset_size
	); ?>
<?php endif; ?>
<?php if (FBD_TWITTER_APP_ID): ?>
	<?php echo form_anchor(
		conf('login_uri.site').'/twitter',
		icon_label(t('member.login_with', array('service' => term('service.twitter.view'))), 'both', false, 'twitter', 'fa fa-'),
		array('class' => 'btn btn-default'),
		$col_offset_size
	); ?>
<?php endif; ?>
<?php if (FBD_GOOGLE_APP_ID): ?>
	<?php echo form_anchor(
		conf('login_uri.site').'/google',
		icon_label(t('member.login_with', array('service' => term('service.google.view'))), 'both', false, 'google', 'fa fa-'),
		array('class' => 'btn btn-default'),
		$col_offset_size
	); ?>
<?php endif; ?>
<?php if (conf('member.register.signup.IsEnabled')): ?>
	<?php echo form_anchor('member/register/signup', icon_label('member.registration', 'both', false), array('class' => 'btn btn-default btn-warning'), $col_offset_size); ?>
<?php endif; ?>

<?php echo form_close(); ?>
<?php if (empty($in_popover)): ?></div><?php endif; ?>
