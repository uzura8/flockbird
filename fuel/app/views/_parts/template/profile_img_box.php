<div class="profile_img_box well">
	<div class="content">
		<?php echo member_image($u, 'M', 'member/me'); ?>
		<div class="main">
			<div class="fullname"><?php echo member_name($u, 'member/me'); ?></div>
		</div>
		<small><?php echo anchor('member/profile/edit', icon_label('member.profile.edit', 'both', false)); ?></small>
	</div>
</div>

