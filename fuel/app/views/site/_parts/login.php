<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>

<?php if (!$is_api_request): ?><div class="well"><?php endif; ?>
<?php
$form_attributes = array('action' => 'site/login');
if (!$is_api_request) $form_attributes['class'] = 'form-horizontal';
$input_class = ($is_api_request) ? 'input-medium' : '';
?>
<?php echo Form::open($form_attributes); ?>

<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
<?php if (isset($destination)): ?><?php echo Form::hidden('destination' ,$destination); ?><?php endif; ?>

<div class="control-group">
	<?php echo Form::label('メールアドレス', 'email', array('class' => 'control-label')); ?>
	<div class="controls">
		<?php echo Form::input('email', Input::post('email'), array('type' => 'email', 'class' => $input_class, 'required' => 'required')); ?>
		<?php if ($val->error('email')): ?><span class="help-inline error_msg"><?php echo $val->error('email')->get_message(); ?></span><?php endif; ?>
	</div>
</div>

<div class="control-group">
	<?php echo Form::label('パスワード', 'password', array('class' => 'control-label')); ?>
	<div class="controls">
		<?php echo Form::password('password', '', array('class' => $input_class, 'required' => 'required')); ?>
		<?php if ($val->error('password')): ?><span class="help-inline error_msg"><?php echo $val->error('password')->get_message(); ?></span><?php endif; ?>
	</div>
</div>

<div class="control-group">
	<div class="controls">
		<?php echo Form::checkbox('rememberme[]', '1', in_array('1', Input::post('rememberme', array())) ? array('checked' => 'checked', 'id' => 'form_rememberme_1') : array('id' => 'form_rememberme_1')); ?><?php echo Form::label('次回から自動的にログイン', 'rememberme_1', array('class' => 'checkbox')); ?>
	</div>
</div>

<div class="control-group">
	<div class="controls">
	<small><?php echo Html::anchor('member/resend_password', 'パスワードを忘れた場合はこちら'); ?></small>
	</div>
</div>

<div class="control-group">
	<div class="controls"><input type="submit" value="ログイン" class="btn btn-primary" id="form_submit" name="submit" /></div>
</div>

<?php if (PRJ_FACEBOOK_APP_ID): ?>
<div class="control-group">
	<div class="controls facebook_login">
	<?php echo Html::anchor('facebook/login', 'facebookでログイン', array('class' => 'btn btn-primary')); ?>
	</div>
</div>
<?php endif; ?>

<div class="control-group">
	<div class="controls signup">
	<?php echo Html::anchor('member/signup', '新規登録', array('class' => 'btn btn-warning')); ?>
	</div>
</div>

<?php echo Form::close(); ?>

<?php if (!$is_api_request): ?></div><?php endif; ?>

<?php if ($is_api_request): ?></body></html><?php endif; ?>
