<div class="alert alert-info">パスワードを入力してください</div>

<?php echo Form::open(array('action' => 'member/setting/change_email', 'class' => 'form-horizontal well')); ?>
	<?php echo Form::hidden('token' ,$member_email_pre['token']); ?>
	<?php echo Form::hidden(Config::get('security.csrf_token_key') ,Security::fetch_token()); ?>

	<div class="control-group">
		<label class="control-label">メールアドレス</label>
		<div class="controls"><?php echo $member_email_pre['email']; ?></div>
	</div>

	<div class="control-group">
		<label class="control-label">パスワード</label>
		<div class="controls">
		<?php echo Form::password('password', '', array('class' => 'span4')); ?>
		<?php if ($val->error('password')): ?>
		<span class="help-inline"><?php echo $val->error('password')->get_message(':label を入力してください'); ?></span>
		<?php endif; ?>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
		<?php echo Form::submit(array('value'=>'送信', 'name'=>'submit', 'class' => 'btn')); ?>
		</div>
	</div>
<?php echo Form::close(); ?>
