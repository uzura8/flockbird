<?php
$input_id = Site_Form::get_field_id($name);
if (empty($type)) $type = 'image';
if (isset($type) && $type == 'img') $type = 'image';
if ($type == 'image' && empty($accept_type)) $accept_type = 'image/*';

$input_attr = array('id' => $input_id, 'class' => 'js-file_input hidden', 'data-type' => $type, 'data-input' => '#dummy_input_'.$input_id);
if (!empty($input_attr_additional)) $input_attr += (array)$input_attr_additional;
if (!empty($accept_type)) $input_attr['accept'] = $accept_type;
?>
<?php echo Form::file($name, $input_attr); ?>
<div class="pull-left form-inline">
		<div class="input-group">
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" onclick="$('#<?php echo $input_id; ?>').click();">
					<i class="glyphicon glyphicon-<?php if ($type == 'image'): ?>camera<?php else: ?>folder-open<?php endif; ?>"></i>
				</button>
			</span>
			<input id="dummy_input_<?php echo $input_id; ?>" type="text" class="form-control" placeholder="Select file..." disabled>
		</div>
<?php if (FBD_UPLOAD_MAX_FILESIZE): ?>
		<span class="text-muted form-control-static "><?php echo Num::format_bytes(FBD_UPLOAD_MAX_FILESIZE); ?> まで</span>
<?php endif; ?>
</div>
