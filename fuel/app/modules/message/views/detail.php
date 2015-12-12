<?php if (!empty($target_members)): ?>
<h4><?php echo term('message.form.send_to', 'member.view'); ?></h4>
<ul>
<?php foreach ($target_members as $target_member): ?>
	<li><?php echo anchor('admin/member/'.$target_member['id'], $target_member['name'], true) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<h3 class="mt20"><?php echo term('message.form.body'); ?></h3>
<div class="article_body">
<?php echo convert_body($message->body, array('is_truncate' => false)); ?>
</div>

