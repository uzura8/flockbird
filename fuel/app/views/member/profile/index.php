<div class="well">
	<p><?php echo Config::get('site.term.profile'); ?>画面です</p>
	<div><?php echo site_profile_image($current_user->get_image(), '180x180', '', true); ?></div>
	<?php echo Html::anchor('member/profile/setting_image', '<i class="icon-camera"></i>'.Config::get('site.term.profile').'写真設定', array('class' => 'btn')); ?>
</div>
