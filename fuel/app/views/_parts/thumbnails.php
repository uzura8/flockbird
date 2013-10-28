<?php if (!empty($images['list'])): ?>
<ul class="thumbnails">
<?php $span_num = empty($images['column_count']) ? 3 : floor(12 / $images['column_count']); ?>
<?php foreach ($images['list'] as $image): ?>
	<li class="span<?php echo $span_num; ?>">
<?php
$file_cate = !empty($images['file_cate']) ? $images['file_cate'] : 'ai';
if ($file_cate == 'ai')
{
	$file_obj = $image->file;
	$size = 'M';
	$link_uri = 'album/image/'.$image->id;
	$image_name = $image->name;
	$is_link2raw_file = false;
}
else
{
	$file_obj  = $image;
	$file_cate = 'm';
	$size = 'LL';
	$link_uri = '';
	$image_name = '';
	$is_link2raw_file = true;
}
if (!empty($images['size'])) $size = $images['size'];
$additional_table = !empty($images['additional_table']) ? $images['additional_table'] : '';

echo img($file_obj, img_size($file_cate, $size, $additional_table), $link_uri, $is_link2raw_file, $image_name ?: '', false, array('class' => 'thumbnail'));
?>
<?php if (!empty($is_display_name) && $image_name): ?>
		<small><?php echo $image_name; ?></small>
<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
