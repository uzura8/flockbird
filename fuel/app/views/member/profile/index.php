<div class="well">
	<p><?php echo Config::get('term.profile'); ?>画面です</p>
	<div><?php echo img($member->get_image(), '180x180xc', '', true, site_get_screen_name($member), true); ?></div>
<?php if ($is_mypage): ?>
	<div><?php echo Html::anchor('member/profile/setting_image', '<i class="icon-camera"></i>'.Config::get('term.profile').'写真設定', array('class' => 'btn btn-default')); ?></div>
<?php endif; ?>
</div>
