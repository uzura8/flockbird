$(function(){
	$(document).on('click','#link_publish', function(){
		post_submit(this);
		return false;
	});

	if (url('?write_comment')) $('#textarea_comment').focus();
});
