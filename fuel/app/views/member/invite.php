<div class="well">
<?php
$label_col_sm_size = 3;
?>
<?php echo form_open(true); ?>
<?php if (IS_ADMIN && conf('member.inviteFromAdmin.selectGroup.isEnabled', 'admin')): ?>
	<?php echo form_select($val, 'group', null, 12, $label_col_sm_size); ?>
<?php endif; ?>
	<?php echo form_input($val, 'email', null, 12, $label_col_sm_size); ?>
	<?php echo form_textarea($val, 'message', null, $label_col_sm_size); ?>
	<?php echo form_button(null, null, null, null, $label_col_sm_size, null, 'send'); ?>
<?php echo form_close(); ?>
</div><!-- well -->

<?php if ($member_pres): ?>
<h2 class="h3"><?php echo term('form.invited', 'site.email', 'site.list'); ?></h2>

<table class="table">
<tr>
	<th class="small"><?php echo term('form.delete'); ?></th>
	<th><?php echo term('site.email'); ?></th>
<?php if (IS_ADMIN && conf('member.inviteFromAdmin.selectGroup.isEnabled', 'admin')): ?>
	<th><?php echo term('member.group.view'); ?></th>
<?php endif; ?>
	<th><?php echo term('form.invite', 'site.datetime'); ?></th>
</tr>
<?php foreach ($member_pres as $id => $member_pre): ?>
<tr id="invite_<?php echo $id; ?>">
		<td><?php echo btn('form.delete', '#', 'js-ajax-delete', false, 'xs', null, array(
			'data-uri' => IS_ADMIN ? 'admin/member/invite/api/cancel/'.$id : 'member/invite/api/cancel/'.$id,
			'data-msg' => __('message_delete_confirm_for', array('label' => term('form.invite'))),
			'data-parent' => '#invite_'.$id,
		)); ?></td>
		<td><?php echo $member_pre->email; ?></td>
<?php if (IS_ADMIN && conf('member.inviteFromAdmin.selectGroup.isEnabled', 'admin')): ?>
		<td><?php echo \Site_Member::get_group_label($member_pre->group); ?></td>
<?php endif; ?>
		<td><?php echo site_get_time($member_pre->created_at) ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
