<tr class="jqui-sortable-item" id="<?php echo $id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
	<td><?php echo btn('delete', '#', 'btn_delete', false, 'xs', null, array('data-id' => $id, 'data-uri' => $delete_uri)); ?></td>
	<td><?php echo $id; ?></td>
	<td><?php echo $name; ?></td>
</tr>
