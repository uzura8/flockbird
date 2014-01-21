<?php
$offset = $offset_size ? 'col-sm-offset-'.$offset_size : '';
$col    = 'col-sm-'.(12 - $offset_size);
?>
<div class="form-group">
	<div class="<?php echo $col; ?><?php if ($offset): ?> <?php echo $offset; ?><?php endif; ?>">
		<?php if ($is_enclose_small_tag): ?><small><?php endif; ?><?php echo Html::anchor($href, $label, $atter, $secure); ?><?php if ($is_enclose_small_tag): ?></small><?php endif; ?>
	</div>
</div>
