<?php
$label_class = 'col-sm-'.$label_col_sm_size;
$text_col_sm_size = 12 - $label_col_sm_size;
if ($label_col_sm_size == 12)
{
	$text_col_sm_size = 12;
}
else
{
	$label_class .= ' control-label';
}
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
	<?php echo Form::label($label, '', array('class' => $label_class)); ?>
	<div class="form-text col-sm-<?php echo $text_col_sm_size; ?>">
		<?php echo $value; ?>
<?php if ($optional_link): ?>
		<?php echo Html::anchor($optional_link['uri'], !empty($optional_link_text) ? $optional_link_text : $optional_link['text'], $optional_link_attr); ?>
<?php endif; ?>
	</div>
</div>
