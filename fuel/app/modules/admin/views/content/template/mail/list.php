<?php if (!$list): ?>
<?php echo term('site.template'); ?>がありません。
<?php else: ?>

<?php foreach ($list as $module => $templates): ?>
<?php 	if ($templates): ?>
<h3><?php echo ($module == 'admin') ? term('admin.view', 'page.view') : term('site.view'); ?></h3>
<table class="table table-hover table-responsive">
<tr>
	<th><?php echo term('site.item'); ?></th>
	<th class="small"><?php echo term('form.format'); ?></th>
	<th class="small"><?php echo term('form.edit'); ?></th>
</tr>
<?php 		foreach ($templates as $key => $config): ?>
<?php 			if ($key == 'common_variables') continue; ?>
<?php $edit_uri = sprintf('admin/content/template/mail/edit/%s/%s', $module, $key); ?>
<tr>
	<td><?php echo Html::anchor($edit_uri, $config['view']); ?></td>
	<td><?php echo $config['format']; ?></td>
	<td class="small"><?php echo btn('form.edit', $edit_uri, '', false, 'xs'); ?></td>
</tr>
<?php 		endforeach; ?>
</table>
<?php 	endif; ?>
<?php endforeach; ?>
<?php endif; ?>
