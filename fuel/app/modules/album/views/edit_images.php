<?php if ($album_images): ?>
<?php echo form_open(false, false, array(), array(), '', false); ?>
<div class="well">
	<h4><?php echo \Config::get('term.album_image'); ?>一括操作</h4>
	<?php echo Form::hidden('clicked_btn', '', array('id' => 'clicked_btn')); ?>
	<?php echo form_input($val, 'name', 'タイトル'); ?>
<?php if (!$is_disabled_to_update_public_flag): ?>
	<?php echo form_radio_public_flag($val, 99, true); ?>
<?php endif; ?>
	<?php echo form_input($val, 'shot_at', '撮影日時', null, false, 'span4'); ?>
	<?php echo form_button('<i class="ls-icon-edit icon-white"></i> 一括編集', 'button', 'post', array('id' => 'submit_post', 'class' => 'btn btn-default btn-primary')); ?>
	<?php echo form_button('<i class="icon-trash icon-white"></i> 一括削除', 'button', 'delete', array('id' => 'submit_delete', 'class' => 'btn btn-default btn-danger')); ?>
</div><!-- well -->

<label class="checkbox"><?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?> 全て選択/解除</label>

<table id="album_image_list" class="table table-striped">
<tr>
	<th class="formParts span2">対象選択</th>
	<th class="span3"><?php echo \Config::get('term.album_image'); ?></th>
	<th>タイトル</th>
	<th class="span2">公開範囲</th>
	<th class="span3">撮影日時</th>
</tr>
<?php foreach ($album_images as $album_image): ?>
<tr>
	<td class="formParts"><?php echo Form::checkbox('album_image_ids[]', $album_image->id, in_array($album_image->id, $album_image_ids), array('class' => 'album_image_ids')); ?></td>
	<td class="image"><?php echo img((isset($album_image->file)) ? $album_image->file : '', '80x80', 'album/image/'.$album_image->id); ?></td>
	<td class="span5"><?php echo $album_image->name; ?></td>
<?php list($name, $icon, $btn_color) = get_public_flag_label($album_image->public_flag); ?>
	<td><span class="btn btn-default btn-default btn-xs<?php echo $btn_color; ?>"><?php echo sprintf('%s%s', $icon, IS_SP ? '' : $name); ?></span></td>
	<td><?php if (isset($album_image->file->shot_at)) echo date('Y年n月j日 H:i', strtotime($album_image->file->shot_at)); ?></td>
</tr>
<?php endforeach; ?>
</table>

<label class="checkbox"><?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?> 全て選択/解除</label>
<?php else: ?>
	<p><?php echo \Config::get('term.album_image'); ?>がありません。</p>
<?php endif; ?>

<?php echo form_close(); ?>
