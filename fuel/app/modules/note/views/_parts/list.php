<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php echo \Config::get('term.note'); ?>がありません。
<?php else: ?>
<div id="article_list">
<?php foreach ($list as $id => $note): ?>
	<div class="article" id="article_<?php echo $id; ?>">
		<div class="header">
			<h4<?php if (!$note->is_published): ?> class="has_label"<?php endif; ?>>
				<?php echo Html::anchor('note/'.$id, strim($note->title, Config::get('site.view_params_default.list.trim_width.title'))); ?>
<?php if (!$note->is_published): ?>
				<?php echo render('_parts/label', array('name' => Config::get('term.draft'), 'attr' => 'label-inverse')); ?>
<?php endif; ?>
			</h4>
			<div class="list_subtitle">
<?php echo render('_parts/member_contents_box', array(
	'member' => $note->member,
	'model' => 'note',
	'id' => $note->id,
	'size' => 'M',
	'public_flag' => $note->public_flag,
	'public_flag_view_icon_only' => IS_SP,
	'date' => array('datetime' => $note->published_at ? $note->published_at : $note->updated_at)
)); ?>
<?php if (Auth::check() && $note->member_id == $u->id): ?>
				<div class="btn-group edit" id="btn_edit_<?php echo $id ?>">
					<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
						<span class="glyphicon glyphicon-edit"></span><span class="caret"></span>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li><?php echo Html::anchor('note/edit/'.$id, '<i class="glyphicon glyphicon-pencil"></i> 編集'); ?></li>
						<li><a href="#" onclick="delete_item('note/api/delete.json', <?php echo $id; ?>, '#article');return false;"><i class="glyphicon glyphicon-trash"></i> 削除</a></li>
					</ul>
				</div><!-- /btn-group -->

<?php endif; ?>
			</div>
		</div>
		<div class="body"><?php echo truncate_lines($note->body, Config::get('site.view_params_default.list.truncate_lines.body'), 'note/'.$id); ?></div>
<?php if (Module::loaded('album') && $images = \Note\Model_NoteAlbumImage::get_album_image4note_id($note->id, 4, array('id' => 'desc'))): ?>
<?php echo render('_parts/thumbnails', array('images' => array('list' => $images, 'additional_table' => 'note', 'size' => 'N_M', 'column_count' => 4))); ?>
<?php endif; ?>

<?php if ($note->is_published): ?>
<?php list($comments, $is_all_records, $all_comment_count) = \Note\Model_NoteComment::get_comments($id, \Config::get('site.view_params_default.list.comment.limit')); ?>
		<div class="comment_info">
			<small><i class="glyphicon glyphicon-comment"></i> <?php echo $all_comment_count; ?></small>
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
	'trim_width' => Config::get('site.view_params_default.list.comment.trim_width'),
	'date' => array('datetime' => $comment->created_at)
)); ?>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $note->member_id))): ?>
				<a class="btn btn-default btn-xs boxBtn btn_comment_delete" id="btn_comment_delete_<?php echo $comment->id ?>" href="#"><i class="glyphicon glyphicon-trash"></i></a>
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
<?php endforeach; ?>
</div>
<?php endif; ?>

<nav id="page-nav">
<?php
$uri = sprintf('note/api/list.html?page=%d', $page + 1);
if (!empty($member))   $uri .= '&member_id='.$member->id;
if (!empty($is_draft)) $uri .= '&is_draft='.$is_draft;
echo Html::anchor($uri, '');
?>
</nav>

<?php if (IS_API): ?></body></html><?php endif; ?>
