<?php echo Form::open(array('class' => 'form-horizontal well')); ?>

	<?php if (isset($destination)): ?>
		<?php echo Form::hidden('destination' ,$destination); ?>
	<?php endif; ?>

	<div class="control-group">
		<label class="control-label">メールアドレス</label>
		<div class="controls">
		<?php echo Form::input('email', Input::post('email'), array('class' => 'span4')); ?>
		<?php if ($val->error('email')): ?>
		<span class="help-inline"><?php echo $val->error('email')->get_message(':label を入力してください'); ?></span>
		<?php endif; ?>
		</div>
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
		<?php echo Form::submit(array('value'=>'ログイン', 'name'=>'submit', 'class' => 'btn')); ?>
		</div>
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
