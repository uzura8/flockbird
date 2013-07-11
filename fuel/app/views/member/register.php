<div class="alert alert-info">パスワードを入力してください</div>

<?php echo Form::open(array('action' => 'member/register', 'class' => 'form-horizontal well')); ?>
	<?php echo Form::hidden('token' ,$member_pre['token']); ?>
	<?php echo Form::hidden(Config::get('security.csrf_token_key') ,Util_security::get_csrf()); ?>

	<div class="control-group">
		<label class="control-label">名前</label>
		<div class="controls"><?php echo $member_pre['name']; ?></div>
	</div>
	<div class="control-group">
		<label class="control-label">メールアドレス</label>
		<div class="controls"><?php echo $member_pre['email']; ?></div>
	</div>

	<div class="control-group">
		<label class="control-label">パスワード</label>
		<div class="controls">
		<?php echo Form::password('password', '', array('class' => 'span4')); ?>
		<?php if ($val->error('password')): ?>
		<span class="help-inline error_msg"><?php echo $val->error('password')->get_message(); ?></span>
		<?php endif; ?>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
		<?php echo Form::submit(array('value'=>'送信', 'name'=>'submit', 'class' => 'btn')); ?>
		</div>
	</div>
<?php echo Form::close(); ?>
