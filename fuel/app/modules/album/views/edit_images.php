<?php echo Form::open(array('action' => 'album/edit_images/'.$id, 'class' => 'form-stacked form-horizontal', 'method' => 'post', 'id' => 'form_edit_images')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
<?php echo Form::hidden('clicked_btn', '', array('id' => 'clicked_btn')); ?>

<?php if ($album_images): ?>
<div class="well">
	<h4><?php echo \Config::get('term.album_image'); ?>一括操作</h4>
	<div class="control-group">
		<?php echo Form::label('タイトル', 'name', array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::input('name', Input::post('name'), array('id' => 'form_name', 'class' => 'span8')); ?>
			<?php if ($val->error('name')): ?>
			<span class="help-inline error_msg"><?php echo $val->error('name')->get_message(); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo Form::label(Config::get('term.public_flag.label'), 'public_flag', array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::radio('public_flag', 99, is_null(Input::post('public_flag')), array('id' => 'form_public_flag_99')); ?>
			<?php echo Form::label('変更しない', 'public_flag_99'); ?>
		</div>
<?php $public_flags = Site_Form::get_public_flag_options() ; ?>
<?php foreach ($public_flags as $public_flag => $label): ?>
		<div class="controls">
			<?php echo Form::radio('public_flag', $public_flag, Input::post('public_flag') === $public_flag, array('id' => 'form_public_flag_'.$public_flag)); ?>
			<?php echo Form::label($label, 'public_flag_'.$public_flag); ?>
		</div>
<?php endforeach; ?>
<?php if ($val->error('public_flag')): ?>
		<span class="help-inline error_msg"><?php echo $val->error('public_flag')->get_message(); ?></span>
<?php endif; ?>
	</div>

	<div class="control-group">
		<?php echo Form::label('撮影日時', 'shot_at', array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::input('shot_at', Input::post('shot_at'), array('id' => 'form_shot_at', 'class' => 'span4')); ?>
			<?php if ($val->error('shot_at')): ?>
			<span class="help-inline error_msg"><?php echo $val->error('shot_at')->get_message(); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
		<?php echo Form::button('post', '<i class="icon-edit icon-white"></i> 一括編集', array('id' => 'submit_post', 'class' => 'btn btn-primary', 'type' => 'button')); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
		<?php echo Form::button('delete', '<i class="icon-trash icon-white"></i> 一括削除', array('id' => 'submit_delete', 'class' => 'btn btn-danger', 'type' => 'button')); ?>
		</div>
	</div>
</div>

<label class="checkbox"><?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?> 全て選択/解除</label>

<table id="album_image_list" class="table table-striped">
<tr>
	<th class="formParts">対象選択</th>
	<th><?php echo \Config::get('term.album_image'); ?></th>
	<th>タイトル</th>
	<th>公開範囲</th>
	<th>撮影日時</th>
</tr>
<?php foreach ($album_images as $album_image): ?>
<tr>
	<td class="formParts"><?php echo Form::checkbox('album_image_ids[]', $album_image->id, in_array($album_image->id, $album_image_ids), array('class' => 'album_image_ids')); ?></td>
	<td class="image"><?php echo img((isset($album_image->file->name)) ? $album_image->file->name : '', '80x80', 'album/image/'.$album_image->id); ?></td>
	<td class="span5"><?php echo $album_image->name; ?></td>
<?php list($name, $icon, $btn_color) = get_public_flag_label($album_image->public_flag); ?>
	<td><span class="btn btn-mini<?php echo $btn_color; ?>"><?php echo $icon.$name; ?></span></td>
	<td><?php if (isset($album_image->file->shot_at)) echo date('Y年n月j日 H:i', strtotime($album_image->file->shot_at)); ?></td>
</tr>
<?php endforeach; ?>
</table>

<label class="checkbox"><?php echo Form::checkbox('album_image_all', '', array('class' => 'album_image_all')); ?> 全て選択/解除</label>
<?php else: ?>
	<p><?php echo \Config::get('term.album_image'); ?>がありません。</p>
<?php endif; ?>

<?php echo Form::close(); ?>
