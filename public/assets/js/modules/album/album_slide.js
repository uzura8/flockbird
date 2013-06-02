$('.carousel').carousel({
	interval: false
});

var baseUrl = get_baseUrl();
var album_id = get_id_from_url();
var comment_limit_default = get_comment_limit_default();

var basePath = get_upload_uri_base_path(); // 画像のベースパスを指定
var images = []; // 画像ファイル名格納用配列
var image_ids = []; // 画像id格納用配列
var slideNumber = 0;

$.get('/album/api/detail/' + album_id + '.json', function(json){
	$.each(json, function(i, data){
		images.push(basePath + data.file.name);
		image_ids.push(data.id);
	});

	if ( images.length < 2 ) {
		return;
	}

	// 最初の画像の設定
	var position = {
		prev : images.length - 1,
		now  : 0,
		next : 1
	};

	var html = "";
	$.each(position, function(i, v){
		if ( i == 'now' ) {
			html += "<img class='item active' src='"+ images[v]+"' id='image_" + image_ids[v] + "'>";
		}
		else {
			html += "<img class='item' src='"+ images[v]+"' id='image_" + image_ids[v] + "'>";
		}
	});

	if ( html == "" ) {
		return;
	}

	// 画像のDOMの追加
	$('#myCarousel > .carousel-inner').html(html);
	//$('#slideNumber').html('現在のスライド番号:' + slideNumber + ' / 画像ID: ' + image_ids[slideNumber]);
	$('#link2detail').html('<a href="' + baseUrl + 'album/image/detail/' + image_ids[slideNumber] + '" class="btn"><i class="icon-picture"></i> 詳細</a>');

	show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', comment_limit_default);
},'json');

var next = function() {
	// 次のスライドへ移動
	slideNumber++;

	if (slideNumber > images.length - 1) {
		slideNumber = 0;
	}
	nextSlideNumber = slideNumber + 1;
	if (nextSlideNumber > images.length - 1) {
		nextSlideNumber = 0;
	}
	prevSlideNumber = slideNumber - 1;
	if (prevSlideNumber < 0) {
		prevSlideNumber = images.length - 1;
	}

	$('#myCarousel > .carousel-inner > img:first').empty();
	$('#myCarousel > .carousel-inner').append('<img class="item" src="'+ images[nextSlideNumber]+'" id="image_'+ image_ids[nextSlideNumber] +'">');
	$('#myCarousel').carousel('next');

	show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', comment_limit_default);
}

var prev = function() {
	// 前のスライドに移動
	slideNumber--;

	if (slideNumber < 0) {
		slideNumber = images.length - 1;
	}
	prevSlideNumber = slideNumber - 1;
	if (prevSlideNumber < 0) {
		prevSlideNumber = images.length - 1;
	}
	nextSlideNumber = slideNumber + 1;
	if (nextSlideNumber > images.length - 1) {
		nextSlideNumber = 0;
	}

	$('#myCarousel > .carousel-inner > img:last').empty();
	$('#myCarousel > .carousel-inner').prepend('<img class="item" src="'+ images[prevSlideNumber]+'" id="image_'+ image_ids[prevSlideNumber] +'>');
	$('#myCarousel').carousel('prev');

	show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', comment_limit_default);
}

var slide = function(type) {
	// スライドの実行
	if (type == 'next') {
		next();
	}
	else if ( type == 'prev') {
		prev();
	}
	//$('#slideNumber').html('現在のスライド番号:' + slideNumber + ' / 画像ID: ' + image_ids[slideNumber]);
	$('#link2detail').html('<a href="' + baseUrl + 'album/image/detail/' + image_ids[slideNumber] + '" class="btn"><i class="icon-picture"></i> 詳細</a>');
}

$('.carousel-control').click(function(event) {
	// 横ボタン動作
	if ( images.length < 2 ) {
		return;
	}
	slide($(this).attr('data-action'));
});

$('body').keydown(function(event){
	// キーボード操作によるスライドの移動
	slide(event.keyCode);
});

$(document).on('click', '#listMoreBox_comment', function(){
	//show_list('album/image/comment/api/list/' + image_ids[slideNumber] + '.html', '#comment_list', 0, $('.commentBox:first').attr('id'), true, '#' + $(this).attr('id'));
	var uri = 'album/image/detail/' + image_ids[slideNumber] + '?all_comment=1#comments';
	redirect(uri);
	return false;
});

$(document).on('click', '#btn_album_image_comment_create', function(){
	create_comment(
		'#input_album_image_comment',
		image_ids[slideNumber],
		'album/image/comment/api/create.json',
		'album/image/comment/api/list/' + image_ids[slideNumber] + '.html',
		'#comment_list',
		$('.commentBox:last').attr('id')
	)
	return false;
});

$(document).on('click', '.btn_album_image_comment_delete', function(){
	delete_item('album/image/comment/api/delete.json', get_id_num(($(this).attr('id'))), '#commentBox');
	return false;
});

if (!is_sp()) {
	$(document).on({
		mouseenter:function() {$('#btn_album_image_comment_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
		mouseleave:function() {$('#btn_album_image_comment_delete_' + get_id_num($(this).attr('id'))).hide()}
	}, '.commentBox');
}
