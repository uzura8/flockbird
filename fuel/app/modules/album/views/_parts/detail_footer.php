<?php echo Asset::js('bootstrap-carousel.js');?>
<?php echo Asset::js('jquery.masonry.min.js');?>
<?php echo Asset::js('jquery.infinitescroll.min.js');?>
<script type="text/javascript">
$('.carousel').carousel({
	interval: false
})


$(function(){
	load_masonry_item(
		'#main_container',
		'.main_item',
		'<?php echo \Config::get('album.term.album_image'); ?>がありません。',
		'<?php echo \Uri::create('assets/img/site/loading_l.gif'); ?>'
	);
});

<?php if (!Agent::is_smartphone()): ?>
$('.commentBox').live({
	mouseenter:function() {
		var id = $(this).attr('id').replace($(this).attr('class') + '_', '');
		var btn = '#btn_album_image_comment_delete_' + id;
		$(btn).fadeIn('fast');
	},
	mouseleave:function() {
		var id = $(this).attr('id').replace($(this).attr('class') + '_', '');
		var btn = '#btn_album_image_comment_delete_' + id;
		$(btn).hide();
	}
});
$('.imgBox').live({
	mouseenter:function() {
		var id = $(this).attr('id').replace($(this).attr('class') + '_', '');
		var btn = '#btn_album_image_edit_' + id;
		$(btn).fadeIn('fast');
	},
	mouseleave:function() {
		var id = $(this).attr('id').replace($(this).attr('class') + '_', '');
		var btn = '#btn_album_image_edit_' + id;
		$(btn).hide();
	}
});
<?php endif; ?>

$('.btn_album_image_comment_delete').click(function(){
	var id_value = $(this).attr("id");
	var id = id_value.replace(/btn_album_image_comment_delete_/g, "");
	jConfirm('削除しますか?', '削除確認', function(r) {
		if (r == true) {
			$.ajax({
				url : get_baseUrl() + 'album/image/comment/delete_ajax/' + id,
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

function set_cover(album_image_id) {
	$.ajax({
		url : get_baseUrl() + 'album/image/api/set_cover.json',
		dataType : 'text',
		data : {'id': album_image_id, '<?php echo Config::get('security.csrf_token_key'); ?>': '<?php echo Util_security::get_csrf(); ?>'},
		type : 'POST',
		success: function(status_after){
			$.jGrowl('カバー写真を設定しました。');
		},
		error: function(){
			$.jGrowl('カバー写真の設定に失敗しました。');
		}
	});
}
</script>
