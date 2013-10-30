<?php echo render('_parts/member_contents_box', array(
	'member'      => $note->member,
	'id'          => $note->id,
	'public_flag' => $note->public_flag,
	'public_flag_view_icon_only' => IS_SP,
	'model'       => 'note',
	'size' => 'M',
	'date'        => array('datetime' => $note->published_at ? $note->published_at : $note->updated_at, 'label' => $note->published_at ? '日時' : '更新日時')
)); ?>
<?php if (isset($u) && $u->id == $note->member_id): ?>
<div class="btn-group edit">
	<?php echo render('_parts/button_edit'); ?>
	<ul class="dropdown-menu pull-right">
<?php if (!$note->is_published): ?>
		<li><?php echo Html::anchor(sprintf('note/publish/%d%s', $note->id, get_csrf_query_str()), '公開する'); ?></li>
<?php endif; ?>
		<li><?php echo Html::anchor('note/edit/'.$note->id, '<i class="icon-pencil"></i> 編集'); ?></li>
		<li><a href="#" onclick="delete_item('note/delete/<?php echo $note->id; ?>');return false;"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
