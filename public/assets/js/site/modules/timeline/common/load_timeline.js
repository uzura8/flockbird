$(function() {
	var source   = $("#comment_form-template").html();
	var template = Handlebars.compile(source);

	$(document).on('click','.link_comment', function(){
		var id = parseInt($(this).data('id'));
		var targetBlockName = $(this).data('block');
		var postUri = $(this).data('post_uri');
		var getUri = $(this).data('get_uri');

		if (!get_uid() || !id || !targetBlockName.length) return false;

		if ($('#link_comment_box_' + id).size()) $('#link_comment_box_' + id).hide();
		var textareaSelector = '#textarea_comment_' + id;
		if ($(textareaSelector).size() == 0) {
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
})
