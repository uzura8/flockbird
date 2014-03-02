<div class="well profile">
<?php if ($is_mypage): ?>
	<?php echo Html::anchor('member/profile/edit', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-xs btnEdit')); ?>
<?php endif; ?>
	<div class="row">
		<div class="col-md-4">
			<div><?php echo img($member->get_image(), '180x180xc', '', true, site_get_screen_name($member), true, true); ?></div>
			<div><?php echo Html::anchor(sprintf('member/profile/image%s', $is_mypage ? '' : '/'.$member->id), '<i class="glyphicon glyphicon-camera"></i> '.term('profile').'写真', array('class' => 'btn btn-default')); ?></div>
		</div>
		<div class="col-md-8">
			<div class="row"><h3><?php echo site_get_screen_name($member); ?></h3></div>
			<?php echo render('member/profile/_parts/values', array('member_profiles' => $member_profiles, 'access_from' => $access_from)); ?>
		</div>
	</div>
</div>
