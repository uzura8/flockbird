<?php echo alert(term('site.password').'を入力してください。'); ?>

<div class="well">
<?php echo form_open(true); ?>
<?php echo Form::hidden('token' ,$member_email_pre->token); ?>
<?php echo form_text($member_email_pre['email'], term('site.email'), 3); ?>
<?php echo form_input($val, 'password', '', 6, 3); ?>
<?php echo form_button(null, 'submit', null, null, 3); ?>
<?php echo form_close(); ?>
</div><!-- well -->
