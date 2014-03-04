<?php
$profile_page_uri = '';
$is_link2raw_file = true;
if (!empty($link_uri))
{
	$profile_page_uri = $link_uri;
	$is_link2raw_file = false;
}
if (empty($display_type)) $display_type = 'detail';
if (!isset($with_image_upload_form)) $with_image_upload_form = false;
?>
<div class="well profile">
<?php if (!empty($with_edit_btn) && $is_mypage): ?>
	<?php echo Html::anchor('member/profile/edit', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-xs btnEdit')); ?>
<?php endif; ?>
	<div class="row">
		<div class="col-md-4">
			<div class="imgBox"><?php echo img($member->get_image(), '180x180xc', $profile_page_uri, $is_link2raw_file, site_get_screen_name($member), true, true); ?></div>
<?php if (!empty($with_link2profile_image)): ?>
			<div class="btnBox"><?php echo Html::anchor(sprintf('member/profile/image%s', $is_mypage ? '' : '/'.$member->id), '<i class="glyphicon glyphicon-camera"></i> '.term('profile').'写真', array('class' => 'btn btn-default btn-sm')); ?></div>
<?php endif; ?>
<?php if ($is_mypage && $with_image_upload_form  && $member->file_id): ?>
				<?php echo Html::anchor('#', '<i class="glyphicon glyphicon-trash"></i> '.term('form.delete'), array(
					'class' => 'btn btn-default btn-sm delete_image',
					'onclick' => "delete_item('member/profile/image/unset');return false;",
				)); ?>
<?php endif; ?>
<?php if ($is_mypage && $with_image_upload_form): ?>
<?php echo render('_parts/form/upload_form', array('form_attrs' => array('action' => 'member/profile/image/edit'))); ?>
<?php endif; ?>
		</div>
		<div class="col-md-8">
			<div class="row"><h3><?php if (empty($link_uri)): ?><?php echo site_get_screen_name($member); ?><?php else: ?><?php echo Html::anchor($link_uri, site_get_screen_name($member)); ?><?php endif; ?></h3></div>
			<?php echo render('member/profile/_parts/values', array('member_profiles' => $member_profiles, 'access_from' => $access_from, 'display_type' => $display_type)); ?>
		</div>
	</div>
</div>
