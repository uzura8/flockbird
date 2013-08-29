<?php echo render('_parts/load_masonry'); ?>
<?php if (Config::get('album.display_setting.detail.display_slide_image')): ?>
<script type="text/javascript">
$('.carousel').carousel({
	interval: false
})
</script>
<?php endif; ?>
