<div class="profile_img_box well">
	<a class="account-summary account-summary-small" data-nav="profile" href="<?php echo Uri::create('member/profile'); ?>">
	<div class="content">
	<div class="account-group js-mini-current-user" data-screen-name="<?php echo member_name($u); ?>">
	<?php echo member_image($u, 'M', ''); ?>
	<div class="main"><b class="fullname"><?php echo member_name($u); ?></b></div>
	<small class="metadata"><?php echo term('profile'); ?>をみる</small>
	</div>
	</div>
	</a>
</div>
