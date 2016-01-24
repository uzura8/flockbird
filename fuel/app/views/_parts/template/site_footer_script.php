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

<?php 	if (is_enabled('message')): ?>
<?php echo Asset::js('site/modules/message/common/util.js');?>
<?php echo render('_parts/modal', array(
	'block_attrs' => array('id' => 'modal_message_navbar'),
	'size' => 'sm',
	'title' => term('message.view'),
	'is_display_footer_close_btn' => true,
)); ?>
<script type="text/x-handlebars-template" id="messages-template">
<?php echo render('message::_parts/handlebars_template/list'); ?>
</script>
<?php 	endif; ?>
<?php else: ?>

<?php
$destination = Session::get_flash('destination') ?: urlencode(Uri::string_with_query());
$login_form = render('auth/_parts/login', array('in_popover' => true, 'destination' => $destination));
?>
<?php 	switch (conf('auth.headerLoginForm.type')): ?>
<?php 		case 'popover': ?>
<script type="text/x-handlebars-template" id="login-template">
<?php echo $login_form; ?>
</script>
<script>
	var source   = $("#login-template").html();
	var template = Handlebars.compile(source);
	var content = (template());
	var inputs = new Array('#form_email', '#form_password');
	loadPopover('#insecure_user_menu', '#insecure_user_popover', content, '', inputs);
</script>
<?php 		break; ?>

<?php 		case 'modal': ?>
<?php
$modal_view = View::forge('_parts/modal', array(
	'block_attrs' => array('id' => 'insecure_user_modal'),
	'size' => 'sm',
	'title' => term('site.login'),
	'is_display_footer_close_btn' => true,
));
$modal_view->set_safe('body', $login_form);
echo $modal_view->render();
?>
<?php 		break; ?>
<?php 	endswitch; ?>
<?php endif; ?>

<?php if (is_render_site_summary_at_client_side()): ?>
<script type="text/x-handlebars-template" id="site_summary-template">
<?php echo render('_parts/handlebars_template/site_summary'); ?>
</script>
<?php endif; ?>

<?php if (conf('like.isEnabled')): ?>
<?php echo render('_parts/like/modal_like_member'); ?>
<?php endif; ?>

