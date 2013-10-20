<div id="main_post_box">
<?php echo render('_parts/post_comment', array(
	'u' => $u,
	'size' => 'M',
	'button_attrs' => array('class' => 'btn', 'id' => 'btn_timeline'),
	'textarea_attrs' => array('class' => 'span12 autogrow input_timeline'),
	'with_public_flag_selector' => true,
)); ?>
</div>
<?php echo render('_parts/timeline/list', array('list' => $list, 'is_next' => $is_next, 'mytimeline' => true)); ?>
