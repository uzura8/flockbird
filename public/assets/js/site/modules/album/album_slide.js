$('.carousel').carousel({
	interval: false
});

var baseUrl = get_baseUrl();
var album_id = get_id_from_url();
var comment_limit_default = get_comment_limit_default();

var basePath = get_upload_uri_base_path(); // 画像のベースパスを指定
var images = {}; // 画像ファイル名格納用配列
var image_ids = []; // 画像id格納用配列
var slideNumber = 0;
var slideNumber_max = 0;

$.get(get_url('album/image/api/list.json'), {'album_id':album_id, 'limit':0}, function(json){
	$.each(json, function(i, data){
		images[data.id] = basePath + data.file.path + data.file.name;
		image_ids.push(data.id);
	});
	slideNumber_max = image_ids.length;
	image_ids.reverse();

	if ( slideNumber_max < 2 ) {
		return;
	}

	// 最初の画像の設定
	var position = {
		prev : slideNumber_max - 1,
		now  : 0,
		next : 1
	};

	var html = "";
	$.each(position, function(i, v){
		if ( i == 'now' ) {
			html += "<img class='item active' src='"+ images[image_ids[v]]+"' id='image_" + image_ids[v] + "'>";
		} else {
			html += "<img class='item' src='"+ images[image_ids[v]]+"' id='image_" + image_ids[v] + "'>";
		}
	});

	if ( html == "" ) {
		return;
	}

	// 画像のDOMの追加
	$('#myCarousel > .carousel-inner').html(html);
	//$('#slideNumber').html('現在のスライド番号:' + slideNumber + ' / 画像ID: ' + image_ids[slideNumber]);
	$('#link2detail').html('<a href="' + get_url('album/image/' + image_ids[slideNumber]) + '" class="btn btn-default"><i class="glyphicon glyphicon-picture"></i> 詳細</a>');

	show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', comment_limit_default);
},'json');

var next = function() {
	// 次のスライドへ移動
	slideNumber++;

	if (slideNumber > slideNumber_max - 1) {
		slideNumber = 0;
	}
	nextSlideNumber = slideNumber + 1;
	if (nextSlideNumber > slideNumber_max - 1) {
		nextSlideNumber = 0;
	}

	show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', comment_limit_default);

	$('#myCarousel > .carousel-inner > img:first').remove();
	$('#myCarousel .carousel-inner').append('<img class="item" src="'+ images[image_ids[nextSlideNumber]]+'" id="image_'+ image_ids[nextSlideNumber] +'">');
	$('#myCarousel').carousel('next');
}

var prev = function() {
	// 前のスライドに移動
	slideNumber--;

	if (slideNumber < 0) {
		slideNumber = slideNumber_max - 1;
	}
	prevSlideNumber = slideNumber - 1;
	if (prevSlideNumber < 0) {
		prevSlideNumber = slideNumber_max - 1;
	}

	show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', comment_limit_default);

	$('#myCarousel > .carousel-inner > img:last').remove();
	$('#myCarousel > .carousel-inner').prepend('<img class="item" src="'+ images[image_ids[prevSlideNumber]]+'" id="image_'+ image_ids[prevSlideNumber] +'">');
	$('#myCarousel').carousel('prev');
}

var slide = function(type) {
	// スライドの実行
	if (type == 'next') {
		next();
	} else if ( type == 'prev') {
		prev();
	}
	//$('#slideNumber').html('現在のスライド番号:' + slideNumber + ' / 画像ID: ' + image_ids[slideNumber]);
	$('#link2detail').html('<a href="' + get_url('album/image/' + image_ids[slideNumber]) + '" class="btn btn-default"><i class="glyphicon glyphicon-picture"></i> 詳細</a>');
}

$('.carousel-control').click(function(event) {
	// 横ボタン動作
	if ( slideNumber_max < 2 ) {
		return;
	}
	reset_textarea('#textarea_comment');
	slide($(this).attr('data-action'));
	return false;
});

$('body').keydown(function(event){
	// キーボード操作によるスライドの移動
	slide(event.keyCode);
});

$(document).on('click', '#listMoreBox_comment', function(){
	//show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', 0, $('.commentBox:first').attr('id'), true, '#' + $(this).attr('id'));
	var uri = get_url('album/image/' + image_ids[slideNumber]) + '?all_comment=1#comments';
	redirect(uri);
	return false;
});

$(document).on('click', '#btn_comment', function(){
	create_comment(
		image_ids[slideNumber],
		'album/image/comment/api/create.json',
		'album/image/comment/api/list/' + image_ids[slideNumber] + '.html',
		$('.commentBox').last().attr('id'),
		this
	);

	return false;
});

$(document).on('click', '.btn_comment_delete', function(){
	delete_item('album/image/comment/api/delete.json', get_id_num(($(this).attr('id'))), '#commentBox');
	return false;
});

if (!is_sp()) {
	$(document).on({
		mouseenter:function() {$('#btn_comment_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
		mouseleave:function() {$('#btn_comment_delete_' + get_id_num($(this).attr('id'))).hide()}
	}, '.commentBox');
}
