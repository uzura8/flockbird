<div class="profile_img_box well">
	<a class="account-summary account-summary-small" data-nav="profile" href="<?php echo Uri::create('member/profile'); ?>">
	<div class="content">
	<div class="account-group js-mini-current-user" data-screen-name="<?php echo site_get_screen_name($u); ?>">
	<?php echo img($u->get_image(), '50x50xc', '', false, site_get_screen_name($u), true); ?>
	<div class="main"><b class="fullname"><?php echo site_get_screen_name($u); ?></b></div>
	<small class="metadata">プロフィールを見る</small>
	</div>
	</div>
	</a>
</div>
