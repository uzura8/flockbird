<?php
$textarea_attrs_def = array('rows' => 1, 'class' => 'form-control autogrow', 'id' => 'textarea_comment');
$textarea_attrs     = (empty($textarea_attrs)) ? $textarea_attrs_def : array_merge($textarea_attrs_def, $textarea_attrs);

$button_attrs_def = array(
	'class' => 'js-ajax-postComment',
	'id' => 'btn_comment',
	'data-textarea' => '#'.$textarea_attrs['id'],
);
$button_attrs     = (empty($button_attrs)) ? $button_attrs_def : array_merge($button_attrs_def, $button_attrs);
$button_attrs['class'] .= ' pull-right';

$parts_attrs_def = array('class' => 'commentPostBox');
if (!empty($id)) $parts_attrs_def['id'] = 'commentPostBox_'.$id;
$parts_attrs     = empty($parts_attrs) ? $parts_attrs_def : array_merge($parts_attrs_def, $parts_attrs);
$parts_attrs_string = Util_Array::conv_array2attr_string($parts_attrs);

$size = empty($size) ? 'S' : strtoupper($size);
?>
<div<?php if ($parts_attrs_string): ?> <?php echo $parts_attrs_string; ?><?php endif; ?>>
	<div class="row member_contents">
		<div class="col-xs-1"><?php echo img($u->get_image(), $size, 'member/'.$u->id, false, site_get_screen_name($u), true, true); ?></div>
		<div class="col-xs-11">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input"><?php echo Form::textarea('body', null, $textarea_attrs); ?></div>
<?php if (!empty($with_uploader)): ?>
<?php if (!isset($files)) $files = array(); ?>
				<div class="upload hidden">
				<?php echo form_upload_files($files, true, false, 'S', $uploader_selects); ?>
				</div>
<?php endif; ?>
				<div class="clearfix">
<?php if (!empty($with_uploader)): ?>
					<?php echo btn('form.add_picture', null, 'display_upload_form', true, 'ms', null, array('class' => 'pull-left'), null, 'button', 'display_fileinput-button'); ?>
<?php endif; ?>
					<?php echo btn('form.submit', '#', 'btn_comment', true, null, null, $button_attrs, null, null, 'btn_comment'); ?>
<?php if (!empty($with_public_flag_selector)): ?>
<?php
if (!isset($public_flag)) $public_flag = conf('public_flag.default');
$data = array(
	'id' => $u->id,
	'is_use_in_form' => true,
	'public_flag' => $public_flag,
	'view_icon_only' => IS_SP,
	'is_mycontents' => true,
	'parent_box_additional_class' => 'pull-right',
);
if (!empty($uri_for_update_public_flag)) $data['post_uri'] = $uri_for_update_public_flag;
?>
					<?php echo render('_parts/public_flag_selecter', $data); ?>
					<?php echo Form::hidden('public_flag', $public_flag); ?>
<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
