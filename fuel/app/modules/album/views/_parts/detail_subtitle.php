<div class="member_img_box_s">
	<?php echo img($album->member->get_image(), '30x30', 'member/'.$album->member->id); ?>
	<div class="content">
		<div class="main">
			<b class="fullname"><?php echo Html::anchor('member/'.$album->member->id, $album->member->name); ?></b>
		</div>
		<small>
		日時: <?php echo date('Y年n月j日 H:i', strtotime($album->created_at)) ?>
		(<?php echo Date::time_ago(strtotime($album->created_at)) ?>)
		</small>
	</div>
</div>

<?php if (isset($current_user) && $current_user->id == $album->member_id): ?>
<div class="btn-group">
	<button data-toggle="dropdown" class="btn dropdown-toggle"><i class="icon-edit"></i> edit <span class="caret"/></button>
	<ul class="dropdown-menu">
		<li><?php echo Html::anchor('album/edit/'.$album->id, '<i class="icon-pencil"></i> 編集'); ?></li>
		<li><a href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('album/delete/%d?%s=%s', $album->id, Config::get('security.csrf_token_key'), Security::fetch_token())); ?>';});"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
