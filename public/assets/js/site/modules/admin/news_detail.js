$(function(){
	$(document).on('click','#link_publish', function(){
		post_submit(this);
		return false;
	});

	$(document).on('click','#link_delete', function(){
		post_submit(this);
		return false;
	});
});
