<?php
if (empty($is_mypage)) $is_mypage = false;
if (empty($is_list)) $is_list = false;
if (empty($access_from)) $access_from = 'guest';
if (empty($display_type)) $display_type = 'detail';
if (empty($page_type)) $page_type = 'detail';
if (!isset($with_image_upload_form)) $with_image_upload_form = false;
if (!isset($is_simple_list)) $is_simple_list = false;
switch ($page_type)
{
	case 'lerge_list':
		$member_name_tag = 'h4';
		$edit_button_size = 'xs';
		$col_class_left  = 'col-xs-3';
		$col_class_right = 'col-xs-9';
		$image_size = 'ML';
		break;
	case 'list':
		$member_name_tag = 'h5';
		$edit_button_size = 'xs';
		$col_class_left  = 'col-xs-3 col-sm-2';
		$col_class_right = 'col-xs-9 col-sm-10';
		$image_size = 'M';
		break;
	case 'detail':
	default :
		$member_name_tag = 'h3';
		$edit_button_size = 'sm';
		$col_class_left  = 'col-sm-4';
		$col_class_right = 'col-sm-8';
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
	$profile_page_uri = $member ? 'member/'.$member->id : '';
	$is_link2raw_file = false;
}

$is_display_follow_btn  = (conf('memberRelation.follow.isEnabled') && empty($is_hide_fallow_btn) && Auth::check() && !$is_mypage && $member->id != get_uid());
$is_display_access_block_btn = (!empty($is_display_access_block_btn) && conf('memberRelation.accessBlock.isEnabled') && Auth::check() && !$is_mypage);
$is_display_message_btn = (is_enabled('message') && !empty($with_message_btn) && Auth::check() && !$is_mypage);
$is_display_member_edit_btn = (empty($is_display_access_block_btn) && conf('memberRelation.accessBlock.isEnabled') && $page_type == 'detail' && !$is_mypage);
?>
<?php if (!$is_list): ?><div class="well profile"><?php endif; ?>
<?php if (!empty($with_edit_btn) && $is_mypage): ?>
	<?php echo btn('form.edit', 'member/profile/edit', 'btnEdit', true, 'sm'); ?>
<?php endif; ?>
	<div class="row">
		<div class="article-left <?php echo $col_class_left; ?>">
			<div class="imgBox"><?php echo member_image($member, $image_size, $profile_page_uri, $is_link2raw_file); ?></div>
<?php if (!empty($with_link2profile_image)): ?>
			<div class="btnBox">
				<?php echo btn(term('profile', 'site.picture'), sprintf('member/profile/image%s', $is_mypage ? '' : '/'.$member->id), null, true, 'sm', null, null, 'camera', null, null, false); ?>
			</div>
<?php endif; ?>
<?php if ($is_mypage && $with_image_upload_form): ?>
<?php 	if ($member->file_name): ?>
			<div class="btnBox">
				<?php echo btn('form.delete', '#', 'js-simplePost', true, 'sm', 'danger', array('data-uri' => 'member/profile/image/unset', 'class' => 'u-center')); ?>
			</div>
<?php 	endif; ?>
			<div class="mt10">
				<?php echo render('_parts/form/upload_form', array('form_attrs' => array('action' => 'member/profile/image/edit'))); ?>
			</div>
<?php endif; ?>
		</div>
		<div class="article-right <?php echo $col_class_right; ?>">
				<<?php echo $member_name_tag; ?>>
					<?php echo member_name($member, $display_type != 'detail' ? $profile_page_uri : '', true); ?>

<?php if ($is_display_message_btn || $is_display_follow_btn || $is_display_access_block_btn || $is_display_member_edit_btn): ?>
					<div class="btn-group ml10" role="group" aria-label="action for member">

<?php 	if ($is_display_follow_btn): ?>
						<?php echo render('_parts/btn_update_member_relation', array(
							'relation_type' => 'follow',
							'size' => $edit_button_size,
							'member_id_from' => get_uid(),
							'member_id_to' => $member ? $member->id : null,
							'size' => $edit_button_size,
							'attrs' => array('class' => array(''))
						)); ?>
<?php 	endif; ?>

<?php 	if ($is_display_access_block_btn): ?>
						<?php echo render('_parts/btn_update_member_relation', array(
							'relation_type' => 'access_block',
							'size' => $edit_button_size,
							'member_id_from' => get_uid(),
							'member_id_to' => $member ? $member->id : null,
							'size' => $edit_button_size,
							'attrs' => array('class' => array(''))
						)); ?>
<?php 	endif; ?>

<?php 	if ($is_display_message_btn): ?>
						<?php echo btn('message.form.send', 'message/member/'.$member->id, null, true, $edit_button_size, null, null, null, 'button'); ?>
<?php 	endif; ?>

<?php 	if ($is_display_member_edit_btn): ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_member_'.$member->id,
	'class' => array('dropdown'),
);
$menus = array(
	array(
		'icon_term' => Model_MemberRelation::check_relation('access_block', get_uid(), $member->id) ? 'undo_accessBlock' : 'do_accessBlock',
		'attr' => array(
			'class' => 'js-update_toggle',
			'id' => 'btn_access_block_'.$member->id,
			'data-uri' => sprintf('member/relation/api/update/%d/access_block.json', $member->id),
		),
	),
);
echo btn_dropdown('noterm.dropdown', $menus, false, $edit_button_size, null, true, $dropdown_btn_group_attr, null, false);
?>
<?php 	endif; ?>

				</div>
<?php endif; ?>

				</<?php echo $member_name_tag; ?>>
<?php if (!$is_simple_list
				&& check_display_type(conf('profile.sex.displayType'), $display_type)
				&& check_public_flag($member->sex_public_flag, $access_from)
				&& $sex = Site_Form::get_form_options4config('term.member.sex.options', $member->sex, true)): ?>
			<div class="row">
				<div class="col-xs-4 u-alr"><label><?php echo term('member.sex.label'); ?></label></div>
				<div class="col-xs-8"><?php echo $sex; ?></div>
			</div>
<?php endif; ?>
<?php if (!$is_simple_list
				&& $member->birthyear
				&& check_display_type(conf('profile.birthday.birthyear.displayType'), $display_type)
				&& check_public_flag($member->birthyear_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-xs-4 u-alr"><label>
					<?php if (conf('profile.birthday.birthyear.viewType')): ?><?php echo term('member.age'); ?><?php else: ?><?php echo term('member.birthyear'); ?><?php endif; ?>
				</label></div>
				<div class="col-xs-8">
					<?php if (conf('profile.birthday.birthyear.viewType')): ?><?php echo Util_Date::calc_age($member->birthyear, check_public_flag($member->birthday_public_flag, $access_from) ? $member->birthday : null); ?>歳<?php else: ?><?php echo $member->birthyear; ?>年<?php endif; ?>
				</div>
			</div>
<?php endif; ?>
<?php if (!$is_simple_list
				&& !empty($member_profiles)
				&& $member->birthday
				&& check_display_type(conf('profile.birthday.birthday.displayType'), $display_type)
				&& check_public_flag($member->birthday_public_flag, $access_from)): ?>
			<div class="row">
				<div class="col-xs-4 u-alr"><label><?php echo term('member.birthday'); ?></label></div>
				<div class="col-xs-8"><?php echo Util_Date::conv_date_format($member->birthday, '%d月%d日'); ?></div>
			</div>
<?php endif; ?>
<?php if (!empty($member_profiles)): ?>
			<?php echo render('member/profile/_parts/values', array('member' => $member, 'member_profiles' => $member_profiles, 'access_from' => $access_from, 'display_type' => $display_type)); ?>
<?php endif; ?>
<?php if (!$is_simple_list): ?>
			<ul class="list-inline mt10">
				<li><small><label><?php echo term('site.registration'); ?>:</label> <?php echo site_get_time($member->created_at) ?></small></li>
				<?php if ($member->last_login): ?><li><small><label>最終<?php echo term('site.login'); ?>:</label> <?php echo site_get_time($member->last_login) ?></small></li><?php endif; ?>
			</ul>
<?php endif; ?>
		</div>
	</div>
<?php if (!$is_list): ?></div><?php endif; ?>
