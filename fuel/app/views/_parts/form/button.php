<?php
$offset = 'col-sm-offset-'.$offset_size;
$col    = 'col-sm-'.(12 - $offset_size);
?>
<div class="form-group">
	<div class="<?php echo $col.' '.$offset; ?>">
	<?php echo Form::button($name, $label, $atter); ?>
	</div>
</div>
