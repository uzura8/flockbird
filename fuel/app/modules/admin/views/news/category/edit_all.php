<?php echo form_open(false, false, array('class' => 'form-inline')); ?>
<table class="table">
<tr>
	<th class="small">ID</th>
	<th><?php echo term('news.category.name'); ?></th>
</tr>
<?php foreach ($news_categories as $news_category): ?>
<tr<?php if (!strlen($vals[$news_category->id])): ?> class="has-error"<?php endif; ?>>
	<td><?php echo $news_category->id; ?></td>
	<td>
		<?php echo Form::input(sprintf('names[%d]', $news_category->id), $vals[$news_category->id], array(
			'id' => 'input_names_'.$news_category->id,
			'class' => 'form-control input-xlarge'
		)); ?>
<?php if (!strlen($vals[$news_category->id])): ?>
		<span class="error_msg">未入力です。</span>
<?php endif; ?>
	</td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Form::button('submit', term('form.do_edit'), array('type' => 'submit', 'class' => 'btn btn-default', 'id' => 'btn_create')); ?>
<?php echo Form::close(); ?>
