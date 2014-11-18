<?php if (!$list): ?>
<?php echo term('content.page'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>

<table class="table table-hover table-responsive">
<tr>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th>記事識別名</th>
	<th>タイトル</th>
	<th class="small">公開範囲</th>
	<th class="small"><?php echo term('form.edit'); ?></th>
	<th class="datetime">作成日時</th>
	<th class="datetime">最終更新</th>
</tr>
<?php foreach ($list as $id => $content_page): ?>
<tr id="<?php echo $content_page->id; ?>">
	<td class="small"><?php echo $content_page->id; ?></td>
	<td class="small"><?php echo $content_page->slug; ?></td>
	<td><?php echo Html::anchor('admin/content/page/'.$content_page->id, strim($content_page->title, Config::get('content.page.viewParams.admin.list.trim_width.title'))); ?></td>
	<td><?php echo label_is_secure($content_page->is_secure, true); ?></td>
	<td class="small"><?php echo btn('form.edit', 'admin/content/page/edit/'.$content_page->id, '', false, 'xs'); ?></td>
	<td><?php echo site_get_time($content_page->created_at, 'relative', 'Y/m/d H:i'); ?></td>
	<td><?php echo site_get_time($content_page->updated_at, 'relative', 'Y/m/d H:i'); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Pagination::instance('mypagination')->render(); ?>
<?php endif; ?>
