<p><?php echo nl2br($note->body) ?></p>
<hr />

<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div id="comment_list">
<?php echo render('_parts/comment/list', array('u' => $u, 'parent' => $note, 'comments' => $comments, 'is_all_records' => $is_all_records)); ?>
</div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment', array('u' => $u, 'textarea_attrs' => array('class' => 'span12 autogrow'))); ?>
<?php endif; ?>
