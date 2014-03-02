<div class="well">
	<div class="row">
		<div class="col-md-4">
			<div class="imgBox">
				<?php echo img($member->get_image(), '180x180xc', '', true, site_get_screen_name($u), true); ?>
		<?php if ($is_mypage && $member->file_id): ?>
				<?php echo Html::anchor('#', '<i class="glyphicon glyphicon-trash"></i> '.term('form.delete'), array(
					'class' => 'btn btn-default btn-sm delete_image',
					'onclick' => "delete_item('member/profile/image/unset');return false;",
				)); ?>
		<?php endif; ?>
			</div>
<?php if ($is_mypage): ?>
<?php echo form_open(false, true, array('action' => 'member/profile/image/edit')); ?>
<?php echo form_file('image'); ?>
<?php echo Form::button('submit', term('form.submit'), array('type'  => 'submit', 'class' => 'btn btn-default btn-primary')); ?>
<?php //echo form_close(); ?>
<?php echo Form::close(); ?>
<?php endif; ?>
		</div>
		<div class="col-md-8">
			<div class="row"><h3><?php echo site_get_screen_name($member); ?></h3></div>
			<?php echo render('member/profile/_parts/values', array('member_profiles' => $member_profiles, 'access_from' => $access_from, 'display_type' => 1)); ?>
		</div>
	</div>
</div><!-- well -->
<?php if (Module::loaded('album') && Config::get('site.upload.types.img.types.m.save_as_album_image')): ?>
<?php echo render('album::image/_parts/list', array('list' => $images, 'is_simple_view' => true, 'is_setting_profile_image' => true)); ?>
<?php endif; ?>
