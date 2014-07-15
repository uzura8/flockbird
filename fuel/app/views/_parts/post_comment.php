<?php
$textarea_attrs_def = array('rows' => 2, 'class' => 'form-control autogrow', 'id' => 'textarea_comment');
$textarea_attrs     = (empty($textarea_attrs)) ? $textarea_attrs_def : array_merge($textarea_attrs_def, $textarea_attrs);

$button_attrs_def = array('class' => '', 'id' => 'btn_comment');
$button_attrs     = (empty($button_attrs)) ? $button_attrs_def : array_merge($button_attrs_def, $button_attrs);
$button_attrs['class'] .= ' pull-right';

$parts_attrs_def = array('class' => 'commentPostBox');
$parts_attrs     = empty($parts_attrs) ? $parts_attrs_def : array_merge($parts_attrs_def, $parts_attrs);

$size = empty($size) ? 'S' : strtoupper($size);
if (IS_SP) $size = Site_Util::convert_img_size_down($size) ?: $size;
$class_name = 'member_img_box_'.strtolower($size);
$img_size   = conf('upload.types.img.types.m.sizes.'.$size);
?>
<div<?php if (!empty($parts_attrs['class'])): ?> class="<?php echo $parts_attrs['class']; ?>"<?php endif; ?><?php if (!empty($parts_attrs['id'])): ?> id="<?php echo $parts_attrs['id']; ?>"<?php endif; ?>>
	<div class="<?php echo $class_name; ?>">
		<?php echo img($u->get_image(), $img_size, 'member/'.$u->id, false, site_get_screen_name($u), true); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input"><?php echo Form::textarea('body', null, $textarea_attrs); ?></div>
<?php if (!empty($with_uploader)): ?>
<?php if (!isset($files)) $files = array(); ?>
				<div class="upload hidden">
				<?php echo form_upload_files($files, false, true, false, 'S', $uploader_selects); ?>
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
