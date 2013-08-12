<div class="well">
	<div><?php echo img($u->get_image(), '180x180', '', true); ?></div>
	<?php echo form_open(false, true, array('action' => 'member/profile/edit_image', 'class' => 'form-stacked', 'method' => 'post')); ?>
		<?php echo form_file('image'); ?>
		<?php echo form_button(); ?>
<?php if (Auth::check() && $u->file_id): ?>
		<?php echo form_anchor('#', '<i class="ls-icon-delete"></i> 削除', array(
			'class' => 'btn btn-white btn-danger boxBtn',
			'onclick' => "delete_item('member/profile/delete_image');return false;"
		)); ?>
<?php endif; ?>
	<?php echo form_close(); ?>
</div><!-- well -->
