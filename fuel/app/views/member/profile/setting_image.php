<div class="well">
<div><?php echo site_profile_image($current_user->get_image(), '180x180', '', true); ?></div>
<?php echo Form::open(array('action' => 'member/profile/edit_image', 'class' => 'form-stacked', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); ?>
<div class="control-group">
	<div class="controls">
	<?php echo Form::input('image', '写真', array('type' => 'file', 'class' => 'input-file')); ?>
	</div>
</div>
<div class="control-group">
	<div class="controls">
	<?php echo Form::input('submit', '送信', array('type' => 'submit', 'class' => 'btn')); ?>
	</div>
</div>
<?php echo Form::close(); ?>
</div>
