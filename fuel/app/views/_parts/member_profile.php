<?php
if (empty($is_mypage)) $is_mypage = false;
if (empty($is_list)) $is_list = false;
if (empty($access_from)) $access_from = 'guest';
if (empty($display_type)) $display_type = 'detail';
if (!isset($with_image_upload_form)) $with_image_upload_form = false;
if (empty($page_type)) $page_type = 'detail';
switch ($page_type)
{
	case 'lerge_list':
		$member_name_tag = 'h4';
		$button_follow_size = 'xs';
		$col_class = 'xs';
		$image_col_size = 3;
		$image_size = 'ML';
		break;
	case 'list':
		$member_name_tag = 'h5';
		$button_follow_size = 'xs';
		$col_class = 'xs';
		$image_col_size = 2;
		$image_size = 'M';
		break;
	case 'detail':
	default :
		$member_name_tag = 'h3';
		$button_follow_size = 'sm';
		$col_class = 'sm';
		$image_col_size = 4;
		$image_size = 'L';
		break;
}
$profile_page_uri = '';
$is_link2raw_file = true;
if (!empty($link_uri))
{
	$profile_page_uri = $link_uri;
	$is_link2raw_file = false;
}
elseif ($page_type != 'detail')
{
	$profile_page_uri = 'member/'.$member->id;
	$is_link2raw_file = false;
}
?>
<?php if (!$is_list): ?><div class="well profile"><?php endif; ?>
<?php if (!empty($with_edit_btn) && $is_mypage): ?>
	<?php echo Html::anchor('member/profile/edit', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-xs btnEdit')); ?>
<?php endif; ?>
	<div class="row">
		<div class="col-<?php echo $col_class; ?>-<?php echo $image_col_size; ?>">
			<div class="imgBox"><?php echo img($member->get_image(), conf('upload.types.img.types.m.sizes.'.$image_size), $profile_page_uri, $is_link2raw_file, site_get_screen_name($member), true, true); ?></div>
<?php if (!empty($with_link2profile_image)): ?>
			<div class="btnBox"><?php echo Html::anchor(sprintf('member/profile/image%s', $is_mypage ? '' : '/'.$member->id), '<i class="glyphicon glyphicon-camera"></i> '.term('profile').'写真', array('class' => 'btn btn-default btn-sm')); ?></div>
<?php endif; ?>
<?php if ($is_mypage && $with_image_upload_form  && $member->file_id): ?>
				<?php echo btn('delete', '#', 'js-simplePost', true, 'sm', 'default', array('data-uri' => 'member/profile/image/unset')); ?>
<?php endif; ?>
<?php if ($is_mypage && $with_image_upload_form): ?>
<?php echo render('_parts/form/upload_form', array('form_attrs' => array('action' => 'member/profile/image/edit'))); ?>
<?php endif; ?>
		</div>
		<div class="col-<?php echo $col_class; ?>-<?php echo 12 - $image_col_size; ?>">
				<<?php echo $member_name_tag; ?>>
<?php if ($display_type != 'detail'): ?>
					<?php echo Html::anchor($profile_page_uri, site_get_screen_name($member)); ?>
<?php else: ?>
					<?php echo site_get_screen_name($member); ?>
<?php endif; ?>
<?php if (conf('memberRelation.follow.isEnabled') && Auth::check() && $member->id != $u->id): ?>
					<?php echo render('_parts/button_follow', array(
						'member_id_from' => Auth::check() ? $u->id : 0,
						'member_id_to' => $member->id,
						'size' => $button_follow_size,
						'attrs' => array('class' => array('ml10'))
					)); ?>
<?php endif; ?>
				</<?php echo $member_name_tag; ?>>
<?php if (check_display_type(conf('profile.sex.displayType'), $display_type) && check_public_flag($member->sex_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-xs-4"><label><?php echo term('member.sex.label'); ?></label></div>
				<div class="col-xs-8"><?php echo Model_Member::get_sex_options($member->sex); ?></div>
			</div>
<?php endif; ?>
<?php if ($member->birthyear && check_display_type(conf('profile.birthday.birthyear.displayType'), $display_type) && check_public_flag($member->birthyear_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-xs-4"><label>
					<?php if (conf('profile.birthday.birthyear.viewType')): ?><?php echo term('member.age'); ?><?php else: ?><?php echo term('member.birthyear'); ?><?php endif; ?>
				</label></div>
				<div class="col-xs-8">
					<?php if (conf('profile.birthday.birthyear.viewType')): ?><?php echo Util_Date::calc_age($member->birthyear, check_public_flag($member->birthday_public_flag, $access_from) ? $member->birthday : null); ?>歳<?php else: ?><?php echo $member->birthyear; ?>年<?php endif; ?>
				</div>
			</div>
<?php endif; ?>
<?php if ($member->birthday && check_display_type(conf('profile.birthday.birthday.displayType'), $display_type) && check_public_flag($member->birthday_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-xs-4"><label><?php echo term('member.birthday'); ?></label></div>
				<div class="col-xs-8"><?php echo Util_Date::conv_date_format($member->birthday, '%d月%d日'); ?></div>
			</div>
<?php endif; ?>
			<?php echo render('member/profile/_parts/values', array('member' => $member, 'member_profiles' => $member_profiles, 'access_from' => $access_from, 'display_type' => $display_type)); ?>
			<ul class="list-inline mt10">
				<li><small><label><?php echo term('site.registration'); ?>:</label> <?php echo site_get_time($member->created_at) ?></small></li>
				<?php if ($member->last_login): ?><li><small><label>最終<?php echo term('site.login'); ?>:</label> <?php echo site_get_time($member->last_login) ?></small></li><?php endif; ?>
			</ul>
		</div>
	</div>
<?php if (!$is_list): ?></div><?php endif; ?>
