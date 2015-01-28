<?php if (Auth::check()): ?>
<?php 	if (is_enabled('notice')): ?>
<?php echo Asset::js('site/modules/notice/common/util.js');?>
<?php echo render('_parts/modal', array(
	'block_attrs' => array('id' => 'modal_notice_navbar'),
	'size' => 'sm',
	'title' => term('notice'),
	'is_display_footer_close_btn' => true,
)); ?>
<script type="text/x-handlebars-template" id="notices-template">
<?php echo render('notice::_parts/handlebars_template/list'); ?>
</script>
<?php 	endif; ?>
<?php else: ?>
<?php $destination = Session::get_flash('destination') ?: urlencode(Uri::string_with_query()); ?>
<script type="text/x-handlebars-template" id="popover_login-template">
<?php echo render('auth/_parts/login', array('in_popover' => true, 'destination' => $destination)); ?>
</script>
<script>
	var source   = $("#popover_login-template").html();
	var template = Handlebars.compile(source);
	var content = (template());
	var inputs = new Array('#form_email', '#form_password');
	loadPopover('#insecure_user_menu', '#insecure_user_popover', content, '', inputs);
</script>
<?php endif; ?>

<?php if (is_render_site_summary_at_client_side()): ?>
<script type="text/x-handlebars-template" id="site_summary-template">
<?php echo render('_parts/handlebars_template/site_summary'); ?>
</script>
<?php endif; ?>
