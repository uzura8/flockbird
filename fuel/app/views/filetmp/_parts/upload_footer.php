<?php echo Asset::js('jquery.ui.widget.js');?>
<?php echo Asset::js('jquery.iframe-transport.js');?>
<?php echo Asset::js('jquery.fileupload.js');?>
<script>
/*jslint unparam: true */
/*global window, $ */
$(function () {
	'use strict';
	// Change this to the location of your server-side upload handler:
	//var url = get_url('filetmp/upload');
	var url = get_url('filetmp/api/upload.html');
	$('#fileupload').fileupload({
		url: url,
		dataType: 'text',
		done: function (e, data) {
			//$.each(data.result.files, function (index, file) {
			//	if (file.error) {
			//		var error = $('<span class="text-danger"/>').text(file.error);
			//		$('<p/>').text(error).appendTo('#files');
			//	} else {
			//		$('<p/>').text(file.name).appendTo('#files');
			//	}
			//});
			$('#files').append(data['result']).fadeIn('fast');
		},
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#progress .progress-bar').css(
				'width',
				progress + '%'
			);
		}
	}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');

	$(document).on('click','.delete_file_tmp', function(){
		var file_tmp_id = $(this).data('id') ? parseInt($(this).data('id')) : 0;

		delete_item('filetmp/api/upload.json', file_tmp_id, '#file_tmp');
		return false;
	});
});
</script>
