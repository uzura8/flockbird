<div class="member_img_box_s">
	<?php echo img($member->get_image(), '30x30', 'member/'.$member->id); ?>
	<div class="content">
		<div class="main">
			<b class="fullname"><?php echo empty($member) ? Config::get('site.term.left_member') : Html::anchor('member/'.$member->id, $member->name); ?></b>
		</div>
<?php if ($date): ?>
		<small><?php if (!empty($date['label'])) echo $date['label'].': '; ?><?php echo site_get_time($date['datetime']) ?></small>
<?php endif; ?>
<?php if (!empty($content)): ?><?php echo $content; ?><?php endif; ?>
	</div>
</div>
