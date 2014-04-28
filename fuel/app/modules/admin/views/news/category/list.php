<table class="table table-hover table-responsive" id="jqui-sortable">
<tr>
	<th class="small"><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th class="small"><?php echo term('form.delete'); ?></th>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th><?php echo term('news.category.name'); ?></th>
</tr>
<?php foreach ($news_categories as $news_category): ?>
<?php echo render('_parts/table/simple_row_sortable', array(
	'id' => $news_category->id,
	'name' => $news_category->name,
	'delete_uri' => 'admin/news/category/api/delete.json',
)); ?>
<?php endforeach; ?>
</table>

<?php echo render('_parts/form/simple_post', array('btn_attr' => array('data-uri' => 'admin/news/category/api/create.html'))); ?>
