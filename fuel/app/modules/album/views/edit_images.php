<?php if ($album_images): ?>
<?php echo form_open(false, false, array(), array(), '', false); ?>
<div class="well">
	<?php echo Form::hidden('clicked_btn', '', array('id' => 'clicked_btn')); ?>
	<?php echo form_input($val, 'name', ''); ?>
<?php if (!$is_disabled_to_update_public_flag): ?>
	<?php echo form_public_flag($val, 99); ?>
<?php endif; ?>
	<?php echo form_input_datetime($val, 'shot_at', '', null, 5); ?>
<?php if (is_enabled_map('edit_images', 'album')): ?>
	<?php echo form_map($val); ?>
<?php endif; ?>
	<?php echo form_button('form.edit_all', 'button', 'post', array('id' => 'submit_post')); ?>
	<?php echo form_button('form.delete_all', 'button', 'delete', array('id' => 'submit_delete', 'class' => 'btn btn-default btn-danger')); ?>
</div><!-- well -->

<label class="checkbox-inline">
	<?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?>
	<?php echo t('form.toggle_all_select_none'); ?>
</label>

<table id="album_image_list" class="table table-striped image_table">
<tr>
	<th class="formParts span2"><?php echo t('common.exec_targets'); ?></th>
	<th class="span3"><?php echo t('album.image.view'); ?></th>
	<th><?php echo t('album.image.name'); ?></th>
	<th class="span2"><?php echo t('public_flag.label'); ?></th>
	<th class="span3"><?php echo t('site.shot_at'); ?></th>
</tr>
<?php foreach ($album_images as $album_image): ?>
<tr>
	<td class="formParts"><?php echo Form::checkbox('album_image_ids[]', $album_image->id, in_array($album_image->id, $album_image_ids), array('class' => 'album_image_ids')); ?></td>
	<td class="image"><?php echo img($album_image->get_image(), 'S', 'album/image/'.$album_image->id); ?></td>
	<td class="span5"><?php echo $album_image->name; ?></td>
	<td><?php echo get_public_flag_label($album_image->public_flag, false, 'label', true); ?></td>
	<td><?php if (isset($album_image->shot_at)) echo site_get_time($album_image->shot_at, 'normal'); ?></td>
</tr>
<?php endforeach; ?>
</table>

<label class="checkbox-inline">
	<?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?>
	<?php echo t('form.toggle_all_select_none'); ?>
</label>

<?php else: ?>
<p><?php echo __('message_no_data_for', array('label' => t('album.image.plural'))); ?></p>
<?php endif; ?>

<?php echo form_close(); ?>
