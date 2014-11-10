<?php if ($album_images): ?>
<?php echo form_open(false, false, array(), array(), '', false); ?>
<div class="well">
	<h4><?php echo term('album_image', 'form.operation_all'); ?></h4>
	<?php echo Form::hidden('clicked_btn', '', array('id' => 'clicked_btn')); ?>
	<?php echo form_input($val, 'name', ''); ?>
<?php if (!$is_disabled_to_update_public_flag): ?>
	<?php echo form_public_flag($val, 99, false, 2, true); ?>
<?php endif; ?>
	<?php echo form_input_datetime($val, 'shot_at', '', null, 5); ?>
	<?php echo form_button('form.edit_all', 'button', 'post', array('id' => 'submit_post')); ?>
	<?php echo form_button('form.delete_all', 'button', 'delete', array('id' => 'submit_delete', 'class' => 'btn btn-default btn-danger')); ?>
</div><!-- well -->

<label class="checkbox"><?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?> 全て選択/解除</label>

<table id="album_image_list" class="table table-striped">
<tr>
	<th class="formParts span2">対象選択</th>
	<th class="span3"><?php echo term('album_image'); ?></th>
	<th><?php echo term('site.title'); ?></th>
	<th class="span2"><?php echo term('public_flag.label'); ?></th>
	<th class="span3">撮影日時</th>
</tr>
<?php foreach ($album_images as $album_image): ?>
<tr>
	<td class="formParts"><?php echo Form::checkbox('album_image_ids[]', $album_image->id, in_array($album_image->id, $album_image_ids), array('class' => 'album_image_ids')); ?></td>
	<td class="image"><?php echo img($album_image->get_image(), 'S', 'album/image/'.$album_image->id); ?></td>
	<td class="span5"><?php echo $album_image->name; ?></td>
	<td><?php echo get_public_flag_label($album_image->public_flag, false, 'label', true); ?></td>
	<td><?php if (isset($album_image->shot_at)) echo date('Y年n月j日 H:i', strtotime($album_image->shot_at)); ?></td>
</tr>
<?php endforeach; ?>
</table>

<label class="checkbox"><?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?> 全て選択/解除</label>
<?php else: ?>
	<p><?php echo term('album_image'); ?>がありません。</p>
<?php endif; ?>

<?php echo form_close(); ?>
