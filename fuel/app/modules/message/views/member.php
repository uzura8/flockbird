<div id="article_list" data-type="ajax" data-message_type="<?php echo $type; ?>" data-id="<?php echo $related_id; ?>"></div>

<div id="main_post_box">
<?php echo render('_parts/comment/post', array(
	'size' => 'M',
	'button_attrs' => array('id' => 'btn_message', 'class' => 'btn btn-default btn_comment'),
	'textarea_attrs' => array('class' => 'form-control autogrow input_message'),
	'with_uploader' => conf('uploadImages.isEnabled', 'message'),
)); ?>
</div>
