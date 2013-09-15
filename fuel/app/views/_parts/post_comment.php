<?php
$textarea_attrs_def = array('rows' => 1, 'class' => 'span12 autogrow', 'id' => 'textarea_comment');
$textarea_attrs     = (empty($textarea_attrs)) ? $textarea_attrs_def : array_merge($textarea_attrs_def, $textarea_attrs);

$button_attrs_def = array('class' => 'btn', 'id' => 'btn_comment');
$button_attrs     = (empty($button_attrs)) ? $button_attrs_def : array_merge($button_attrs_def, $button_attrs);

$class_name = 'member_img_box_s';
$img_size   = '30x30xc';
if (isset($size))
{
	$class_name = 'member_img_box_'.strtolower($size);
	$img_size   = Config::get('site.upload.types.img.types.m.sizes.'.strtoupper($size));
}
?>
<div class="commentPostBox">
	<div class="<?php echo $class_name; ?>">
		<?php echo img($u->get_image(), $img_size, 'member/'.$u->id, false, site_get_screen_name($u), true); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input"><?php echo Form::textarea('body', null, $textarea_attrs); ?></div>
				<div class="btnBox"><?php echo Form::button('btn_comment', '送信', $button_attrs); ?></div>
			</div>
		</div>
	</div>
</div>
