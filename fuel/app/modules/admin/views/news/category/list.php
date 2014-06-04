<table class="table table-hover table-responsive" id="jqui-sortable">
<tr>
	<th class="small"><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th class="small"><?php echo term('form.delete'); ?></th>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th><?php echo term('news.category.name'); ?></th>
	<th><?php echo term('news.category.label'); ?></th>
</tr>
<?php foreach ($news_categories as $news_category): ?>
<?php echo render('_parts/table/simple_row_sortable', array(
	'id' => $news_category->id,
	'name' => $news_category->name,
	'label' => $news_category->label,
	'delete_uri' => sprintf('admin/news/category/api/delete/%d.json', $news_category->id),
)); ?>
<?php endforeach; ?>
</table>

<?php echo render('_parts/form/simple_post', array(
	'input_label' => term('news.category.name'),
	'additional_input_name' => 'label',
	'additional_input_label' => term('news.category.label'),
	'btn_attr' => array('data-uri' => 'admin/news/category/api/create.html')
)); ?>
