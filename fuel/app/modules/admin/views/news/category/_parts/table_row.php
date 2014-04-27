<tr class="jqui-sortable-item" id="<?php echo $news_category->id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
	<td><?php echo btn('delete', '#', 'btn_delete', false, 'xs', null, array('data-id' => $news_category->id, 'data-uri' => 'admin/news/category/api/delete')); ?></td>
	<td><?php echo $news_category->id; ?></td>
	<td><?php echo $news_category->name; ?></td>
</tr>
