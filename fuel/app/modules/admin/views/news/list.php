<?php //echo render('news/_parts/list', array('list' => $list, 'page' => $page, 'is_next' => $is_next, 'member' => $member, 'is_draft' => $is_draft)); ?>
<?php if (!$list): ?>
<?php echo term('news.view'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>

<table class="table" id="jqui-sortable">
<tr>
	<th>id</th>
	<th colspan="2"><?php echo term('form.operation'); ?></th>
	<th>タイトル</th>
	<th>公開日時</th>
	<th>最終更新日時</th>
</tr>
<?php foreach ($list as $id => $news): ?>
<tr id="<?php echo $news->id; ?>">
	<td><?php echo $news->id; ?></td>
	<td><?php echo btn('edit', 'admin/news/edit/'.$news->id, '', false, 'xs'); ?></td>
	<td><?php echo btn('publish', '#', 'btn_news_publish', false, 'xs', 'default', array('data-id' => $news->id)); ?></td>
	<td><?php echo Html::anchor('admin/news/'.$news->id, strim($news->title, Config::get('news.view_params.admin.list.trim_width.title'))); ?></td>
	<td><?php if ($news->published_at): ?><?php echo site_get_time($news->published_at, 'normal'); ?><?php else: ?><?php echo symbol('noValue'); ?><?php endif; ?></td>
	<td><?php echo site_get_time($news->updated_at); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Pagination::instance('mypagination')->render(); ?>
<?php endif; ?>
