<?php
if (!empty($label))
{
	$label_class = 'col-sm-'.$offset_size;
	$label_class .= ' control-label';
	$offset = '';
}
else
{
	$offset = $offset_size ? 'col-sm-offset-'.$offset_size : '';
}
$col = 'col-sm-'.(12 - $offset_size);
?>
<div class="form-group">
<?php if (!empty($label)): ?>
	<?php echo Form::label($label, null, array('class' => $label_class)); ?>
<?php endif; ?>
	<div class="<?php echo $col; ?><?php if ($offset): ?> <?php echo $offset; ?><?php endif; ?>">
		<?php if ($is_enclose_small_tag): ?><small><?php endif; ?><?php echo Html::anchor($href, $anchor_label, $atter, $secure); ?><?php if ($is_enclose_small_tag): ?></small><?php endif; ?>
	</div>
</div>
