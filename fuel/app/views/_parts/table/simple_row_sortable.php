<tr class="jqui-sortable-item" id="<?php echo $id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
<?php if (!empty($edit_uri)): ?>
	<td><?php echo btn('form.edit', $edit_uri, 'edit', false, 'xs'); ?></td>
<?php endif; ?>
	<td><?php echo btn('form.delete', '#', 'js-ajax-delete', false, 'xs', null, array('data-id' => $id, 'data-uri' => $delete_uri)); ?></td>
	<td><?php echo $id; ?></td>
<?php if (!empty($name)): ?>
	<td><?php echo $name; ?></td>
<?php endif; ?>
<?php if (!empty($label)): ?>
	<td><?php echo $label; ?></td>
<?php endif; ?>
</tr>
