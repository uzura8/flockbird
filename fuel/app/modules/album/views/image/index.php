<p><?php echo img($album_image->get_image(), '600x600', '', true); ?></p>

<hr />

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
<?php if (isset($current_user) && in_array($current_user->id, array($comment->member_id, $album_image->album->member_id))): ?>
	<a class="btn btn-mini boxBtn" href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('album/image_comment/delete/%d?%s=%s', $comment->id, Config::get('security.csrf_token_key'), Security::fetch_token())); ?>';});"><i class="icon-trash"></i></a>
<?php endif ; ?>
</div>
<?php endforeach; ?>

<?php if (Auth::check()): ?>

<div class="commentBox">
	<div class="member_img_box_s">
		<?php echo img($current_user->get_image(), '30x30', 'member/'.$current_user->id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$current_user->id, $current_user->name); ?></b>
<?php echo Form::open('album/image/comment/create/'.$album_image->id) ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); ?>
		 <div class="input"><?php echo Form::textarea('body', null, array('cols' => 60, 'rows' => 1, 'class' => 'input-xlarge')); ?></div>
		 <div class="input"><?php echo Form::submit(array('name' => 'submit', 'value' => 'submit', 'class' => 'btn btn-mini')); ?></div>
<?php echo Form::close() ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
