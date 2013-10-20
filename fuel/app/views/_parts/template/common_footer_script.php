<script>
var GL = {};
GL.execute_flg = false;
function get_baseUrl() {return '<?php echo Uri::base(false); ?>';}
function get_token_key() {return '<?php echo Config::get('security.csrf_token_key'); ?>';}
function get_token() {return '<?php echo Util_security::get_csrf(); ?>';}
function is_sp() {return <?php echo (IS_SP)? 'true' : 'false'; ?>;}
function get_term(key) {
	var terms = {};
	terms['public_flag'] = '<?php echo \Config::get('term.public_flag.label'); ?>';
	terms['album_image'] = '<?php echo \Config::get('term.album_image'); ?>';
	terms['timeline']    = '<?php echo \Config::get('term.timeline'); ?>';
	return terms[key];
}
function get_config(key) {
	var config = {};
	config['timeline_articles_limit'] = <?php echo Config::get('timeline.articles.limit'); ?>;
	config['site_public_flag_default'] = <?php echo Config::get('site.public_flag.default'); ?>;
	return config[key];
}

</script>
