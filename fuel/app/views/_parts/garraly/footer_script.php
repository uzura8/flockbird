<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls">
	<div class="slides"></div>
	<h3 class="title"></h3>
	<a class="prev">‹</a>
	<a class="next">›</a>
	<a class="close">×</a>
</div>

<?php
Asset::js(array(
	'blueimp-gallery/blueimp-gallery.js',
	'site/common/gallery.js',
), null, 'js_garray', false, true);
echo Asset::render('js_garray', false, 'js');
?>

<?php if (!empty($slide_file_names)): ?>
<script>
var pos;
var links = [
<?php
$count = count($slide_file_names);
$i = 0;
foreach ($slide_file_names as $file_name)
{
	$i++;
	$image_uri = img_uri($file_name, IS_SP ? 'L' : 'raw');
	echo sprintf("get_url('%s')%s", $image_uri, $i == $count ? '' : ',').PHP_EOL;
}
?>
];
</script>
<?php endif; ?>

