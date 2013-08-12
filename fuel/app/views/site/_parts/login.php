<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>

<?php if (!$is_api_request): ?><div class="well"><?php endif; ?>
<?php
$form_attributes = array('action' => 'site/login');
if ($is_api_request) $form_attributes['class'] = '';
$input_class = ($is_api_request) ? 'input-medium' : '';
?>
<?php echo form_open(false, false, $form_attributes, array('destination' => $destination)); ?>
	<?php echo form_input($val, 'email', 'メールアドレス', '', true, $input_class, 'email'); ?>
	<?php echo form_input($val, 'password', 'パスワード', '', true, $input_class, 'password'); ?>

	<div class="control-group">
		<div class="controls">
			<?php echo Form::checkbox(
				'rememberme[]',
				'1',
				in_array('1', Input::post('rememberme', array())) ? array('checked' => 'checked', 'id' => 'form_rememberme_1') : array('id' => 'form_rememberme_1')
			); ?>
			<?php echo Form::label('次回から自動的にログイン', 'rememberme_1', array('class' => 'checkbox')); ?>
		</div>
	</div>

	<?php echo form_anchor('member/resend_password', 'パスワードを忘れた場合はこちら', array(), null, true); ?>
	<?php echo form_button('ログイン'); ?>

<?php if (PRJ_FACEBOOK_APP_ID): ?>
	<?php echo form_anchor('facebook/login', 'facebookでログイン', array('class' => 'btn btn-primary')); ?>
<?php endif; ?>
	<?php echo form_anchor('member/signup', '新規登録', array('class' => 'btn btn-warning')); ?>

<?php echo form_close(); ?>
<?php if (!$is_api_request): ?></div><?php endif; ?>
<?php if ($is_api_request): ?></body></html><?php endif; ?>
