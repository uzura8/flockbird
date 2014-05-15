<script>
var GL = {};
GL.execute_flg = false;
function get_baseUrl() {return '<?php echo Uri::base(false); ?>';}
function get_token_key() {return '<?php echo Config::get('security.csrf_token_key'); ?>';}
function get_token() {return '<?php echo Util_security::get_csrf(); ?>';}
function is_sp() {return <?php echo (IS_SP)? 'true' : 'false'; ?>;}
function get_term(key) {
	var terms = {};
	terms['public_flag'] = '<?php echo term('public_flag.label'); ?>';
<?php if (conf('memberRelation.follow.isEnabled')): ?>
	terms['follow'] = '<?php echo term('follow'); ?>';
<?php endif; ?>
<?php if (Module::loaded('album')): ?>
	terms['album']       = '<?php echo term('album'); ?>';
	terms['album_image'] = '<?php echo term('album_image'); ?>';
<?php endif; ?>
<?php if (Module::loaded('timeline')): ?>
	terms['timeline']    = '<?php echo term('timeline'); ?>';
<?php endif; ?>
	return terms[key];
}
function get_config(key) {
	var config = {};
	config['default_list_comment_limit_max'] = <?php echo conf('view_params_default.list.comment.limit_max'); ?>;
	config['default_detail_comment_limit_max'] = <?php echo conf('view_params_default.detail.comment.limit_max'); ?>;
	config['site_public_flag_default'] = <?php echo conf('public_flag.default'); ?>;
<?php if (Module::loaded('timeline')): ?>
	config['timeline_list_limit'] = <?php echo Config::get('timeline.articles.limit'); ?>;
	config['timeline_list_comment_limit_max'] = <?php echo Config::get('timeline.articles.comment.limit_max'); ?>;
<?php endif; ?>
	return config[key];
}
</script>
