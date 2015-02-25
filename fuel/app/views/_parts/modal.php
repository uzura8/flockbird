<?php
$block_attrs_default = array(
	'class' => array('modal', 'fade'),
	'tabindex' => '-1',
	'role' => 'dialog',
	//'aria-labelledby' => '',
	'aria-hidden' => 'true',
);
$block_attrs = Util_Array::conv_arrays2str(array_merge_recursive($block_attrs_default, isset($block_attrs) ? $block_attrs : array()));
if (!isset($is_display_footer_close_btn)) $is_display_footer_close_btn = false;
?>
<!-- Modal -->
<div <?php echo Util_Array::conv_array2attr_string($block_attrs); ?>>
	<div class="modal-dialog<?php if (!empty($size)): ?> modal-<?php echo $size; ?><?php endif; ?>">
		<div class="modal-content">
<?php if (!empty($is_display_header_close_btn) || !empty($title)): ?>
			<div class="modal-header">
<?php if (!empty($is_display_header_close_btn)): ?>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<?php endif; ?>
<?php if (!empty($title)): ?>
				<h4 class="modal-title"><?php echo $title; ?></h4>
<?php endif; ?>
			</div>
<?php endif; ?>
			<div class="modal-body"><?php if (!empty($body)): ?><?php echo $body; ?><?php endif; ?></div>
			<div class="modal-footer">
<?php if ($is_display_footer_close_btn): ?>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<?php endif; ?>
<?php if (!empty($footer_btn_params)): ?>
				<?php echo call_user_func_array('btn', $footer_btn_params); ?>
<?php endif; ?>
			</div>
		</div>
	</div>
</div>
