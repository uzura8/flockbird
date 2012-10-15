
<div class="member_img_box_s">
	<?php echo img($album_image->album->member->get_image(), '30x30', 'member/'.$album_image->album->member_id); ?>
	<div class="content">
		<div class="main">
			<b class="fullname"><?php echo Html::anchor('member/'.$album_image->album->member_id, $album_image->album->member->name); ?></b>
		</div>
<?php if (!empty($album_image->file->shot_at)): ?>
		<small>撮影日時: <?php echo site_get_time($album_image->file->shot_at) ?></small>
<?php endif; ?>
	</div>
</div>
<?php if (isset($u) && $u->id == $album_image->album->member_id): ?>
<div class="btn-group">
	<button data-toggle="dropdown" class="btn dropdown-toggle"><i class="icon-edit"></i> edit <span class="caret"/></button>
	<ul class="dropdown-menu">
		<li><?php echo Html::anchor('album/image/edit/'.$album_image->id, '<i class="icon-pencil"></i> 編集'); ?></li>
		<li><a href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('album/image/delete/%d?%s=%s', $album_image->id, Config::get('security.csrf_token_key'), Util_security::get_csrf())); ?>';});"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
