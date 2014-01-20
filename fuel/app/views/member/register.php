<?php echo alert('パスワードを入力してください'); ?>
<div class="well">
<?php echo form_open(true); ?>
	<?php echo Form::hidden('token' ,$member_pre['token']); ?>
	<?php echo form_text($member_pre['name'], '名前', 3); ?>
	<?php echo form_text($member_pre['email'], 'メールアドレス', 3); ?>
	<?php echo form_input($val, 'password', '', 6, 3); ?>
	<?php echo form_button(null, 'submit', null, null, 3); ?>
<?php echo form_close(); ?>
</div><!-- well -->
