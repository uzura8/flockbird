<?php echo render('_parts/post_comment', array('u' => $u, 'size' => 'M', 'textarea_attrs' => array('class' => 'span12 autogrow input_timeline'))); ?>
<div id="article_list">
<?php echo render('_parts/timeline/list', array('list' => $list, 'is_next' => $is_next)); ?>
</div>
