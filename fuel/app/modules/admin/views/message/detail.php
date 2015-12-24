<h4><?php echo term('message.form.send_to', 'member.view'); ?></h4>

<?php if (\Message\Site_Util::check_type($type, 'site_info') && !empty($target_members)): ?>
<ul>
<?php foreach ($target_members as $target_member): ?>
	<li><?php echo anchor('admin/member/'.$target_member['id'], $target_member['name'], true) ?></li>
<?php endforeach; ?>
</ul>
<?php elseif (\Message\Site_Util::check_type($type, 'site_info_all')): ?>
<p><?php echo term('member.view', 'member.all'); ?></p>
<?php endif; ?>

<h3 class="mt20"><?php echo term('message.form.body'); ?></h3>
<div class="article_body">
<?php echo convert_body($message->body, array('is_truncate' => false)); ?>
</div>

