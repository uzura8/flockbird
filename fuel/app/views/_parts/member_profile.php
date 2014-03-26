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
			<div class="row">
				<h3>
					<?php if (empty($link_uri)): ?><?php echo site_get_screen_name($member); ?><?php else: ?><?php echo Html::anchor($link_uri, site_get_screen_name($member)); ?><?php endif; ?>
<?php if (conf('memberRelation.follow.isEnabled') && !$is_mypage): ?>
					<?php echo render('_parts/button_follow', array('member_id_from' => $u->id, 'member_id_to' => $member->id, 'attrs' => array('class' => array('ml10')))); ?>
<?php endif; ?>
				</h3>
			</div>
<?php if (check_display_type(conf('profile.sex.displayType'), $display_type) && check_public_flag($member->sex_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-sm-4"><label><?php echo term('member.sex.label'); ?></label></div>
				<div class="col-sm-8"><?php echo Model_Member::get_sex_options($member->sex); ?></div>
			</div>
<?php endif; ?>
<?php if ($member->birthyear && check_display_type(conf('profile.birthday.birthyear.displayType'), $display_type) && check_public_flag($member->birthyear_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-sm-4"><label>
					<?php if (conf('profile.birthday.birthyear.viewType')): ?><?php echo term('member.age'); ?><?php else: ?><?php echo term('member.birthyear'); ?><?php endif; ?>
				</label></div>
				<div class="col-sm-8">
					<?php if (conf('profile.birthday.birthyear.viewType')): ?><?php echo Util_Date::calc_age($member->birthyear, check_public_flag($member->birthday_public_flag, $access_from) ? $member->birthday : null); ?>歳<?php else: ?><?php echo $member->birthyear; ?>年<?php endif; ?>
				</div>
			</div>
<?php endif; ?>
<?php if ($member->birthday && check_display_type(conf('profile.birthday.birthday.displayType'), $display_type) && check_public_flag($member->birthday_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-sm-4"><label><?php echo term('member.birthday'); ?></label></div>
				<div class="col-sm-8"><?php echo Util_Date::conv_date_format($member->birthday, '%d月%d日'); ?></div>
			</div>
<?php endif; ?>
			<?php echo render('member/profile/_parts/values', array('member' => $member, 'member_profiles' => $member_profiles, 'access_from' => $access_from, 'display_type' => $display_type)); ?>
		</div>
	</div>
</div>
