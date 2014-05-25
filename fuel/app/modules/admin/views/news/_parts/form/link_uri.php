<?php foreach ($links as $id => $values): ?>
<?php
$is_saved = empty($is_saved) ? false : true;
$name_link_uri = sprintf('link_uri%s[%d]', $is_saved ? '_saved' : '',$id);
$name_link_label = sprintf('link_label%s[%d]', $is_saved ? '_saved' : '', $id);

$row_id = sprintf('link_row%s_%d', $is_saved ? '_saved' : '', $id);
?>
<div class="row mb10 link_row" id="<?php echo $row_id; ?>">
	<div class="col-sm-6<?php if ($val->error($name_link_uri)): ?> has-error<?php endif; ?>">
		<?php echo Form::label('URL', null, array('for' => $name_link_uri, 'class' => 'sr-only')); ?>
		<?php echo Form::input($name_link_uri, $values['uri'], array('type' => 'text', 'class' => 'form-control', 'placeholder' => 'http://example.com')); ?>
	</div>
	<div class="col-sm-4<?php if ($val->error($name_link_label)): ?> has-error<?php endif; ?>">
		<?php echo Form::label('リンク表示', null, array('for' => $name_link_label, 'class' => 'sr-only')); ?>
		<?php echo Form::input($name_link_label, isset($values['label']) ? $values['label'] : '', array('type' => 'text', 'class' => 'form-control', 'placeholder' => 'リンク表示')); ?>
	</div>
	<div class="col-sm-2">
		<button type="button" class="btn btn-danger btn-sm btn_delete_link_row" data-id="<?php echo $id; ?>" data-is_saved="<?php echo $is_saved ? 1 : 0; ?>"><i class="glyphicon glyphicon-trash"></i></button>
	</div>
</div>
<?php endforeach; ?>
