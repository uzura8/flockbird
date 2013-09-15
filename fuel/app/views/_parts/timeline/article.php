<div class="timelineBox" id="timelineBox_<?php echo $timeline->id; ?>" data-id="<?php echo $timeline->id; ?>">
<?php echo render('_parts/member_contents_box', array(
	'member' => $timeline_data->member,
	'size' => 'M',
	'date' => array('datetime' => $timeline->created_at),
	'content' => $timeline_data->body,
	'trim_width' => empty($trim_width) ? 0 : $trim_width,
)); ?>
<?php if (Auth::check() && $timeline->member_id == $u->id && \Timeline\Site_Util::check_is_editable($timeline_data->type)): ?>
<a class="btn btn-mini boxBtn btn_timeline_delete" data-id="<?php echo $timeline->id; ?>" id="btn_timeline_delete_<?php echo $timeline->id; ?>" href="#"><i class="icon-trash"></i></a>
<?php endif ; ?>
</div>
<?php /*
	<div class="article" id="article_<?php echo $id; ?>">
		<div class="header">
			<div class="list_subtitle">
<?php echo render('_parts/member_contents_box', array(
	'member' => $timeline->member,
	'model' => 'timeline',
	'id' => $timeline->id,
	'public_flag' => $timeline->public_flag,
	'public_flag_view_icon_only' => IS_SP,
	'date' => array('datetime' => $timeline->created_at)
)); ?>
<?php if (Auth::check() && $note->member_id == $u->id): ?>
				<div class="btn-group edit" id="btn_edit_<?php echo $id ?>">
					<button data-toggle="dropdown" class="btn btn-mini dropdown-toggle"><i class="ls-icon-edit"></i><span class="caret"></span></button>
					<ul class="dropdown-menu pull-right">
						<li><?php echo Html::anchor('note/edit/'.$id, '<i class="icon-pencil"></i> 編集'); ?></li>
						<li><a href="#" onclick="delete_item('note/api/delete.json', <?php echo $id; ?>, '#article');return false;"><i class="icon-trash"></i> 削除</a></li>
					</ul>
				</div><!-- /btn-group -->
<?php endif; ?>
			</div>
		</div>
		<h4>
			<?php echo Html::anchor('note/'.$id, strim($note->title, Config::get('note.articles.trim_width.title'))); ?>
		</h4>
		<div class="body">
			<div><?php echo nl2br(strim($timeline->body, Config::get('timeline.articles.trim_width.body'))) ?></div>
<?php if (mb_strlen($timeline->body) > Config::get('timeline.articles.trim_width.body')): ?>
			<div class="bodyMore"><?php echo Html::anchor('timeline/'.$id, 'もっとみる'); ?></div>
<?php endif; ?>
		</div>
<?php if ($images = \Note\Model_NoteAlbumImage::get_album_image4note_id($note->id, 4, array('id' => 'desc'))): ?>
<?php echo render('_parts/thumbnails', array('images' => $images)); ?>
<?php endif; ?>

<?php list($comments, $is_all_records, $all_comment_count) = \Note\Model_NoteComment::get_comments($id, \Config::get('site.record_limit.default.comment.s')); ?>
		<div class="comment_info">
			<small><i class="icon-comment"></i> <?php echo $all_comment_count; ?></small>
<?php if (Auth::check()): ?>
			<small><?php echo Html::anchor('note/'.$id.'?write_comment=1#comments', 'コメントする'); ?></small>
<?php endif; ?>
		</div>

<?php if ($comments): ?>
		<div class="list_comment">
<?php foreach ($comments as $comment): ?>
			<div class="commentBox" id="commentBox_<?php echo $comment->id ?>">
<?php echo render('_parts/member_contents_box', array(
	'member' => $comment->member,
	'content' => $comment->body,
	'trim_width' => Config::get('note.articles.comment.trim_width'),
	'date' => array('datetime' => $comment->created_at)
)); ?>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $note->member_id))): ?>
				<a class="btn btn-mini boxBtn btn_comment_delete" id="btn_comment_delete_<?php echo $comment->id ?>" href="#"><i class="icon-trash"></i></a>
<?php endif; ?>
			</div>
<?php endforeach; ?>
<?php if (!$is_all_records): ?>
			<div class="listMoreBox"><?php echo Html::anchor('note/'.$id.'?all_comment=1#comments', 'もっとみる'); ?></div>
<?php endif; ?>
		</div>
<?php endif; ?>
<?php endif; ?>
	</div>
*/ ?>
