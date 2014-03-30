<?php $label_col_size = 3; ?>
<div class="well form-horizontal">
	<div class="form-group">
		<label class="col-sm-<?php echo $label_col_size; ?> control-label"><?php echo term('site.email'); ?></label>
		<div class="col-sm-<?php echo 12 - $label_col_size; ?> form-text">
<?php if ($u->member_auth->email): ?>
			<?php echo $u->member_auth->email; ?>
<?php else: ?>
			<span class="text-danger"><?php echo term('unset'); ?></span>
<?php endif; ?>
			<?php echo Html::anchor('member/setting/email', icon('edit').' '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-<?php echo $label_col_size; ?> control-label"><?php echo term('site.password'); ?></label>
		<div class="col-sm-<?php echo 12 - $label_col_size; ?> form-text">
<?php if ($u->member_auth->password): ?>
			<span class="text-muted"><?php echo term('site.set_already'); ?></span>
<?php else: ?>
			<span class="text-danger"><?php echo term('unset'); ?></span>
<?php endif; ?>
			<?php echo Html::anchor('member/setting/password', icon('edit').' '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
		</div>
	</div>
<?php if (is_enabled('timeline')): ?>
	<div class="form-group">
		<label class="col-sm-<?php echo $label_col_size; ?> control-label"><?php echo term(array('timeline', 'site.view', 'site.setting')); ?></label>
		<div class="col-sm-<?php echo 12 - $label_col_size; ?> form-text">
			<?php echo Form_MemberConfig_Timeline::get_viewType_options($u->timeline_viewType); ?>
			<?php echo Html::anchor('member/setting/timeline_viewtype', icon('edit').' '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
		</div>
	</div>
<?php endif; ?>
</div>

<div class="list-group">
	<?php echo Html::anchor('member/leave', term('site.leave'), array('class' => 'list-group-item list-group-item-danger')); ?>
</div>
