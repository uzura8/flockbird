<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>

<?php if (!IS_API): ?><div class="well"><?php endif; ?>
<?php
$form_attributes = array('action' => Config::get('site.login_uri.site'));
if (IS_API) $form_attributes['class'] = '';
$col_sm_size = IS_API ? 12 : 7;
$label_col_sm_size = IS_API ? 12 : 3;
?>
<?php echo form_open(false, false, $form_attributes, array('destination' => $destination)); ?>
	<?php echo form_input($val, 'email', '', $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_input($val, 'password', '', $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_checkbox($val, 'rememberme', array(), IS_API ? 12 : $label_col_sm_size, 'block', '', array(), IS_API); ?>
	<?php echo form_anchor('member/resend_password', 'パスワードを忘れた場合はこちら', array(), IS_API ? 0 : 3, null, true); ?>
	<?php echo form_button('ログイン', 'submit', null, null, IS_API ? 0 : 3); ?>

<?php if (PRJ_FACEBOOK_APP_ID): ?>
	<?php echo form_anchor(
		Config::get('site.login_uri.site').'/facebook',
		'<i class="ls-icon-facebook"></i> facebookでログイン',
		array('class' => 'btn btn-default'),
		IS_API ? 0 : 3
	); ?>
<?php endif; ?>
<?php if (PRJ_TWITTER_APP_ID): ?>
	<?php echo form_anchor(
		Config::get('site.login_uri.site').'/twitter',
		'<i class="ls-icon-twitter"></i> Twitterでログイン',
		array('class' => 'btn btn-default'),
		IS_API ? 0 : 3
	); ?>
<?php endif; ?>
<?php if (PRJ_GOOGLE_APP_ID): ?>
	<?php echo form_anchor(
		Config::get('site.login_uri.site').'/google',
		'<i class="ls-icon-google"></i> Googleでログイン',
		array('class' => 'btn btn-default'),
		IS_API ? 0 : 3
	); ?>
<?php endif; ?>
	<?php echo form_anchor('member/register/signup', '新規登録', array('class' => 'btn btn-default btn-warning'), IS_API ? 0 : 3); ?>

<?php echo form_close(); ?>
<?php if (!IS_API): ?></div><?php endif; ?>
<?php if (IS_API): ?></body></html><?php endif; ?>
