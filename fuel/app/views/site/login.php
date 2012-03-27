<?php echo Form::open(array()); ?>

	<?php if (isset($destination)): ?>
		<?php echo Form::hidden('destination' ,$destination); ?>
	<?php endif; ?>

	<div class="row">
		<label for="email">メールアドレス:</label>
		<div class="input"><?php echo Form::input('email', Input::post('email')); ?></div>
		
		<?php if ($val->errors('email')): ?>
			<div class="error"><?php echo $val->errors('email')->get_message(':label を入力してください'); ?></div>
		<?php endif; ?>
	</div>

	<div class="row">
		<label for="password">パスワード:</label>
		<div class="input"><?php echo Form::password('password'); ?></div>
		
		<?php if ($val->errors('password')): ?>
			<div class="error"><?php echo $val->errors('password')->get_message(':label を入力してください'); ?></div>
		<?php endif; ?>
	</div>

	<div class="actions">
		<?php echo Form::submit(array('value'=>'ログイン', 'name'=>'submit')); ?>
	</div>

<?php if (PRJ_FACEBOOK_APP_ID): ?>
	<div class="facebook_login">
		<?php echo Html::anchor('facebook/login', 'facebookでログイン'); ?>
	</div>
<?php endif; ?>

	<div class="signup">
		<?php echo Html::anchor('member/signup', '新規登録'); ?>
	</div>

<?php echo Form::close(); ?>
