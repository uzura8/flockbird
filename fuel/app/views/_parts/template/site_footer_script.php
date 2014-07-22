<?php if (!Auth::check()): ?>
<?php $destination = Session::get_flash('destination') ?: urlencode(Uri::string_with_query());?>
<script>
	var inputs = new Array('#form_email', '#form_password');
	loadPopover('#insecure_user_menu', '#insecure_user_popover', '<?php echo Uri::base_path('auth/api/login.html').'?destination='.$destination; ?>', inputs);
</script>
<?php endif; ?>
