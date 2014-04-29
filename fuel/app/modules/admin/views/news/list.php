<?php //echo render('news/_parts/list', array('list' => $list, 'page' => $page, 'is_next' => $is_next, 'member' => $member, 'is_draft' => $is_draft)); ?>
<?php if (!$list): ?>
<?php echo term('news.view'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>

<table class="table table-hover table-responsive">
<tr>
	<th class="small">id</th>
	<th class="small"><?php echo term('news.category.simple'); ?></th>
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
<tr id="<?php echo $news->id; ?>" class="<?php echo \News\Site_Util::get_status_label_type($status); ?>">
	<td class="small"><?php echo $news->id; ?></td>
	<td class="small fs10"><?php echo $news->news_category->name; ?></td>
	<td><?php echo Html::anchor('admin/news/'.$news->id, strim($news->title, Config::get('news.viewParams.admin.list.trim_width.title'))); ?></td>
	<td class="small"><?php echo btn('preview', 'news/preview/'.$news->id.'?token='.$news->token, '', false, 'xs'); ?></td>
	<td class="small"><?php echo btn('edit', 'admin/news/edit/'.$news->id, '', false, 'xs'); ?></td>
<?php $attr = array('data-destination' => Uri::string_with_query()); ?>
<?php if ($news->is_published): ?>
	<td class="small"><?php echo btn('do_unpublish', '#', 'btn_publish', true, 'xs', null, $attr + array('data-uri' => 'admin/news/unpublish/'.$news->id)); ?></td>
<?php else: ?>
	<td class="small"><?php echo btn('do_publish', '#', 'btn_publish', true, 'xs', null, $attr + array('data-uri' => 'admin/news/publish/'.$news->id)); ?></td>
<?php endif; ?>
	<td><?php echo label(term('news.status.'.$status), \News\Site_Util::get_status_label_type($status)); ?></td>
	<td class="text-<?php if ($status == 'reserved'): ?>warning<?php elseif ($status == 'closed'): ?>muted<?php else: ?>normal<?php endif; ?>">
		<?php if ($news->published_at): ?><?php echo site_get_time($news->published_at, 'both', 'Y/m/d H:i'); ?><?php else: ?><?php echo symbol('noValue'); ?><?php endif; ?>
	</td>
	<td><?php echo site_get_time($news->updated_at, 'relative', 'Y/m/d H:i'); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Pagination::instance('mypagination')->render(); ?>
<?php endif; ?>
