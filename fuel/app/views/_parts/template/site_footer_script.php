<?php if (!Auth::check()): ?>
<?php $destination = Session::get_flash('destination') ?: urlencode(Input::server('REQUEST_URI'));?>
<script>
	var inputs = new Array('#form_email', '#form_password');
	load_popover('#insecure_user_menu', '#insecure_user_popover', '<?php echo Uri::create('auth/api/login').'?destination='.$destination; ?>', inputs);
</script>
<?php endif; ?>
