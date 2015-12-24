<?php if (!$list): ?>
<?php echo term('message.view'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>

<table class="table table-hover table-responsive">
<tr>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th class="u-sm"><?php echo term('common.kind'); ?></th>
	<th><?php echo term('message.form.subject'); ?></th>
	<th class="small"><?php echo term('form.edit'); ?></th>
	<th class="small"><?php echo term('form.send', 'form.operation'); ?></th>
	<th class="u-sm"><?php echo term('common.status'); ?></th>
	<th class="datetime"><?php echo term('form.send', 'site.datetime'); ?></th>
	<th class="datetime"><?php echo term('form.updated', 'site.datetime'); ?></th>
</tr>
<?php foreach ($list as $id => $message): ?>
<tr id="<?php echo $message->id; ?>"<?php if (!$message->is_sent): ?> class="danger"<?php endif; ?>>
	<td class="small"><?php echo $message->id; ?></td>
	<td class="u-sm fs12"><?php echo \Message\Site_Util::get_type_label($message->type, true); ?></td>
	<td><?php echo Html::anchor('admin/message/'.$message->id, strim($message->subject, view_params('trim_width.title', 'message', 'list', null, true))); ?></td>
<?php 	if (!$message->is_sent && check_acl($uri = 'admin/message/edit')): ?>
	<td class="small"><?php echo btn('form.edit', $uri.'/'.$message->id, '', false, 'xs'); ?></td>
<?php 	else: ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php 	endif; ?>

<?php 	if (check_acl('admin/message/sent')): ?>
<?php $attr = array('data-destination' => Uri::string_with_query()); ?>
<?php 		if ($message->is_sent): ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php 		else: ?>
	<td class="small"><?php echo btn('form.do_send', '#', 'js-simplePost', true, 'xs', 'warning', $attr + array(
		'data-uri' => 'admin/message/send/'.$message->id,
		'data-msg' => term('form.do_send').'しますか？',
	)); ?></td>
<?php 		endif; ?>
<?php 	else: ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php 	endif; ?>
	<td class="u-sm fs12 text-<?php if (!$message->is_sent): ?>danger<?php else: ?>muted<?php endif; ?>"><?php echo term($message->is_sent ? 'form.sent' : 'form.unsent'); ?></td>
	<td class="fs12">
		<?php if (isset_datatime($message->sent_at)): ?><?php echo site_get_time($message->sent_at, 'both', 'Y/m/d H:i'); ?><?php else: ?><?php echo symbol('noValue'); ?><?php endif; ?>
	</td>
	<td class="fs12"><?php echo site_get_time($message->updated_at, 'relative', 'Y/m/d H:i'); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Pagination::instance('mypagination')->render(); ?>
<?php endif; ?>
