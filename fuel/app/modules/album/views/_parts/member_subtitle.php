<div id="btn_menu">
<?php if ($is_mypage) : ?>
<?php echo Html::anchor('album/create', sprintf('<i class="ls-icon-edit"></i> %s新規作成', Config::get('term.album')), array('class' => 'btn btn-default mr')); ?>
<?php endif; ?>
<?php
$name = $is_mypage ? '自分' : $member->name.'さん';
$controller = Site_Util::get_controller_name();
if ($controller == 'album')
{
	echo Html::anchor(
		sprintf('album/member/%d/images', $member->id),
		sprintf('<i class="icon-picture"></i> %sの%sを全て見る', $name, Config::get('term.album_image')),
		array('class' => 'btn btn-default mr')
	);
}
elseif ($controller == 'image')
{
	echo Html::anchor(
		sprintf('album/member/%d', $member->id),
		sprintf('<i class="icon-picture"></i> %sの%sを全て見る', $name, Config::get('term.album')),
		array('class' => 'btn btn-default mr')
	);
}
?>
</div>
