$('.carousel').carousel({
	interval: false
});

var templatePostComment = Handlebars.compile($('#comment_form-template').html());

var images = {}; // 画像ファイル名格納用配列
var image_ids = []; // 画像id格納用配列
var slideNumber = 0;
var slideNumber_max = 0;

var $slideBox = $('#img_comment_box');
var contentType = $slideBox.data('content') ? $slideBox.data('content') : 'album';
var contentId = parseInt($slideBox.data('content_id'));
var startAlbumImageId = $slideBox.data('start_id') ? parseInt($slideBox.data('start_id')) : 0;

var getUriPrefix = (contentType == 'timeline') ? 'timeline' : 'album/image';
var getUri = getUriPrefix + '/api/list/' + contentId + '.json';
var slidLimit = getConfigSlide('slideLimit');
var isAsc = getConfigSlide('sort') == 'asc' ? 1 : 0;
var getData = {limit: slidLimit, asc: isAsc};
if (startAlbumImageId) getData.start_id = startAlbumImageId;

$.get(get_url(getUri), getData, function(json){
	$.each(json.list, function(i, data){
		images[data.id] = getConfigSlide('uploadUriBasePath') + data.file_name.replace(/_/g, '/');
		image_ids.push(data.id);
	});
	slideNumber_max = image_ids.length - 1;
	//image_ids.reverse();

	// 最初の画像の設定
	var position = {
		prev : slideNumber_max,
		now  : 0,
		next : 1
	};
	if (slideNumber_max < 1) {
		position = {
			prev : 0,
			now  : 0,
			next : 0
		};
	}

	var html = "";
	$.each(position, function(i, v){
		if (i == 'now') {
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
	setLink2DetailPage(image_ids[slideNumber]);
	displayComment(image_ids[slideNumber], templatePostComment);
},'json');

$("#myCarousel").on('slide.bs.carousel', function () {
	$("#modal_album_slide").animate({scrollTop:15});
});

var next = function() {
	// 次のスライドへ移動
	slideNumber++;

	if (slideNumber > slideNumber_max) {
		slideNumber = 0;
	}
	nextSlideNumber = slideNumber + 1;
	if (nextSlideNumber > slideNumber_max) {
		nextSlideNumber = 0;
	}

	displayComment(image_ids[slideNumber], templatePostComment);
	if (!$('#image_' + image_ids[nextSlideNumber]).exists()) {
		$('#myCarousel > .carousel-inner').append(getImageTag(images[image_ids[nextSlideNumber]], image_ids[nextSlideNumber]));
	}
	$('#myCarousel').carousel('next');
}

var prev = function() {
	// 前のスライドに移動
	slideNumber--;

	if (slideNumber < 0) {
		slideNumber = slideNumber_max;
	}
	prevSlideNumber = slideNumber - 1;
	if (prevSlideNumber < 0) {
		prevSlideNumber = slideNumber_max;
	}

	displayComment(image_ids[slideNumber], templatePostComment);
	if (!$('#image_' + image_ids[prevSlideNumber]).exists()) {
		$('#myCarousel > .carousel-inner').prepend(getImageTag(images[image_ids[prevSlideNumber]], image_ids[prevSlideNumber]));
	}
	$('#myCarousel').carousel('prev');
}

var slide = function(type) {
	// スライドの実行
	if (type == 'next') {
		next();
	} else if ( type == 'prev') {
		prev();
	}
	setLink2DetailPage(image_ids[slideNumber]);
}

$('.carousel-control').click(function(event) {
	// 横ボタン動作
	if (slideNumber_max < 1) {
		return;
	}
	reset_textarea('#textarea_comment');
	slide($(this).attr('data-slide'));
	return false;
});

var getImageTag = function(imageUri, imageId) {
	var isActive = (arguments.length > 2) ? Boolean(arguments[2]) : false;
	var classValueAdditional = isActive ? ' active' : '';
	var imageUrl = get_url(imageUri, true, false);

	return '<div class="item' + classValueAdditional + '"><img src="' + imageUrl + '" id="image_' + imageId + '"></div>';
}

var setLink2DetailPage = function(imageId) {
	$('#link2detail').html('<a href="' + get_url('album/image/' + imageId) + '" class="btn btn-default"><i class="glyphicon glyphicon-picture"></i> 詳細</a>');
}

var displayComment = function(image_id, template) {
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
		$('#textarea_comment_' + image_id).autogrow();
	}
}
