<?php if (isset($html_error)): ?>
<?php echo $html_error; ?>
<?php endif; ?>
<?php echo $html_form; ?>

<?php /*//echo '<pre>'; var_dump($form); echo '</pre>'; ?>

<?php echo Form::open(array()); ?>

	<?php if (isset($error)): ?>
		<div class="error"><?php echo $error; ?></div>
	<?php endif; ?>

	<div class="row">
		<label for="username"><?php echo Form::label('', 'username'); ?>:</label>
		<div class="input"><?php echo Form::input('username', Input::post('username')); ?></div>
		
	</div>

	<div class="row">
		<label for="email"><?php echo Form::label('email'); ?>:</label>
		<div class="input"><?php echo Form::input('email', Input::post('email')); ?></div>
		
	</div>

	<div class="row">
		<label for="password">Password:</label>
		<div class="input"><?php echo Form::password('password'); ?></div>
		
	</div>

	<div class="row">
		<label for="password_confirm">Password:</label>
		<div class="input"><?php echo Form::password('password_confirm'); ?></div>
		
	</div>

	<div class="actions">
		<?php echo Form::submit(array('value'=>'Login', 'name'=>'submit')); ?>
	</div>

<?php echo Form::close();*/ ?>
