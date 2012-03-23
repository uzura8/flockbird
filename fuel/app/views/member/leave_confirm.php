<p>本当に退会しますか？</p>

<?php echo Form::open('member/leave'); ?>
<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
<div class="actions">
	<?php echo Form::submit('submit1', '戻る'); ?>
</div>
<?php echo Form::close(); ?>

<?php echo Form::open('member/delete'); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); ?>
<?php echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
<div class="actions">
	<?php echo Form::submit('submit2', '確定'); ?>
</div>
<?php echo Form::close(); ?>
