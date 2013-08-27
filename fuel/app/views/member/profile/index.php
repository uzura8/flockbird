<div class="well">
	<p><?php echo Config::get('term.profile'); ?>画面です</p>
	<div><?php echo img($u->get_image(), '180x180xc', '', true, site_get_screen_name($u), true); ?></div>
	<?php echo Html::anchor('member/profile/setting_image', '<i class="icon-camera"></i>'.Config::get('term.profile').'写真設定', array('class' => 'btn')); ?>
</div>
