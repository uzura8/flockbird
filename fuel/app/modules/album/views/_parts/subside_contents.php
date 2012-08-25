<div id="album_image_comment"></div>

<h3 id="comments">Comments</h3>

<div id="loading_list"></div>
<div id="comment_list"></div>

<?php if (Auth::check()): ?>
<div class="commentBox">
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
