<div id="btn_menu">
<?php if ($is_mypage) : ?>
<?php echo btn(term('album', 'form.create'), 'album/create', 'mr', true, null, null, null, 'plus', null, null, false); ?>
<?php endif; ?>
<?php
$name = $is_mypage ? '自分' : $member->name.'さん';
$controller = Site_Util::get_controller_name();
if ($controller == 'album')
{
	echo btn(sprintf('%sの%sを全て見る', $name, term('album_image')), sprintf('album/member/%d/images', $member->id), 'mr', true, null, null, null, 'picture', null, null, false);
}
elseif ($controller == 'image')
{
	echo btn(sprintf('%sの%sを全て見る', $name, term('album')), sprintf('album/member/%d', $member->id), 'mr', true, null, null, null, 'picture', null, null, false);
}
?>
</div>
