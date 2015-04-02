<?php echo render('_parts/member_profile', array(
	'is_mypage' => $is_mypage,
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'display_type' => 'summary',
	'with_image_upload_form' => true,
)); ?>

<?php if (is_enabled('album') && conf('upload.types.img.types.m.save_as_album_image')): ?>
<?php echo render('album::image/_parts/list', array('list' => $images, 'is_simple_view' => true)); ?>
<?php endif; ?>
