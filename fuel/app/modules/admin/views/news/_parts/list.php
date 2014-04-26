<?php if (!$list): ?>
<?php echo term('news.view'); ?>がありません。
<?php else: ?>
<div class="list-group">
<?php foreach ($list as $id => $news): ?>
	<a href="<?php echo Uri::create('admin/news/detail'.$id); ?>" class="list-group-item" id="list-group-item_<?php echo $id; ?>">
		<h4 class="list-group-item-heading">
			<?php echo strim($news->title, term('view_params_default.list.trim_width.title')); ?>
<?php if (!$news->is_published): ?>
			<?php echo label(term('draft')); ?>
<?php endif; ?>
		</h4>
		<p class="list-group-item-text">
			<?php echo truncate_lines($news->body, Config::get('site.view_params_default.list.truncate_lines.body'), 'news/'.$id); ?>
		</p>
	</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
