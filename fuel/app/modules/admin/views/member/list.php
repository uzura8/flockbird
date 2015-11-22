<?php if (!$list): ?>
<?php echo term('member.view'); ?>の<?php echo term('site.registration'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>

<table class="table table-hover table-responsive">
<tr>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th class="small"><?php echo term('site.detail'); ?></th>
	<th class="small"><?php echo term('form.delete'); ?></th>
<?php if (conf('member.group.display.isEnabled', 'admin')): ?>
	<th class="u-sm"><?php echo term('member.group.view'); ?></th>
<?php endif; ?>
	<th><?php echo term('member.name'); ?></th>
	<th><?php echo term('member.sex.label'); ?></th>
	<th class="datetime"><?php echo term('site.registration', 'site.datetime'); ?></th>
	<th class="datetime"><?php echo term('site.last', 'site.login'); ?></th>
</tr>
<?php foreach ($list as $id => $member): ?>
<tr id="<?php echo $member->id; ?>">
	<td class="small"><?php echo $member->id; ?></td>

<?php 	if (check_acl($uri = 'admin/member/detail')): ?>
	<td class="small"><?php echo btn('site.detail', $uri.'/'.$member->id, '', false, 'xs'); ?></td>
<?php 	else: ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php 	endif; ?>

<?php 	if (check_acl($uri = 'admin/member/delete')): ?>
	<td class="small"><?php echo btn('form.delete', '#', 'js-simplePost', false, 'xs', null, array(
		'data-destination' => Uri::string_with_query(),
		'data-uri' => $uri.'/'.$member->id,
		'data-msg' => term('common.force', 'site.left').'します。よろしいですか？',
	)); ?></td>
<?php 	else: ?>
	<td class="small"><?php echo symbol('noValue'); ?></td>
<?php 	endif; ?>

<?php // group edit ?>
<?php 	if (conf('member.group.display.isEnabled', 'admin')): ?>
<?php 		if (conf('member.group.edit.isEnabled', 'admin')
						&& check_acl('admin/member/group/edit', 'POST')
						&& \Admin\Site_AdminUser::check_editable_member_group(\Auth::get_groups(), \Site_Member::get_group_key($member->group))): ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdow_group_'.$member->id,
	'class' => array('dropdown', 'boxBtn'),
);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('admin/member/group/api/menu/%d.html', $member->id),
	'data-menu' => '#menu_'.$member->id,
	'data-loaded' => 0,
	'data-get_data' => json_encode(array('page' => Pagination::instance('mypagination')->current_page)),
);
?>
	<td class="u-sm"><?php echo btn_dropdown($member->display_group(), array(), true, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, true); ?></td>
<?php 		else: ?>
	<td class="u-sm"><?php echo $member->display_group(); ?></td>
<?php 		endif; ?>
<?php 	endif; ?>
<?php // group edit ?>

	<td><?php echo Html::anchor('admin/member/'.$member->id, $member->name); ?></td>
	<td><?php echo (isset($member->sex) && strlen($member->sex)) ?
				\Site_Form::get_form_options4config('term.member.sex.options', $member->sex) : symbol('noValue'); ?></td>
	<td class="fs12"><?php echo site_get_time($member->created_at, 'relative', 'Y/m/d H:i'); ?></td>
	<td class="fs12"><?php echo site_get_time($member->last_login, 'relative', 'Y/m/d H:i'); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo Pagination::instance('mypagination')->render(); ?>
<?php endif; ?>
