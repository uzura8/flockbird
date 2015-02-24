<?php
$input_col_sm_size = 12 - $label_col_sm_size;
if ($label)
{
	$label_class = 'control-label col-sm-'.$label_col_sm_size;
	$input_col_class = 'col-sm-'.$input_col_sm_size;
}
else
{
	$input_col_class = sprintf('col-sm-offset-%d col-sm-%d', $label_col_sm_size, $input_col_sm_size);
}

$btn_attrs_default = array(
	'class' => array('btn', 'btn-default', 'js-modal'),
	'type' => 'button',
	'data-uri' => $modal_get_uri,
	'data-target' => '#'.$modal_id_name,
);
if (!is_array($btn_attrs)) $btn_attrs = (array)$btn_attrs;
$btn_attr = Util_Array::conv_arrays2str(array_merge_recursive($btn_attrs_default, $btn_attrs));
?>
<div class="form-group">
<?php if ($label): ?>
	<?php echo Form::label($label, null, array('class' => $label_class)); ?>
<?php endif; ?>
	<div class="<?php echo $input_col_class; ?>">
		<?php echo Form::button('', $button_label, $btn_attr); ?>
	</div>
</div>
<?php
$data = array(
	'block_attrs' => array('id' => $modal_id_name),
	'is_display_header_close_btn' => true,
	'is_display_footer_close_btn' => true,
	'size' => 'lg',
);
if ($modal_title) $data['title'] = $modal_title;
echo render('_parts/modal', $data);
?>
