<script type="text/javascript">
$(function(){
	var album_image_id = <?php echo $album_image_id; ?>;
	var baseUrl = '<?php echo Uri::base(false); ?>';
	show_list(baseUrl, album_image_id, false);

	$('#btn_album_image_comment_create').click(function(){
		var body = $("#input_album_image_comment").val().trim();
		if (body.length > 0) {
			$.post(
				baseUrl + 'album/image/comment/create_ajax/' + album_image_id,
				{
					'body': body,
					'<?php echo Config::get('security.csrf_token_key'); ?>': '<?php echo Util_security::get_csrf(); ?>',
				},
				function(data){
					$.jGrowl('コメントを投稿しました。');
					show_list(baseUrl, album_image_id, false);
					$("#input_album_image_comment").val('');
					$("textarea#input_album_image_comment").css("height", "33px");;
				}
			);
		}
	});

	$('.btn_album_image_comment_delete').live("click", function(){
		var id_value = $(this).attr("id");
		var id = id_value.replace(/btn_album_image_comment_delete_/g, "");
		jConfirm('削除しますか?', '削除確認', function(r) {
			if (r == true) {
				$.ajax({
					url : baseUrl + 'album/image/comment/delete_ajax/' + id,
					dataType : "text",
					data : {'id': id, '<?php echo Config::get('security.csrf_token_key'); ?>': '<?php echo Util_security::get_csrf(); ?>'},
					type : 'POST',
					success: function(status_after){
						$('#commentBox_' + id).fadeOut();
						$.jGrowl('No.' + id + 'のコメントを削除しました。');
					},
					error: function(){
						$.jGrowl('No.' + id + 'のコメントを削除できませんでした。');
					}
				});
			}
		});
	});
});

function show_list(base_url, album_image_id, is_fadein) {
	var url = base_url + 'album/image/comment/list/' + album_image_id;
	$("#loading_list").html('<img src="' + base_url + 'assets/img/loading.gif">');
	$.get(url, {'nochache':(new Date()).getTime()}, function(data) {
		if (data.length > 0) {
			if (is_fadein) {
				$("#comment_list").fadeOut('fast');
			}
			$("#comment_list").html(data).fadeIn('fast');
		}
	});
	$("#loading_list").remove();
}
</script>
