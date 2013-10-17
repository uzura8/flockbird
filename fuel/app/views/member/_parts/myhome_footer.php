<script>
function get_config(key) {
	var config = {};
	config['timeline_articles_limit'] = <?php echo Config::get('timeline.articles.limit'); ?>;
	config['site_public_flag_default'] = <?php echo Config::get('site.public_flag.default'); ?>;
	return config[key];
}

<?php echo render('_parts/script/get_public_flags'); ?>
</script>
