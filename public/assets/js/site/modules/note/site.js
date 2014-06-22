$(function(){
	$(document).on('click','.update_public_flag', function(){
		$(this).parent('li').parent('ul.dropdown-menu').parent('div.btn-group').removeClass('open');
		if (GL.execute_flg) return false;
		update_public_flag(this);
		return false;
	});
});
