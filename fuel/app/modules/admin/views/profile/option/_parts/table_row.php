<tr class="jqui-sortable-item" id="<?php echo $profile_option->id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
	<td><?php echo btn('delete', '#', 'btn_profile_delete', false, 'xs', null, array('data-id' => $profile_option->id, 'data-uri' => '')); ?></td>
	<td><?php echo $profile_option->id; ?></td>
	<td><?php echo $profile_option->label; ?></td>
</tr>
