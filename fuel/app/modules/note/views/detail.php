<p><?php echo nl2br($note->body) ?></p>
<hr />

<?php if ($comments): ?>
<h3 id="comments">Comments</h3>
<?php foreach ($comments as $comment): ?>
<div class="commentBox">
	<div class="member_img_box_s">
		<?php echo img($comment->member->get_image(), '30x30', 'member/'.$comment->member_id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$comment->member_id, $comment->member->name); ?></b>
				<?php echo $comment->body ?>
			</div>
			<small><?php echo site_get_time($comment->created_at); ?></small>
		</div>
	</div>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $note->member_id))): ?>
	<a class="btn btn-mini boxBtn" href="#" onclick="delete_item('note/comment/delete/<?php echo $comment->id; ?>');return false;"><i class="icon-trash"></i></a>
<?php endif ; ?>
</div>
<?php endforeach; ?>
<?php endif ; ?>

<?php if (Auth::check()): ?>

<div class="commentBox">
	<div class="member_img_box_s">
		<?php echo img($u->get_image(), '30x30', 'member/'.$u->id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
<?php echo Form::open('note/comment/create/'.$note->id) ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
		 <div class="input"><?php echo Form::textarea('body', null, array('cols' => 60, 'rows' => 1, 'class' => 'input-xlarge')); ?></div>
		 <div class="input"><?php echo Form::submit(array('name' => 'submit', 'value' => 'submit', 'class' => 'btn btn-mini')); ?></div>
<?php echo Form::close() ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
