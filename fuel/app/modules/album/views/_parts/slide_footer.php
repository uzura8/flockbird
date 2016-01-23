<?php echo render('_parts/comment/handlebars_template'); ?>
<script>
function getConfigSlide(key) {
	var config = {
		uploadUriBasePath: '<?php echo FBD_URI_PATH.Site_Upload::get_uploaded_file_path('', img_size('ai', 'L'), 'img', false, true); ?>',
		slideLimit: <?php echo conf('display_setting.slide.limit', 'album'); ?>,
		sort: '<?php echo !empty($is_desc) ? 'desc' : 'asc'; ?>'
	};
	return config[key];
}
</script>

<?php if (Auth::check()): ?>
<?php echo render('_parts/handlebars_template/post_comment', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
<?php endif; ?>

<?php echo Asset::js('site/modules/album/common/slide.js');?>

