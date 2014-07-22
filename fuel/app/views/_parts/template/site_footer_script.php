<?php if (!Auth::check()): ?>
<?php $destination = Session::get_flash('destination') ?: urlencode(Uri::string_with_query()); ?>
<script type="text/x-handlebars-template" id="popover_login-template">
<?php echo render('auth/_parts/login', array('in_popover' => true, 'destination' => $destination)); ?>
</script>
<script>
	var source   = $("#popover_login-template").html();
	var template = Handlebars.compile(source);
	var val = {};
	var content = (template(val));
	var inputs = new Array('#form_email', '#form_password');
	loadPopover('#insecure_user_menu', '#insecure_user_popover', content, '', inputs);
</script>
<?php endif; ?>
