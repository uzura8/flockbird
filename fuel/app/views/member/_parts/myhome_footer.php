<script type="text/javascript">
function get_config(key) {
	var config = {};
	config['timeline_articles_limit'] = <?php echo Config::get('timeline.articles.limit'); ?>;
	return config[key];
}
</script>
