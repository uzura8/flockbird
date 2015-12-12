$(function() {
	var listSelector = '#article_list',
		messageType = $(listSelector).data('message_type'),
		relatedId = $(listSelector).data('id');
		messageGetUri = 'message/api/talks/' + messageType + '/' + relatedId + '.html';

	//$('body').tooltip({
	//	selector: 'a[data-toggle=tooltip]'
	//});

	$('textarea.input_message').css('height', '50px');
	loadMessageDefault(listSelector, messageGetUri);

	var listType = $(listSelector).data('type');
	if (!listType || listType != 'ajax') {
		postLoadMessage();
	}

	//$(document).on('click','.js-ajax-Load_message', function(){
	//	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	//	loadMessage(getData, this, true);
	//	return false;
	//});

	$('#btn_message').click(function(){
		if (GL.execute_flg) return false;

		var body = $.trim($('#textarea_comment').val());// for legacy IE.
		var postData = $(this).data('post_data') ? $(this).data('post_data') : {};
		//$('input[name^="image_tmp"]').each(function(){
		//	postData[this.name] = this.value;
		//});
		if (body.length == 0 && Object.keys(postData).length == 0) return;

		var position = 'append';
		var getData = {
			latest: 1,
			desc: 0
		};

		// 「最新をみる」リンクがあれば削除する
		linkElement = $(listSelector + ' > a.listMoreBox:first');
		if (!empty(linkElement.data('type')) && linkElement.data('type') == 'see_latest') {
			linkElement.remove();
		}

		var sinceId = getSinceId(listSelector, position);
		if (sinceId) getData['since_id'] = sinceId;

		postComment(
			'message/api/create/' + messageType + '/' +relatedId + '.json',
			'#textarea_comment',
			messageGetUri,
			listSelector,
			position,
			getData,
			this,
			'',
			'',
			[resetInputs],
			[postLoadMessage],
			postData,
			false,
			get_term('message'),
			'50px'
		);
		return false;
	});
})

function resetInputs() {
	//$('.upload').addClass('hidden');
	//$('.display_upload_form').removeClass('hidden');
	//$('#files_img').html('');
	//$('#progress_img .progress-bar').css('width', 0);
	//scroll(is_sp() ? '#main_post_box' : 0, 'swing');
}

function loadMessageDefault(listSelector, getUri) {
	//var getData = {'mytimeline': 1};
	var getData = {};
	var max_id = url('?max_id');
	if (max_id) {
		getData['max_id'] = max_id;
		getData['before_link'] = 1;
	}
	loadMessage(listSelector, getUri, getData);
}

function loadMessage(parentListSelector, getUri) {
	var getData        = (arguments.length > 2) ? arguments[2] : {};
	var trigerSelector = (arguments.length > 3) ? arguments[3] : '';
	var isAddHisttory  = (arguments.length > 4) ? Boolean(arguments[4]) : false;

	//var getUri = 'message/api/talks/member/.html';
	//var parentListSelector = '#article_list';

	var pushStateInfo = {};
	if (isAddHisttory) {
		var trigerObj = trigerSelector ? $(trigerSelector) : null;
		if (trigerObj && trigerObj.attr('href') && trigerObj.attr('href') != '#') {
			pushStateInfo['url'] = trigerObj.attr('href');
		} else {
			pushStateInfo['keys'] = ['max_id'];
		}
	}

	loadList(
		getUri,
		parentListSelector,
		trigerSelector,
		'append',
		getData,
		pushStateInfo,
		null,
		null,
		[postLoadMessage]
	);
}

function postLoadMessage() {
}
