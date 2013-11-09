<?php $type = isset($type)? $type : 'info'; ?>
<div class="alert alert-<?php echo $type; ?>">
	<a class="close" data-dismiss="alert">x</a>
	<?php echo $message; ?>
</div>
