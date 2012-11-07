<div class="img_box">
	<?php echo ($before_id) ? Html::anchor('album/image/detail/'.$before_id, '<i class="icon-backward"></i><br>前へ', array('class' => 'btn btn-mini backward')) : ''; ?>
	<?php echo img($album_image->get_image(), '600x600', '', true); ?>
	<?php echo ($after_id) ? Html::anchor('album/image/detail/'.$after_id, '<i class="icon-forward"></i><br>次へ', array('class' => 'btn btn-mini forward')) : ''; ?>
</div>

<hr />

<div id="album_image_comment"></div>
<?php if (Auth::check() || $album_image->album_image_comment): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div id="loading_list"></div>
<div id="comment_list">
<?php foreach ($album_image->album_image_comment as $comment): ?>
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
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $album_image->album->member_id))): ?>
		<a class="btn btn-mini boxBtn" href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('album/image_comment/delete/%d?%s=%s', $comment->id, Config::get('security.csrf_token_key'), Util_security::get_csrf())); ?>';});"><i class="icon-trash"></i></a>
<?php endif ; ?>
	</div>
<?php endforeach; ?>
</div>

<?php if (Auth::check()): ?>
<div class="commentPostBox">
	<div class="member_img_box_s">
		<?php echo img($u->get_image(), '30x30', 'member/'.$u->id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input"><?php echo Form::textarea('body', null, array('rows' => 1, 'class' => 'span12 autogrow', 'id' => 'input_album_image_comment')); ?></div>
				<div class="input"><a href="javaScript:void(0);" class="btn btn-mini" id="btn_album_image_comment_create">送信</a></div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
