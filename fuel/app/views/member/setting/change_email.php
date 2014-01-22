<?php echo alert('パスワードを入力してください。'); ?>

<?php echo Form::open(array('action' => 'member/setting/change_email', 'class' => 'form-horizontal well')); ?>
<?php echo Form::hidden('token' ,$member_email_pre['token']); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key') ,Util_security::get_csrf()); ?>
<?php echo form_text($member_email_pre['email'], 'メールアドレス', 3); ?>
<?php echo form_input($val, 'password', '', 6, 3); ?>
<?php echo form_button(null, 'submit', null, null, 3); ?>
<?php echo Form::close(); ?>
