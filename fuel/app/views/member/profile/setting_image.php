<div class="well">
<div><?php echo img($u->get_image()), '180x180', '', true); ?></div>
<?php echo Form::open(array('action' => 'member/profile/edit_image', 'class' => 'form-stacked', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
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
<?php if (Auth::check() && $u->file_id): ?>
<div><a class="btn boxBtn" href="#" onclick="delete_item('member/profile/delete_image');return false;"><i class="icon-trash"></i> 削除</a></div>
<?php endif; ?>
</div>
