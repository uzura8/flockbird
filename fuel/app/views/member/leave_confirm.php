<div class="alert">本当に退会しますか？</div>

<div class="well">
<?php echo Form::open('member/leave'); ?>
<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
<div class="actions">
	<?php echo form_button('<i class="ls-icon-arrowleft"></i> 戻る', 'submit', 'submit_back', array('class' => 'btn btn-default')); ?>
</div>
<?php echo Form::close(); ?>

<?php echo Form::open('member/delete'); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
<div class="actions">
	<?php echo form_button('退会する', 'submit', 'submit', array('class' => 'btn btn-default btn-danger')); ?>
</div>
<?php echo Form::close(); ?>
</div>
