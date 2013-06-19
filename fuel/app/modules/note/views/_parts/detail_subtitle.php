<?php echo render('_parts/member_contents_box', array('member' => $note->member, 'date' => array('datetime' => $note->created_at, 'label' => '日時'))); ?>
<?php if (isset($u) && $u->id == $note->member_id): ?>
<div class="btn-group">
	<button data-toggle="dropdown" class="btn dropdown-toggle"><i class="icon-edit"></i> edit <span class="caret"/></button>
	<ul class="dropdown-menu">
		<li><?php echo Html::anchor('note/edit/'.$note->id, '<i class="icon-pencil"></i> 編集'); ?></li>
		<li><a href="#" onclick="delete_item('note/delete/<?php echo $note->id; ?>');return false;"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
