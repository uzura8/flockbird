<?php echo alert(sprintf('本当に%sしますか？', term('site.left')), 'danger'); ?>
<div class="well">
<?php echo form_open(false, false, array('action' => 'member/leave/delete')); ?>
	<?php echo form_button('site.leave', 'submit', 'submit', array('class' => 'btn btn-default btn-danger')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
<?php if ($u->check_registered_oauth(true)): ?>
	<?php echo form_button('form.back', 'button', 'submit_back', array(
		'class' => 'btn btn-default js-simpleLink',
		'data-uri' => 'member/setting',
	)); ?>
<?php else: ?>
	<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
<?php endif; ?>
<?php echo form_close(); ?>

<?php if (!$u->check_registered_oauth(true)): ?>
<?php echo form_open(false, false, array('action' => 'member/leave')); ?>
	<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
	<?php echo form_button('form.back', 'submit', 'submit_back', array('class' => 'btn btn-default')); ?>
<?php echo form_close(); ?>
<?php endif; ?>
</div>
