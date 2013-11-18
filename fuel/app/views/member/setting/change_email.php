<div class="alert alert-info">パスワードを入力してください</div>

<?php echo Form::open(array('action' => 'member/setting/change_email', 'class' => 'form-horizontal well')); ?>
	<?php echo Form::hidden('token' ,$member_email_pre['token']); ?>
	<?php echo Form::hidden(Config::get('security.csrf_token_key') ,Util_security::get_csrf()); ?>

	<div class="form-group">
		<label class="control-label col-sm-2">メールアドレス</label>
		<div class="col-sm-10"><?php echo $member_email_pre['email']; ?></div>
	</div>

	<div class="form-group">
		<label class="control-label col-sm-2">パスワード</label>
		<div class="col-sm-10">
		<?php echo Form::password('password', '', array('class' => 'span4')); ?>
		<?php if ($val->error('password')): ?>
		<span class="help-inline error_msg"><?php echo $val->error('password')->get_message(); ?></span>
		<?php endif; ?>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-10">
		<?php echo form_button('送信'); ?>
		</div>
	</div>
<?php echo Form::close(); ?>
