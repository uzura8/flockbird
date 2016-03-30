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


if ($optional_link)
{
	$link_attr_default = array(
		'class' => array('btn', 'btn-default', 'btn-sm', 'pull-right'),
	);
	if (!isset($optional_link['attr'])) $optional_link['attr'] = array();
	$link_attr = array_merge_recursive($link_attr_default, $optional_link['attr']);
	$optional_link_attr = Util_Array::conv_arrays2str($link_attr);
}
?>
<div class="form-group">
<?php if (!empty($label)): ?>
	<?php echo Form::label($label, null, array('class' => $label_class)); ?>
<?php endif; ?>
	<div class="form-text wrap <?php echo $col; ?><?php if ($offset): ?> <?php echo $offset; ?><?php endif; ?>">
		<?php echo !empty($is_nl2br) ? nl2br($value) : $value; ?>
<?php if ($optional_link): ?>
		<?php echo Html::anchor($optional_link['uri'], !empty($optional_link_text) ? $optional_link_text : $optional_link['text'], $optional_link_attr); ?>
<?php endif; ?>
	</div>
</div>


