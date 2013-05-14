<div class="alert">本当に退会しますか？</div>

<div class="well">
<?php echo Form::open('member/leave'); ?>
<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
<div class="actions">
	<?php echo Form::submit('submit1', '戻る', array('class' => 'btn')); ?>
</div>
<?php echo Form::close(); ?>

<?php echo Form::open('member/delete'); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
<div class="actions">
	<?php echo Form::submit('submit2', '確定', array('class' => 'btn btn-danger')); ?>
</div>
<?php echo Form::close(); ?>
</div>
