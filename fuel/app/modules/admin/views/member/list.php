<?php if (!$list): ?>
<?php echo term('member.view'); ?>の<?php echo term('site.registration'); ?>がありません。
<?php else: ?>
<?php echo Pagination::instance('mypagination')->render(); ?>

<table class="table table-hover table-responsive">
<tr>
	<th class="small"><?php echo term('site.id'); ?></th>
	<th class="small"><?php echo term('site.detail'); ?></th>
	<th class="small"><?php echo term('form.delete'); ?></th>
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
