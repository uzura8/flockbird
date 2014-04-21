<?php //echo render('news/_parts/list', array('list' => $list, 'page' => $page, 'is_next' => $is_next, 'member' => $member, 'is_draft' => $is_draft)); ?>
<?php if (!$list): ?>
<?php echo term('news.view'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>
<div class="list-group">
<?php foreach ($list as $id => $news): ?>
	<a href="<?php echo Uri::create('admin/news/detail/'.$id); ?>" class="list-group-item" id="list-group-item_<?php echo $id; ?>">
		<h4 class="list-group-item-heading">
			<?php echo strim($news->title, Config::get('news.view_params.admin.list.trim_width.title')); ?>
<?php if (!$news->is_published): ?>
			<?php echo render('_parts/label', array('name' => term('form.draft'), 'attr' => 'label-warning')); ?>
<?php endif; ?>
		</h4>
		<p class="list-group-item-text">
			<?php echo strim($news->body, Config::get('news.view_params.admin.list.trim_width.body')); ?>
		</p>
	</a>
<?php endforeach; ?>
</div>
<?php echo Pagination::instance('mypagination')->render(); ?>
<?php endif; ?>
