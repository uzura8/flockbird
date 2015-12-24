<script>
var GL = {};
GL.execute_flg = false;
function get_uid() {return <?php echo (!IS_ADMIN && Auth::check()) ? $u->id : 0; ?>;}
function check_is_admin() {return <?php echo IS_ADMIN ? 'true' : 'false'; ?>;}
function getBasePath() {return '<?php echo Uri::base_path(); ?>';}
function getCurrentPath() {return '<?php echo Uri::string(); ?>';}
function getBaseUrl() {
	var is_current_protocol = (arguments.length > 0) ? arguments[0] : false;
	if (is_current_protocol) return '<?php echo Uri::base(true); ?>';
	return '<?php echo Uri::base(); ?>';
}
function get_token_key() {return '<?php echo Config::get('security.csrf_token_key'); ?>';}
function get_token() {return '<?php echo Util_security::get_csrf(); ?>';}
function is_sp() {return <?php echo (IS_SP)? 'true' : 'false'; ?>;}
function is_site() {return <?php echo (IS_ADMIN)? 'false' : 'true'; ?>;}
function get_term(key) {
	var terms = {
<?php if (conf('memberRelation.follow.isEnabled')): ?>
		'follow': '<?php echo term('follow'); ?>',
<?php endif; ?>
<?php if (conf('like.isEnabled')): ?>
		'like': '<?php echo term('form.like'); ?>',
		'do_like': '<?php echo term('form.do_like'); ?>',
		'undo_like': '<?php echo term('form.undo_like'); ?>',
<?php endif; ?>
<?php if (is_enabled('notice')): ?>
		'watch': '<?php echo term('form.watch'); ?>',
		'do_watch': '<?php echo term('form.do_watch'); ?>',
		'do_unwatch': '<?php echo term('form.do_unwatch'); ?>',
<?php endif; ?>
<?php if (is_enabled('note')): ?>
		'note': '<?php echo term('note'); ?>',
<?php endif; ?>
<?php if (is_enabled('album')): ?>
		'album': '<?php echo term('album'); ?>',
		'album_image': '<?php echo term('album_image'); ?>',
		'add_picture': '<?php echo term('form.add_picture'); ?>',
<?php endif; ?>
<?php if (is_enabled('timeline')): ?>
		'timeline': '<?php echo term('timeline'); ?>',
<?php endif; ?>
<?php if (is_enabled('thread')): ?>
		'thread': '<?php echo term('thread'); ?>',
<?php endif; ?>
<?php if (is_enabled('message')): ?>
		'message': '<?php echo term('message.view'); ?>',
		'messageTypeMember': '<?php echo term('message.types.label.member'); ?>',
		'messageTypeGroup': '<?php echo term('message.types.label.group'); ?>',
		'messageTypeSiteInfo': '<?php echo term('message.types.label.site_info'); ?>',
		'messageTypeSiteInfoAll': '<?php echo term('message.types.label.site_info_all'); ?>',
		'messageTypeSystemInfo': '<?php echo term('message.types.label.system_info'); ?>',
<?php endif; ?>
		'public_flag': '<?php echo term('public_flag.label'); ?>',
		'comment': '<?php echo term('form.comment'); ?>',
		'member': '<?php echo term('member.view'); ?>',
		'member_left': '<?php echo term('member.left'); ?>',
		'left_member': '<?php echo term('member.left'); ?>',
		'show_detail': '<?php echo term('site.show_detail'); ?>',
		'login': '<?php echo term('site.login'); ?>',
		'auth': '<?php echo term('site.auth'); ?>',
		'info': '<?php echo term('common.info'); ?>',
		'request': '<?php echo term('common.request'); ?>',
		'invalid': '<?php echo term('common.invalid'); ?>'
	};
	return terms[key];
}
function get_config(key) {
	var config = {
<?php if (is_enabled('timeline')): ?>
		'timeline_list_limit': <?php echo conf('articles.limit', 'timeline'); ?>,
<?php endif; ?>
<?php if (is_enabled_map()): ?>
		'mapParams': <?php echo json_encode(conf('map.paramsDefault')); ?>,
<?php endif; ?>
<?php if (is_enabled_share('google')): ?>
		'isEnabledShareGoogle': 1,
<?php endif; ?>
		'default_ajax_timeout': <?php echo conf('default.ajax_timeout'); ?>,
		'default_list_limit': <?php echo conf('view_params_default.list.limit'); ?>,
		'default_list_comment_limit_max': <?php echo conf('view_params_default.list.comment.limit_max'); ?>,
		'default_detail_comment_limit_max': <?php echo conf('view_params_default.detail.comment.limit_max'); ?>,
		'default_form_comment_textarea_height': '<?php echo conf('view_params_default.form.comment.textarea.height'); ?>',
		'site_public_flag_default': <?php echo conf('public_flag.default'); ?>,
		'is_render_site_summary_at_client_side': <?php echo is_render_site_summary_at_client_side() ? 1 : 0; ?>,
		mediaBaseUrl: '<?php echo FBD_MEDIA_BASE_URL; ?>',
		upload_dir_name: '<?php echo FBD_UPLOAD_DIRNAME; ?>',
		site_description: '<?php echo FBD_SITE_DESCRIPTION; ?>',
		upload_max_filesize: <?php echo FBD_UPLOAD_MAX_FILESIZE; ?>,
		app_id_facebook: '<?php echo FBD_FACEBOOK_APP_ID; ?>'
	};
	return config[key];
}
</script>
