<div class="well">
	<div class="imgBox">
		<?php echo img($u->get_image(), '180x180xc', '', true, site_get_screen_name($u), true); ?>
<?php if ($u->file_id): ?>
		<?php echo Html::anchor('#', '<span class="glyphicon glyphicon-trash"></span> 削除', array(
			'class' => 'btn btn-default btn-sm delete_image',
			'onclick' => "delete_item('member/profile/delete_image');return false;",
		)); ?>
<?php endif; ?>
	</div>
<?php if (Auth::check()): ?>
<?php echo form_open(false, true, array('action' => 'member/profile/edit_image')); ?>
<?php echo form_file('image'); ?>
<?php echo Form::button('submit', '送信', array('type'  => 'submit', 'class' => 'btn btn-default btn-primary')); ?>
<?php //echo form_close(); ?>
<?php echo Form::close(); ?>
<?php endif; ?>
</div><!-- well -->
<?php if (Config::get('site.upload.types.img.types.m.save_as_album_image')): ?>
<?php echo render('_parts/album_images', array('list' => $images, 'is_simple_view' => true, 'is_setting_profile_image' => true)); ?>
<?php endif; ?>
