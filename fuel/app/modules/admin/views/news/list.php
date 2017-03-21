<?php if (!$list): ?>
<?php echo term('news.view'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>

<table class="table table-hover table-responsive">
<tr>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th><?php echo term('news.category.simple'); ?></th>
	<th>タイトル</th>
	<th class="small fs9"><?php echo term('form.preview'); ?></th>
	<th class="small"><?php echo term('form.edit'); ?></th>
	<th class="small"><?php echo term('form.publish', 'form.operation'); ?></th>
	<th class="small">状態</th>
	<th class="datetime">公開日時</th>
	<th class="datetime">最終更新</th>
</tr>
<?php foreach ($list as $id => $news): ?>
<?php $status = \News\Site_Util::get_status($news->is_published, $news->published_at); ?>
<tr id="<?php echo $news->id; ?>"<?php if ($class = \News\Site_Util::get_status_label_type($status, true)): ?> class="<?php echo $class; ?>"<?php endif; ?>>
	<td class="small"><?php echo $news->id; ?></td>
	<td class="fs10"><?php echo isset($news->news_category->label) ? $news->news_category->label : sprintf('<span class="text-danger">%s</span>', term('site.unset')); ?></td>
	<td><?php echo Html::anchor('admin/news/'.$news->id, strim($news->title, Config::get('news.viewParams.admin.list.trim_width.title'))); ?></td>
	<td class="small"><?php echo btn('form.preview', 'news/preview/'.$news->slug.'?token='.$news->token, '', false, 'xs'); ?></td>
<?php 	if (check_acl($uri = 'admin/news/edit')): ?>
	<td class="small"><?php echo btn('form.edit', $uri.'/'.$news->id, '', false, 'xs'); ?></td>
<?php 	else: ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php 	endif; ?>

<?php 	if (check_acl('admin/news/publish')): ?>
<?php $attr = array('data-destination' => Uri::string_with_query()); ?>
<?php 		if ($news->is_published): ?>
	<td class="small"><?php echo btn('form.do_unpublish', '#', 'btn_publish', true, 'xs', null, $attr + array(
		'data-uri' => 'admin/news/unpublish/'.$news->id,
		'data-msg' => term('form.unpublish').'にしますか？',
	)); ?></td>
<?php 		else: ?>
	<td class="small"><?php echo btn('form.do_publish', '#', 'btn_publish', true, 'xs', null, $attr + array(
		'data-uri' => 'admin/news/publish/'.$news->id,
		'data-msg' => term('form.publish').'しますか？',
	)); ?></td>
<?php 		endif; ?>
<?php 	else: ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php 	endif; ?>

	<td><?php echo label(term('news.status.'.$status), \News\Site_Util::get_status_label_type($status)); ?></td>
	<td class="fs12 text-<?php if ($status == 'reserved'): ?>warning<?php elseif ($status == 'closed'): ?>muted<?php else: ?>normal<?php endif; ?>">
		<?php if (isset_datatime($news->published_at)): ?><?php echo site_get_time($news->published_at, 'both'); ?><?php else: ?><?php echo symbol('noValue'); ?><?php endif; ?>
	</td>
	<td class="fs12"><?php echo site_get_time($news->updated_at); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Pagination::instance('mypagination')->render(); ?>
<?php endif; ?>
