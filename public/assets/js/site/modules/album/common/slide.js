$('.carousel').carousel({
	interval: false
});

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
	displayCommentAndOptionalInfo(image_ids[slideNumber]);
},'json');

// slide 切替時に写真のトップへ移動
$("#myCarousel").on('slide.bs.carousel', function () {
	$("#modal_album_slide").animate({scrollTop:15});
});

// modal 多重起動時対応
$(document).on('hidden.bs.modal', '.modal', function () {
	$('.modal:visible').length && $(document.body).addClass('modal-open');
});

// 次回起動時に備えて画像を消しておく
$('#modal_album_slide').on('hidden.bs.modal', function (e) {
  $('#myCarousel > .carousel-inner').html('');
})

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

	displayCommentAndOptionalInfo(image_ids[slideNumber]);
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

	displayCommentAndOptionalInfo(image_ids[slideNumber]);
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
	$('#link2detail').html('<a href="' + get_url('album/image/' + imageId) + '" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-picture"></i> 詳細</a>');
}

var displayCommentAndOptionalInfo = function(image_id) {
	// display optional_info
	var templateLikeLink = Handlebars.compile($('#link_count_and_execute-template').html());
	var getUriOptionalInfo = 'album/image/api/optional_info/' + image_id + '.json';
	$.get(get_url(getUriOptionalInfo), {}, function(result) {
		result['id_prefix'] = 'slide_modal_';
		$('#comment_info').html(templateLikeLink(result)).fadeIn('fast');
	},'json');

	// display comment
	var templatePostComment = Handlebars.compile($('#comment_form-template').html());
	var getUriComments = 'album/image/comment/api/list/' + image_id + '.json';
	var listSelector = '#comment_list';
	$('.commentPostBox').remove();
	loadList(getUriComments, listSelector, '', 'replace', {}, null, '#comment-template', '#comment_count_' + image_id);

	var uid = get_uid();
	if (uid) {
		var val = {
			'id' : image_id,
			'counterSelector' : '#comment_count_' + image_id,
			'listSelector' : listSelector,
			'getUri' : getUriComments,
			'postUri' : 'album/image/comment/api/create/' + image_id + '.json'
		};
		$(listSelector).after(templatePostComment(val));
		$('#textarea_comment_' + image_id).autogrow();
	}
}
