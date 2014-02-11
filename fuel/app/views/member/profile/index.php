<div class="well">
	<p><?php echo Config::get('term.profile'); ?>画面です</p>
	<div><?php echo img($member->get_image(), '180x180xc', '', true, site_get_screen_name($member), true); ?></div>
	<div><?php echo Html::anchor(sprintf('member/profile/image%s', $is_mypage ? '' : '/'.$member->id), '<i class="glyphicon glyphicon-camera"></i> '.term('profile').'写真', array('class' => 'btn btn-default')); ?></div>
</div>
