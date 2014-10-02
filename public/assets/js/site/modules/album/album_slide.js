$('.carousel').carousel({
	interval: false
});

var source   = $("#comment_form-template").html();
var templatePostComment = Handlebars.compile(source);

var album_id = getIdFromUrl();

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
			html += getImageTag(images[image_ids[v]], image_ids[v], true);
		} else {
			html += getImageTag(images[image_ids[v]], image_ids[v]);
		}
	});

	if ( html == "" ) {
		return;
	}

	// 画像のDOMの追加
	$('#myCarousel > .carousel-inner').html(html);
	//$('#slideNumber').html('現在のスライド番号:' + slideNumber + ' / 画像ID: ' + image_ids[slideNumber]);
	$('#link2detail').html('<a href="' + get_url('album/image/' + image_ids[slideNumber]) + '" class="btn btn-default"><i class="glyphicon glyphicon-picture"></i> 詳細</a>');
	displayComment(image_ids[slideNumber], templatePostComment);
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

	displayComment(image_ids[slideNumber], templatePostComment);
	$('#myCarousel > .carousel-inner > img:first').remove();
	$('#myCarousel > .carousel-inner').append(getImageTag(images[image_ids[nextSlideNumber]], image_ids[nextSlideNumber]));
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

	displayComment(image_ids[slideNumber], templatePostComment);
	$('#myCarousel > .carousel-inner > img:last').remove();
	$('#myCarousel > .carousel-inner').prepend(getImageTag(images[image_ids[prevSlideNumber]], image_ids[prevSlideNumber]));
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
	slide($(this).attr('data-slide'));
	return false;
});

function getImageTag(imageUri, imageId) {
	var isActive = (arguments.length > 2) ? Boolean(arguments[2]) : false;
	var classValueAdditional = isActive ? ' active' : '';

	return '<div class="item' + classValueAdditional + '"><img src="' + imageUri + '" id="image_' + imageId + '"></div>';
}

function displayComment(image_id, template) {
	var getUri = 'album/image/comment/api/list/' + image_id + '.json';
	var listSelector = '#comment_list';
	$('.commentPostBox').remove();
	loadList(getUri, listSelector, '', 'replace', {}, null, '#comment-template');

	var uid = get_uid();
	if (uid) {
		var val = {
			'id' : image_id,
			'listSelector' : listSelector,
			'getUri' : getUri,
			'postUri' : 'album/image/comment/api/create/' + image_id + '.json'
		};
		$(listSelector).after(template(val));
	}
}
