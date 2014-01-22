<?php $type = isset($type)? $type : 'info'; ?>
<div class="alert alert-<?php echo $type; ?>">
<?php if ($with_dismiss_btn): ?>
	<a class="close" data-dismiss="alert">x</a>
<?php endif; ?>
	<?php echo $message; ?>
</div>
