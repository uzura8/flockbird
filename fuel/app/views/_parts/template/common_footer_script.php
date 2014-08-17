<script>
var GL = {};
GL.execute_flg = false;
function get_uid() {return <?php echo Auth::check() ? $u->id : 0; ?>;}
function check_is_admin() {return <?php echo IS_ADMIN ? 'true' : 'false'; ?>;}
function getBasePath() {return '<?php echo Uri::base_path(); ?>';}
function getBaseUrl() {
	var is_current_protocol = (arguments.length > 0) ? arguments[0] : false;
	if (is_current_protocol) return '<?php echo Uri::base(true, true); ?>';
	return '<?php echo Uri::base(); ?>';
}
function get_token_key() {return '<?php echo Config::get('security.csrf_token_key'); ?>';}
function get_token() {return '<?php echo Util_security::get_csrf(); ?>';}
function is_sp() {return <?php echo (IS_SP)? 'true' : 'false'; ?>;}
function get_term(key) {
	var terms = {
<?php if (conf('memberRelation.follow.isEnabled')): ?>
		'follow': '<?php echo term('follow'); ?>',
<?php endif; ?>
<?php if (conf('like.isEnabled')): ?>
		'like': '<?php echo term('form.like'); ?>',
		'do_like': '<?php echo term('form.do_like'); ?>',
<?php endif; ?>
<?php if (Module::loaded('album')): ?>
		'album': '<?php echo term('album'); ?>',
		'album_image': '<?php echo term('album_image'); ?>',
<?php endif; ?>
<?php if (Module::loaded('timeline')): ?>
		'timeline': '<?php echo term('timeline'); ?>',
<?php endif; ?>
		'public_flag': '<?php echo term('public_flag.label'); ?>',
		'comment': '<?php echo term('form.comment'); ?>'
	};
	return terms[key];
}
function get_config(key) {
	var config = {
<?php if (Module::loaded('timeline')): ?>
		'timeline_list_limit': <?php echo Config::get('timeline.articles.limit'); ?>,
<?php endif; ?>
		'default_ajax_timeout': <?php echo conf('default.ajax_timeout'); ?>,
		'default_list_limit': <?php echo conf('view_params_default.list.limit'); ?>,
		'default_list_comment_limit_max': <?php echo conf('view_params_default.list.comment.limit_max'); ?>,
		'default_detail_comment_limit_max': <?php echo conf('view_params_default.detail.comment.limit_max'); ?>,
		'default_form_comment_textarea_height': '<?php echo conf('view_params_default.form.comment.textarea.height'); ?>',
		'site_public_flag_default': <?php echo conf('public_flag.default'); ?>
	};
	return config[key];
}
</script>
