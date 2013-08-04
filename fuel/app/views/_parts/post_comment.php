<?php
$textarea_attrs_def = array('rows' => 1, 'class' => 'span12 autogrow', 'id' => 'textarea_comment');
$textarea_attrs     = (empty($textarea_attrs)) ? $textarea_attrs_def : array_merge($textarea_attrs_def, $textarea_attrs);

$button_attrs_def = array('class' => 'btn', 'id' => 'btn_comment');
$button_attrs     = (empty($button_attrs)) ? $button_attrs_def : array_merge($button_attrs_def, $button_attrs);
?>
<div class="commentPostBox">
	<div class="member_img_box_s">
		<?php echo img($u->get_image(), '30x30', 'member/'.$u->id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input"><?php echo Form::textarea('body', null, $textarea_attrs); ?></div>
				<div class="btnBox"><?php echo Form::button('btn_comment', '送信', $button_attrs); ?></div>
			</div>
		</div>
	</div>
</div>
