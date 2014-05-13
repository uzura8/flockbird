<?php if (!$list): ?>
<?php echo term('admin.account.view'); ?>がありません。
<?php else: ?>

<table class="table table-hover table-responsive">
<tr>
	<th class="small"><?php echo term('form.delete'); ?></th>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th><?php echo term('admin.account.username'); ?></th>
	<th><?php echo term('admin.user.groups.view_simple'); ?></th>
	<th><?php echo term('site.email'); ?></th>
	<th>作成日時</th>
</tr>
<?php foreach ($list as $id => $user): ?>
<tr>
<?php if ($user->id == conf('original_user_id.admin') || !\Admin\Site_AdminUser::check_gruop($u->group, 100)): ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php else: ?>
	<td class="small"><?php echo btn('form.delete', '#', 'js-simplePost', false, 'xs', null, array('data-uri' => 'admin/account/delete/'.$user->id)); ?></td>
<?php endif; ?>
	<td class="small"><?php echo $user->id; ?></td>
	<td><?php echo $user->username; ?></td>
	<td><?php echo \Admin\Site_AdminUser::get_gruop_name($user->group, true); ?></td>
	<td>
<?php if (\Admin\Site_AdminUser::check_gruop($u->group, 100)): ?>
		<?php echo $user->email; ?>
<?php else: ?>
		<?php echo sprintf('<span class="text-muted">%s</span>', term('site.set_already')); ?>
<?php endif; ?>
	</td>
	<td><?php echo site_get_time($user->created_at, 'relative', 'Y/m/d H:i'); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
