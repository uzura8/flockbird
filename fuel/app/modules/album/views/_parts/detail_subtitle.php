<?php echo render('_parts/member_contents_box', array(
	'member'      => $album->member,
	'id'          => $album->id,
	'public_flag' => $album->public_flag,
	'model'       => 'album',
	'date'        => array('datetime' => $album->created_at, 'label' => '日時')
)); ?>
<?php if (isset($u) && $u->id == $album->member_id): ?>
<div class="btn-group edit">
	<button data-toggle="dropdown" class="btn dropdown-toggle"><i class="icon-edit"></i> edit <span class="caret"/></button>
	<ul class="dropdown-menu">
		<li><?php echo Html::anchor('album/edit/'.$album->id, '<i class="icon-pencil"></i> 編集'); ?></li>
		<li><a href="#" onclick="delete_item('album/delete/<?php echo $album->id; ?>');return false;"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
