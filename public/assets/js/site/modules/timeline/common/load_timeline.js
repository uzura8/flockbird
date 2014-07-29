$(function() {
	$(document).on('click','.link_comment', function(){
		var id = parseInt($(this).data('id'));
		var targetBlockName = $(this).data('block');
		var postUri = $(this).data('post_uri');
		var getUri = $(this).data('get_uri');

		if (!get_uid() || !id || !targetBlockName.length) return false;

		if ($('#link_comment_box_' + id).size()) $('#link_comment_box_' + id).hide();
		var textareaSelector = '#textarea_comment_' + id;
		if ($(textareaSelector).size() == 0) {
			var source   = $("#comment_form-template").html();
			var template = Handlebars.compile(source);
			var val = {
				'id' : id,
				'postUri' : postUri,
				'getUri' : getUri,
				'listSelector' : '#comment_list_' + id,
				'counterSelector' : '#comment_count_' + id
			};
			$('#' + targetBlockName).html(template(val));
		}
		$(textareaSelector).focus();
		return false;
	});

	$('body').tooltip({
		selector: 'a[data-toggle=tooltip]'
	});

	$(document).on('click', '.js-dropdown_tl_menu', function(){
		var detail_uri = $(this).data('detail_uri') ? $(this).data('detail_uri') : '';
		var delete_uri = $(this).data('delete_uri') ? $(this).data('delete_uri') : '';
		var parent = $(this).data('parent') ? $(this).data('parent') : '';
		var member_id = $(this).data('member_id') ? parseInt($(this).data('member_id')) : 0;

		var targetBlock = $(this).next('ul');
		if (targetBlock.html().length) return false;

		var source   = $("#tl_dropdown_menu-tpl").html();
		var template = Handlebars.compile(source);
		var val = {'detail_uri' : get_url(detail_uri)};
		if (member_id == get_uid()) {
			if (delete_uri) {
				val['delete_uri'] = get_url(delete_uri);
				val['parent_id'] = parent;
			}
		}
		targetBlock.html(template(val));
		return false;
	});
})
