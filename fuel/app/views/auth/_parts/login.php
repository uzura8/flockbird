<?php if (empty($in_popover)): ?><div class="well"><?php endif; ?>
<?php
$form_attributes = array('action' => conf('login_uri.site'));
if (!empty($in_popover)) $form_attributes['class'] = '';
$col_sm_size       = empty($in_popover) ? 7 : 12;
$label_col_sm_size = empty($in_popover) ? 3 : 12;
$col_offset_size   = empty($in_popover) ? 3 : 0;
?>
<?php echo form_open(false, false, $form_attributes, array('destination' => $destination)); ?>
	<?php echo form_input($login_val, 'email', '', $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_input($login_val, 'password', '', $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_checkbox($login_val, 'rememberme', array(), $label_col_sm_size, 'block', '', array(), !empty($in_popover)); ?>
	<?php echo form_anchor('member/recover/resend_password', term('site.password').'を忘れた場合はこちら', array(), $col_offset_size, null, true); ?>
	<?php echo form_button('site.login', 'submit', null, null, $col_offset_size); ?>

<?php if (PRJ_FACEBOOK_APP_ID): ?>
	<?php echo form_anchor(
		conf('login_uri.site').'/facebook',
		icon_label('Facebookで'.term('site.login'), 'both', false, 'facebook-square', 'fa fa-'),
		array('class' => 'btn btn-default'),
		$col_offset_size
	); ?>
<?php endif; ?>
<?php if (PRJ_TWITTER_APP_ID): ?>
	<?php echo form_anchor(
		conf('login_uri.site').'/twitter',
		icon_label('Twitterで'.term('site.login'), 'both', false, 'twitter', 'fa fa-'),
		array('class' => 'btn btn-default'),
		$col_offset_size
	); ?>
<?php endif; ?>
<?php if (PRJ_GOOGLE_APP_ID): ?>
	<?php echo form_anchor(
		conf('login_uri.site').'/google',
		icon_label('Googleで'.term('site.login'), 'both', false, 'google', 'fa fa-'),
		array('class' => 'btn btn-default'),
		$col_offset_size
	); ?>
<?php endif; ?>
	<?php echo form_anchor('member/register/signup', icon_label('form.create'), array('class' => 'btn btn-default btn-warning'), $col_offset_size); ?>

<?php echo form_close(); ?>
<?php if (empty($in_popover)): ?></div><?php endif; ?>
