<p><?php echo Html::anchor('admin/profile/create', '<i class="ls-icon-edit"></i> 新規作成', array('class' => 'btn btn-default')); ?></p>
<?php if ($profiles): ?>
<table class="table">
<tr>
	<th>ID</th>
<?php foreach ($labels as $label): ?>
	<th><?php echo $label; ?></th>
<?php endforeach; ?>
</tr>
<?php foreach ($profiles as $profile): ?>
<tr>
	<td><?php echo $profile->id; ?></td>
	<td><?php echo $profile->name; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<?php echo term('profile'); ?>項目がありません。
<?php endif; ?>
