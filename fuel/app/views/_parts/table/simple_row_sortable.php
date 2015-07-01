<?php
if (!isset($is_display_id_col)) $is_display_id_col = true;

$delete_btn_attr = array('data-id' => $id, 'data-uri' => $delete_uri);
if (!empty($delete_confirm_msg)) $delete_btn_attr['data-msg'] = $delete_confirm_msg;
?>
<tr class="jqui-sortable-item" id="<?php echo $id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
<?php if (!empty($edit_uri)): ?>
	<td><?php echo btn('form.edit', $edit_uri, 'edit', false, 'xs'); ?></td>
<?php endif; ?>
	<td><?php echo btn('form.delete', '#', 'js-ajax-delete', false, 'xs', null, $delete_btn_attr); ?></td>
<?php if ($is_display_id_col): ?>
	<td><?php echo $id; ?></td>
<?php endif; ?>
<?php if (!empty($name)): ?>
	<td><?php echo $name; ?></td>
<?php endif; ?>
<?php if (!empty($label)): ?>
	<td><?php echo $label; ?></td>
<?php endif; ?>
</tr>
