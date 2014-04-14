<?php echo create_anchor_button('admin/news/create'); ?>
<ul class="nav nav-pills">
	<li<?php if (!$is_draft): ?> class="disabled"<?php endif; ?>><?php echo Html::anchor(
		$is_draft ? 'news/list' : '#',
		term('form.published'),
		$is_draft ? array() : array('onclick' => 'return false;')
	); ?></li>
	<li<?php if ($is_draft): ?> class="disabled"<?php endif; ?>><?php echo Html::anchor(
		$is_draft ? '#' : 'news/list?is_draft=1',
		term('form.draft'),
		$is_draft ? array('onclick' => 'return false;') : array()
	); ?></li>
</ul>

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
