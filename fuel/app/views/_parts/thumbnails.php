<?php if (!empty($images['list'])): ?>
<div class="row">
<?php $col_num = empty($images['column_count']) ? 3 : floor(12 / $images['column_count']); ?>
<?php foreach ($images['list'] as $image): ?>
	<div class="col-sm-<?php echo $col_num; ?> thumbnail_box">
<?php
$file_cate = !empty($images['file_cate']) ? $images['file_cate'] : 'ai';
if ($file_cate == 'ai' && $image)
{
	$file_name = $image->file_name;
	$size = 'M';
	$link_uri = 'album/image/'.$image->id;
	$image_name = $image->name;
	$is_link2raw_file = false;
}
elseif (in_array($file_cate, $original_table_keys) && $image)
{
	$file_name = $image->file_name;
	$link_uri = '';
	$image_name = $image->name;
	$is_link2raw_file = true;
}
else
{
	$file_name = $image->name;
	$file_cate = 'm';
	$size = 'LL';
	$link_uri = '';
	$image_name = '';
	$is_link2raw_file = true;
}
if (!empty($images['size'])) $size = $images['size'];

$attrs = array('class' => 'thumbnail');
if (!empty($is_modal_link))
{
	$attrs['class'] .= ' js-modal btn_modal_slide';
	$attrs['data-is_list'] = 1;
	$attrs['data-uri'] = sprintf('album/image/api/slide/%d.html', $image->id);
	$attrs['data-target'] = '#modal_album_slide';
}

echo img($file_name, $size, $link_uri, $is_link2raw_file, $image_name ?: '', false, false, $attrs);
?>
<?php if (!empty($is_display_name) && $image_name): ?>
		<small><?php echo $image_name; ?></small>
<?php endif; ?>
	</div>
<?php endforeach; ?>
</div>
<?php if (isset($images['parent_page_uri'], $images['count_all'])): ?>
<div><?php echo render('_parts/image_count_link', array('count' => $images['count_all'], 'uri' => $images['parent_page_uri'])); ?></div>
<?php endif; ?>
<?php endif; ?>
