$(document).on('click','.btn_profile_delete', function(){
	var id = parseInt($(this).data('id'));
	if (!id) return false;

	delete_item('admin/profile/delete/' + id);
	return false;
});
