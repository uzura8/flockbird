<div class="member_img_box_s">
	<?php echo img($note->member->get_image(), '30x30', 'member/'.$note->member->id); ?>
	<div class="content">
		<div class="main">
			<b class="fullname"><?php echo Html::anchor('member/'.$note->member->id, $note->member->name); ?></b>
		</div>
		<small>
		日時: <?php echo date('Y年n月j日 H:i', strtotime($note->created_at)) ?>
		(<?php echo Date::time_ago(strtotime($note->created_at)) ?>)
		</small>
	</div>
</div>

<?php if (isset($u) && $u->id == $note->member_id): ?>
<div class="btn-group">
	<button data-toggle="dropdown" class="btn dropdown-toggle"><i class="icon-edit"></i> edit <span class="caret"/></button>
	<ul class="dropdown-menu">
		<li><?php echo Html::anchor('note/edit/'.$note->id, '<i class="icon-pencil"></i> 編集'); ?></li>
		<li><a href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('note/delete/%d?%s=%s', $note->id, Config::get('security.csrf_token_key'), Util_security::get_csrf())); ?>';});"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
