function get_url(uri)
{
	var isMediaUri = (arguments.length > 1) ? arguments[1] : false;
	var isReturnPath = (arguments.length > 2) ? arguments[2] : true;
	var isReturnCurrentProtocol = (arguments.length > 3) ? arguments[3] : true;

	if (isMediaUri && get_config('mediaBaseUrl')) return get_config('mediaBaseUrl') + uri;
	if (isReturnPath) return getBasePath() + uri;
	if (isReturnCurrentProtocol) return getBaseUrl(true) + uri;
	return getBaseUrl() + uri;
}

function set_token()
{
	var obj = (arguments.length > 0) ? arguments[0] : false;
	var token_key = get_token_key();

	if (obj == false) {
		return token_key + '=' + get_token();
	} else {
		obj[token_key] = get_token();
		return obj;
	}
}

function redirect(uri)
{
	location.href = get_url(uri, false, false);
}

function getTerm(key)
{
	return get_term(key);
}

function getTerms(keys)
{
	var delimitter = (arguments.length > 1) ? arguments[1] : '';

	var terms = '';
	$.each(keys, function(i, key) {
		if (term.length && delimitter) terms += delimitter;
		terms += getTerm(key);
	});
	return terms;
}

function get_error_message(status)
{
	var default_message = (arguments.length > 1) ? arguments[1] : '';

	switch (status)
	{
		case 401:
			return '認証情報の取得に失敗しました。ログイン後、再度実行してください。';
		default :
			return default_message;
	}
}

function getErrorMessage(responseObj)
{
	var defaultMessage = (arguments.length > 1) ? arguments[1] : '';

	var statusCode = responseObj.status;
	var messages = !empty(responseObj.responseJSON) ? responseObj.responseJSON.error_messages : '';

	if (!empty(messages) && messages.absolute) return messages.absolute;
	if (!empty(messages) && messages.statusCode) return messages.statusCode;

	switch (statusCode)
	{
		case 401:
			return getTerms(['auth', 'info']) + 'の取得に失敗しました。' + getTerm('login') + '後、再度実行してください。';
		case 400:
		case 403:
		case 404:
			return getTerms(['invalid', 'request']) + 'です。';
		case 500:
			return 'サーバエラーが発生しました。';
	}

	if (!empty(messages['default'])) return messages['default'];
	if (defaultMessage) return defaultMessage;

	return 'エラーが発生しました。';
}

function showErrorMessage(responseObj)
{
	var defaultMessage = (arguments.length > 1) ? arguments[1] : '';
	showMessage(getErrorMessageNew(responseObj, defaultMessage));
}

function getErrorMessageNew(responseObj)
{
	var defaultMessage = (arguments.length > 1) ? arguments[1] : '';

	if (empty(responseObj.responseJSON) && !empty(responseObj.responseText)) return responseObj.responseText;
	if (!empty(responseObj.responseJSON.errors.message)) return responseObj.responseJSON.errors.message;

	switch (responseObj.status)
	{
		case 401:
			return getTerms(['auth', 'info']) + 'の取得に失敗しました。' + getTerm('login') + '後、再度実行してください。';
		case 400:
		case 403:
		case 404:
			return getTerms(['invalid', 'request']) + 'です。';
		case 500:
			return 'サーバエラーが発生しました。';
	}

	return 'エラーが発生しました。';
}

function showMessage(msg)
{
	var msgDefault = (arguments.length > 1) ? arguments[1] : '';
	if (!msg && msgDefault) msg = msgDefault;
	$.jGrowl(msg);
}

function getImgUri(file_name, size)
{
	var isReturnNoImagePath = (arguments.length > 2) ? arguments[2] : true;
	var uploadDirName = get_config('upload_dir_name');
	var matches;

	if (empty(file_name)) return isReturnNoImagePath ? 'assets/img/site/noimage.gif' : '';
	matches = file_name.match(/^([a-z]+)_([0-9]+)_(([a-zA-Z0-9]+).(gif|png|jpg))$/);
	if (matches) return uploadDirName + '/img/' + size + '/' + matches[1] + '/' + matches[2] + '/' + matches[3];
	if (file_name.length > 0) return isReturnNoImagePath ? uploadDirName + '/img/' + size + '/' + file_name + '/allnoimage.gif' : '';
	return isReturnNoImagePath ? 'assets/img/site/noimage.gif' : '';
}

function get_loading_image_tag()
{
	var isEncloseBlock = (arguments.length > 0) ? arguments[0] : false;
	var blockSelector = (arguments.length > 1) ? arguments[1] : '';

	var imageTag = '<img src="' + get_url('assets/img/loading.gif') + '">';
	if (!isEncloseBlock) return imageTag;

	var blockTag = '<div class="loading_image"'
	if (blockSelector) blockTag += ' id="' + blockSelector + '"';
	blockTag += '>' + imageTag + '</div>';

	return blockTag;
}

function removeLoadingBlock()
{
	var loadingBlockId = (arguments.length > 0) ? arguments[0] : '';

	if (loadingBlockId) {
		$('#' + loadingBlockId).remove();
		return;
	}

	$('.loading_image').remove();
}

function setLoading(blockSelector)
{
	var trigerSelector = (arguments.length > 1) ? arguments[1] : '';
	var loadingBlockSelector = (arguments.length > 2) ? arguments[2] : '';

	if (trigerSelector) {
		$(trigerSelector).attr('disabled', true);
		$(trigerSelector).html(get_loading_image_tag(true, loadingBlockSelector));
	} else {
		$(blockSelector).append(get_loading_image_tag(true, loadingBlockSelector));
	}
}

function removeLoading(blockSelector)
{
	var trigerSelector = (arguments.length > 1) ? arguments[1] : '';
	var loadingBlockId = (arguments.length > 2) ? arguments[2] : '';
	var isRemoveTrigerSelector = (arguments.length > 3) ? Boolean(arguments[3]) : false;
	var trigerHtml = (arguments.length > 4) ? arguments[4] : '';

	if (trigerSelector) {
		$(trigerSelector).attr('disabled', false);
		if (isRemoveTrigerSelector) {
			$(trigerSelector).remove();
			return;
		} else if (trigerHtml) {
			$(trigerSelector).html(trigerHtml);
		}
	}
	removeLoadingBlock(loadingBlockId);
}

function displayLoading() {
	var isEnd = (arguments.length > 0) ? Boolean(arguments[0]) : false;
	$('#loading-view').remove();
	if(isEnd) return;
	$('<div id="loading-view"><img src="' + get_url('assets/img/site/loading_l.gif') + '"></div>').appendTo('body');
}

function addHistory(pushStateInfo, getData) {
	if (!('pushState' in history)) return;
	if (!pushStateInfo) return;
	var HistoryUrl = '';
	if (pushStateInfo.url && pushStateInfo.url != '#') {
		HistoryUrl = pushStateInfo.url;
	} else if (pushStateInfo.fullPath) {
		HistoryUrl = pushStateInfo.fullPath;
	} else if (pushStateInfo.uri) {
		HistoryUrl = get_url(pushStateInfo.uri);
	} else {
		HistoryUrl = get_url(getCurrentPath());
	}
	if (pushStateInfo.keys) {
		var query = '';
		if ($.type(pushStateInfo.keys) == 'string') pushStateInfo.keys = JSON.parse(pushStateInfo.keys);
		$.each(pushStateInfo.keys, function(index, val) {
			if (!getData[val]) return;
			if (index > 0) query += '&';
			query += val + '=' + getData[val];
		});
		if (query.length) {
			var delimitter = (HistoryUrl.indexOf('?') == -1) ? '?' : '&';
			HistoryUrl += delimitter + query;
		}
	}
	
	window.history.pushState(null, null, HistoryUrl);// history に追加
}

function loadList(getUri) {
	var parentListSelector = (arguments.length > 1) ? arguments[1] : 0;
	var trigerSelector     = (arguments.length > 2) ? arguments[2] : '';
	var position           = (arguments.length > 3) ? arguments[3] : 'replace';// params: replace / append / prepend
	var getData            = (arguments.length > 4) ? arguments[4] : {};
	var pushStateInfo      = (arguments.length > 5) ? arguments[5] : null;
	var templateSelector   = (arguments.length > 6) ? arguments[6] : '';
	var counterSelector    = (arguments.length > 7) ? arguments[7] : '';
	var callbackFuncs      = (arguments.length > 8) ? arguments[8] : null;
	var isRemoveTrigger    = (arguments.length > 9) ? Boolean(arguments[9]) : false;
	var trigerHtml         = trigerSelector ? $(trigerSelector).html() : '';
	var html;

	var template = templateSelector ? Handlebars.compile($(templateSelector).html()) : null;
	$.ajax({
		url : get_url(getUri),
		type : 'GET',
		dataType : getUri.split('.').pop() == 'html' ? 'text' : 'json',
		data : getData,
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			setLoading(parentListSelector, trigerSelector, 'list_loading_image');
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			removeLoading(parentListSelector, trigerSelector, 'list_loading_image', null, isRemoveTrigger ? null : trigerHtml);
		},
		success: function(result) {
			if (template) {
				html = template(result);
			} else if (!empty(result.html)) {
				html = result.html;
			} else  {
				html = result;
			}
			if (isRemoveTrigger) {
				var trigarObj = $(trigerSelector);
				if (position == 'prepend') {
					trigarObj.before(html).fadeIn('fast');
				} else {
					trigarObj.after(html).fadeIn('fast');
				}
				trigarObj.remove();
			} else {
				if (position == 'prepend') {
					$(parentListSelector).prepend(html).fadeIn('fast');
				} else if (position == 'append') {
					$(parentListSelector).append(html).fadeIn('fast');
				} else {
					$(parentListSelector).html(html).fadeIn('fast');
				}
			}
			if (templateSelector && counterSelector && result.count) {
					$(counterSelector).html(result.count);
			}
			if (callbackFuncs) {
				$.each(callbackFuncs, function() {
					this();
				});
			}
			if (pushStateInfo) addHistory(pushStateInfo, getData);
			if (!empty(result.message)) showMessage(result.message);
		},
		error: function(result) {
			GL.execute_flg = false;
			removeLoading(parentListSelector, trigerSelector, 'list_loading_image', null, trigerHtml);
			showErrorMessage(result, '読み込みに失敗しました。');
		}
	});
}

function delete_item(uri)
{
	var deleteTargetSelector = (arguments.length > 1) ? arguments[1] : '';
	var id = (arguments.length > 2) ? arguments[2] : 0;
	var itemTerm = (arguments.length > 3) ? arguments[3] : '';
	var confirmMsg = (arguments.length > 4 && arguments[4].length) ? arguments[4] : '削除します。よろしいですか?';
	var counterSelector = (arguments.length > 5) ? arguments[5] : '';

	apprise(confirmMsg, {'confirm':true}, function(r) {
		if (r == true) deleteExecuteAjax(uri, deleteTargetSelector, id, true, itemTerm, counterSelector);
	});
}

function deleteExecuteAjax(postUri, deleteTargetSelector)
{
	var id = (arguments.length > 2) ? parseInt(arguments[2]) : 0;
	var is_display_message_success = (arguments.length > 3) ? arguments[3] : true;
	var itemTerm = (arguments.length > 4) ? arguments[4] : '';
	var counterSelector = (arguments.length > 5) ? arguments[5] : '';
	var msgPrefix = (itemTerm.length > 0) ? itemTerm + 'を' : '';
	var postData = {_method: 'DELETE'};
	if (id) postData['id'] = id;

	$.ajax({
		url : get_url(postUri),
		dataType : 'json',
		data : set_token(postData),
		timeout: get_config('default_ajax_timeout'),
		type : 'POST',
		success: function(response){
			$(deleteTargetSelector).fadeOut();
			$(deleteTargetSelector).remove();
			updateCounter(counterSelector, -1);
			if (is_display_message_success) {
				var message = !empty(response.message) ? response.message : msgPrefix + '削除しました。';
				showMessage(message);
			}
		},
		error: function(response){
			showErrorMessage(response, '削除に失敗しました。');
		}
	});
}

function reset_textarea()
{
	var textareaSelector = (arguments.length > 0) ? arguments[0] : 'textarea';
	var textareaHeight   = (arguments.length > 1) ? arguments[1] : '';
	if (!textareaHeight) textareaHeight = get_config('default_form_comment_textarea_height');

	$(textareaSelector).val('');
	$(textareaSelector).css('height', textareaHeight);
}

function getSinceId(listSelector, position)
{
	var dataAttr2listId = (arguments.length > 2) ? arguments[2] : 'id';
	var insertPosition,
		nextElement,
		listMoreBoxObj,
		listMoreBoxGetData;

	if (position == 'replace') return '';
	if (!$(listSelector).html().replace(/[\r\n\s]+/, '')) return '';

	insertPosition = (position == 'prepend') ? 'first' : 'last';
	nextElement = $(listSelector + ' > div:' + insertPosition);
	if (!empty(nextElement) && nextElement.attr('id')) {
		return parseInt($('#' + nextElement.attr('id')).data(dataAttr2listId));
	}

	listMoreBoxObj = $(listSelector).find('.listMoreBox');
	if (!empty(listMoreBoxObj)) {
		listMoreBoxGetData = $(listMoreBoxObj).data('get_data');
		if (!empty(listMoreBoxGetData) && listMoreBoxGetData.max_id) return parseInt(listMoreBoxGetData.max_id);
	}
	return 0;
}

function postComment(postUri, textareaSelector, getUri, listSelector)
{
	var position          = (arguments.length > 4)  ? arguments[4] : 'replace';
	var getData           = (arguments.length > 5)  ? arguments[5] : {};
	var trigerSelector    = (arguments.length > 6)  ? arguments[6] : '';
	var counterSelector   = (arguments.length > 7)  ? arguments[7] : '';
	var templateSelector  = (arguments.length > 8)  ? arguments[8] : '';
	var callbackFuncs     = (arguments.length > 9)  ? arguments[9] : null;
	var callbackFuncsAfterLoadList = (arguments.length > 10) ? arguments[10] : null;
	var postData          = (arguments.length > 11) ? arguments[11] : {};
	var isCheckInput      = (arguments.length > 12) ? arguments[12] : true;
	var postedArticleTerm = (arguments.length > 13) ? arguments[13] : '';
	var textareaHeight    = (arguments.length > 14) ? arguments[14] : '33px';

	if (GL.execute_flg) return false;
	if (!postUri) return false;

	//var body = $(textareaSelector).val().trim();
	var body = $.trim($(textareaSelector).val());// for legacy IE.
	if (isCheckInput && !body.length) return;
	postData['body'] = body;
	postData = set_token(postData);

	if (!postedArticleTerm) postedArticleTerm = get_term('comment');
	var trigerSelectorHtml = (trigerSelector) ? $(trigerSelector).html() : '';

	$.ajax({
		url : get_url(postUri),
		type : 'POST',
		dataType : 'json',
		data : postData,
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			setLoading(listSelector, trigerSelector, 'btn_loading_image');
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			removeLoading(listSelector, trigerSelector, 'btn_loading_image');
			if (trigerSelector) $(trigerSelector).html(trigerSelectorHtml);
		},
		success: function(response){
			var msg = !empty(response.message) ? response.message : postedArticleTerm + 'を投稿しました。';
			showMessage(msg);
			loadList(getUri, listSelector, '', position, getData, null, templateSelector, counterSelector, callbackFuncsAfterLoadList);
			if (!templateSelector) updateCounter(counterSelector);
			reset_textarea(textareaSelector, textareaHeight);
			if (callbackFuncs) {
				$.each(callbackFuncs, function() {
					this();
				});
			}
			if (!empty(response.shareFacebook)) popupFacebookShareDialog(response.shareFacebook.obj);
		},
		error: function(response){
			showMessage(getErrorMessage(response, postedArticleTerm + 'の投稿に失敗しました。'));
		}
	});
}

function updateCounter(counterSelector)
{
	var addValue = (arguments.length > 1) ? parseInt(arguments[1]) : 1;

	if (!counterSelector.length) return false;
	if (!$(counterSelector).size()) return false;
	var count = parseInt($(counterSelector).html()) + addValue;
	if (count < 0) count = 0;
	$(counterSelector).html(count);
}

function loadItem(container_attribute, item_attribute)
{
	var finished_msg = (arguments.length > 2) ? arguments[2] : '';
	var loading_image_url = (arguments.length > 3) ? arguments[3] : get_url('assets/img/site/loading_l.gif');

	var $container = $(container_attribute);
	$container.infinitescroll({
		navSelector  : '#page-nav',   // ページのナビゲーションを選択
		nextSelector : '#page-nav a', // 次ページへのリンク
		itemSelector : item_attribute,    // 持ってくる要素のclass
		loadingImg   : loading_image_url
	});
}

function loadPopover(linkSelector, contentSelector) {
	var content = (arguments.length > 2) ? arguments[2] : '';
	var contentUrl = (arguments.length > 3) ? arguments[3] : '';
	var inputAttrs = (arguments.length > 4) ? arguments[4] : '';

	if (!content && !contentUrl) return false;
	$(linkSelector).popover({html: true})
	$(linkSelector).click(function(){
		if (content) {
			$(contentSelector).html(content);
		} else {
			$(contentSelector).load(contentUrl);
		}
		//if (inputAttrs.length > 0) $(inputAttrs).focus();
		if (!is_sp()) {
			$(window).resize(function(e) {
				e.preventDefault()
				$(linkSelector).each(function (){
					if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('a[data-toggle=popover]').has(e.target).length === 0 && checkIsInput(inputAttrs) === false) {
						$(this).popover('hide');
						return;
					}
				});
			});
		}
		return false;
	})
}

function update_public_flag(selfDomElement) {
	var public_flag          = $(selfDomElement).data('public_flag');
	var public_flag_original = $(selfDomElement).data('public_flag_original');

	if (is_expanded_public_range(public_flag_original, public_flag)) {
		apprise('公開範囲が広がります。実行しますか？', {'confirm':true}, function(r) {
			if (r == true) check_is_update_children_public_flag_before_update(selfDomElement);
		});
	} else {
		check_is_update_children_public_flag_before_update(selfDomElement);
	}

	return false;
}

function check_is_update_children_public_flag_before_update(selfDomElement) {
	var model = $(selfDomElement).data('model');
	var model = $(selfDomElement).data('child_model');
	var have_children_public_flag = $(selfDomElement).data('have_children_public_flag') ? $(selfDomElement).data('have_children_public_flag') : 0;
	var child_model = $(selfDomElement).data('child_model') ? $(selfDomElement).data('child_model') : '';

	if (have_children_public_flag) {
		apprise(get_term(child_model) + 'の' + get_term('public_flag') + 'も変更しますか？', {'verify':true}, function(r) {
			if (r == true) {
				update_public_flag_execute(selfDomElement, 1);
			} else {
				update_public_flag_execute(selfDomElement);
			}
		});
	} else {
		update_public_flag_execute(selfDomElement);
	}

	return false;
}

function update_public_flag_execute(selfDomElement) {
	var is_update_children_public_flag = (arguments.length > 1) ? arguments[1] : 0;
	var id          = $(selfDomElement).data('id');
	var model       = $(selfDomElement).data('model');
	var public_flag = $(selfDomElement).data('public_flag');
	var model_uri   = $(selfDomElement).data('model_uri');
	var post_uri       = $(selfDomElement).data('post_uri')  ? $(selfDomElement).data('post_uri')  : '';
	var icon_only_flag = $(selfDomElement).data('icon_only') ? $(selfDomElement).data('icon_only') : 0;
	var have_children_public_flag = $(selfDomElement).data('have_children_public_flag') ? $(selfDomElement).data('have_children_public_flag') : 0;
	var child_model    = $(selfDomElement).data('child_model') ? $(selfDomElement).data('child_model') : '';
	var is_refresh     = $(selfDomElement).data('is_refresh')  ? $(selfDomElement).data('is_refresh')  : 0;
	var is_no_msg      = $(selfDomElement).data('is_no_msg')   ? $(selfDomElement).data('is_no_msg')   : 0;

	var text = $(selfDomElement).html();
	var parentElement = $(selfDomElement).parent('li');
	var buttonElement = $(parentElement).parents('div.btn-group');

	var post_data = {
		'id'             : id,
		'public_flag'    : public_flag,
		'model'          : model,
		'icon_only_flag' : icon_only_flag,
		'have_children_public_flag'      : have_children_public_flag,
		'is_update_children_public_flag' : is_update_children_public_flag,
	};
	uri = post_uri ? post_uri : model_uri +'/api/update_public_flag.html';
	post_data = set_token(post_data);
	$.ajax({
		url : get_url(uri),
		type : 'POST',
		dataType : 'text',
		data : post_data,
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			$(selfDomElement).remove();
			$(parentElement).html('<span>' + get_loading_image_tag( + '</span>'));
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
		},
		success: function(result, status, xhr){
			$(buttonElement).html(result);
			$(buttonElement).removeClass('open');
			var msg = is_no_msg ? '' : get_term('public_flag') + 'を変更しました。';
			if (is_refresh) {
				var query_strring = '';
				if (msg.length > 0) {
					var delimitter = (url('?').length > 0) ? '&' : '?';
					query_strring = delimitter + 'msg=' + msg;
				}
				location.href=url() + query_strring;
			} else {
				if (msg.length > 0) $.jGrowl(msg);
			}
		},
		error: function(result){
			$(parentElement).html(selfDomElement);
			//var resData = $.parseJSON(result.responseText);
			//var message = resData.error.message ? resData.error.message : get_term('public_flag') + 'の変更に失敗しました。';
			//$.jGrowl(get_error_message(result['status'], message));
			$.jGrowl(get_error_message(result['status'], get_term('public_flag') + 'の投稿に失敗しました。'));
		}
	});
}

function is_expanded_public_range(original_public_flag, changed_public_flag) {
	if (typeof changed_public_flag === "undefined") return false;
	if (original_public_flag == changed_public_flag) return false;
	if (original_public_flag == 0) return true;
	if (changed_public_flag == 0) return false;
	if (original_public_flag > changed_public_flag) return true;

	return false;
}

function get_public_flag_select_button_html(selected_value) {
	var is_label_only      = (arguments.length > 1) ? arguments[1] : false;
	var without_parent_box = (arguments.length > 2) ? arguments[2] : false;

	var selected_key = String(selected_value);
	var items = get_public_flags();

	var html = '';
	if (!without_parent_box) html += '<div class="btn-group public_flag pull-right">' + "\n";
	html += '<button class="btn dropdown-toggle btn-mini ' + items[selected_key]['btn_color'] + '" id="public_flag_selector" data-toggle="dropdown">' + "\n";
	html += items[selected_key]['icon'];
	if (!is_label_only) html += items[selected_key]['name'];
	html += '<span class="caret"></span>' + "\n";
	html += '</button>' + "\n";
	html += '<ul class="dropdown-menu">' + "\n";
	$.each(items, function(i, val) {
		var key = String(i);
		if (key == selected_key) {
			html += '<li><span class="disabled">' + items[key]['icon'] + items[key]['name'] + '</span></li>' + "\n";
		} else {
			html += '<li><a href="#" class="select_public_flag" data-public_flag="' + key + '" >' + items[key]['icon'] + items[key]['name'] + '</a></li>' + "\n";
		}
	});
	html += '</ul>' + "\n";
	if (!without_parent_box) html += '</div>' + "\n";

	return html;
}

function setup_simple_validation_required_popover(input_atter) {
	$(input_atter).popover({
		placement: 'bottom',
		content: 'このフィールドは必須入力です。',
		trigger: 'manual',
		animation: true,
		delay: { show: 500, hide: 100 }
	});
}

function simple_validation_required(input_atter) {
	//var input_val = $(input_atter).val().trim();
	var input_val = $.trim($(input_atter).val());// for legacy IE.

	if (input_val.length == 0) {
		$(input_atter).popover('show');
		isPopover = true;
		return false;
	}
	return true;
}

function checkIsInput(inputAttrs) {
	var is_input = false;
	for (i = 0; i < inputAttrs.length; i++) {
		var val = $(inputAttrs[i]).val();
		if(val && val.length > 0) is_input = true;
	}

	return is_input;
}

function load_masonry_item(container_attribute, item_attribute)
{
	var load_more    = (arguments.length > 2) ? arguments[2] : true;
	var finished_msg = (arguments.length > 3) ? arguments[3] : '';
	var loading_image_url = (arguments.length > 4) ? arguments[4] : get_url('assets/img/site/loading_l.gif');

	var $container = $(container_attribute);
	$container.imagesLoaded(function(){
		$container.masonry({
			itemSelector : item_attribute,
			isFitWidth: true,
			isAnimated: true,
			animationOptions: {
					duration: 400
			}
		});
	});
	if (load_more) {
		$container.infinitescroll({
			navSelector  : '#page-nav',   // ページのナビゲーションを選択
			nextSelector : '#page-nav a', // 次ページへのリンク
			itemSelector : item_attribute,    // 持ってくる要素のclass
			loading: {
					finishedMsg: finished_msg, //次のページがない場合に表示するテキスト
					img: loading_image_url //ローディング画像のパス
				}
			},
			// trigger Masonry as a callback
			function( newElements ) {
				// TODO: add carousel items.
				var $newElems = $( newElements ).css({ opacity: 0 });
				$newElems.imagesLoaded(function(){
					$newElems.animate({ opacity: 1 });
					$container.masonry( 'appended', $newElems, true );
				});
			}
		);
	}
}

function sendArticle(btnObj, postData, post_uri, parent_box_attr) {
	var add_before  = (arguments.length > 4 && arguments[4]) ? arguments[4] : false;
	var msgSuccess = (arguments.length > 5 && arguments[5]) ? arguments[5] : '投稿に成功しました。';
	var msgError   = (arguments.length > 6 && arguments[6]) ? arguments[6] : '投稿に失敗しました。';

	var btn_html = $(btnObj).html();
	$.ajax({
		url : get_url(post_uri),
		type : 'POST',
		dataType : 'text',
		data : set_token(postData),
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			$(btnObj).attr('disabled', true);
			$(btnObj).html(get_loading_image_tag());
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			$(btnObj).attr('disabled', false);
			$(btnObj).html(btn_html);
		},
		success: function(result) {
			if (add_before) {
				$(parent_box_attr).prepend(result).fadeIn();
			} else {
				$(parent_box_attr).append(result).fadeIn();
			}
			$.each(postData, function(key, val) {
				var input_attr = '#input_' + key;
				if ($(input_attr) != null) $(input_attr).val('');
			});
			//$.jGrowl('profile 選択肢を作成しました。');
			showMessage(msgSuccess);
		},
		error: function(result){
			GL.execute_flg = false;
			//$.jGrowl(get_error_message(result['status'], 'profile 選択肢の作成に失敗しました。'));
			showErrorMessage(result, msgError);
		}
	});
}

function display_form4value(value, target_values, disp_target_forms) {
	var prefix  = (arguments.length > 3 && arguments[3]) ? arguments[3] : '#form_';
	var suffix  = (arguments.length > 4 && arguments[4]) ? arguments[4] : '_block';

	if ($.inArray(value, target_values) == -1) {
		disp_target_forms.forEach(function(target) {
			var block = $(prefix + target + suffix);
			if (block.hasClass('hidden') == false) block.addClass('hidden');
		});
	} else {
		disp_target_forms.forEach(function(target) {
			var block = $(prefix + target + suffix);
			if (block.hasClass('hidden') == true) block.removeClass('hidden');
		});
	}
}

function update_like_status(selfDomElement) {
	var counterSelector = $(selfDomElement).data('count');
	var selfDomElement_html = $(selfDomElement).html();
	var postUrl = $(selfDomElement).data('uri');
	var postData = {};
	var messageDefault;
	if (!postUrl) return;

	$.ajax({
		url : get_url(postUrl),
		type : 'POST',
		dataType : 'json',
		data : set_token(postData),
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			if (selfDomElement) {
				$(selfDomElement).attr('disabled', true);
				$(selfDomElement).html(get_loading_image_tag());
			}
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			$(selfDomElement).attr('disabled', false);
		},
		success: function(result){
			if (result.result) {
				selfDomElement_html = get_term('undo_like');
				messageDefault = get_term('like') + 'しました。';
			} else {
				$(selfDomElement).removeClass('btn-primary');
				selfDomElement_html = get_term('do_like');
				messageDefault = get_term('like') + 'を取り消しました。';
			}
			if (counterSelector) $(counterSelector).html(result.count);
			$(selfDomElement).html(selfDomElement_html);
			showMessage(result.message, messageDefault);
		},
		error: function(result){
			$(selfDomElement).html(selfDomElement_html);
			showErrorMessage(result, get_term('like') + 'に失敗しました。');
		}
	});
}

function updateToggle(selfDomElement) {
	var postUrl = $(selfDomElement).data('uri');
	var postData = $(this).data('post_data') ? $(this).data('post_data') : {};
	var selfDomElementHtmlBefore = $(selfDomElement).html();

	if (!postUrl) return;

	$.ajax({
		url : get_url(postUrl),
		type : 'POST',
		dataType : 'json',
		data : set_token(postData),
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			if (selfDomElement) {
				$(selfDomElement).attr('disabled', true);
				$(selfDomElement).html(get_loading_image_tag());
			}
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			$(selfDomElement).attr('disabled', false);
		},
		success: function(response){
			if (!empty(response.is_replace)) {
				$(selfDomElement).replaceWith(response.html);
			} else {
				if (!empty(response.attr)) {
					if (response.attr.class.add) {
						$(selfDomElement).addClass(response.attr.class.add);
					} else if (response.attr.class.remove) {
						$(selfDomElement).removeClass(response.attr.class.remove);
					}
				}
				$(selfDomElement).html(response.html);
			}
			if (!empty(response.message)) showMessage(response.message);
		},
		error: function(response, status){
			showErrorMessage(response);
		}
	});
}

function execute_post(uri){
	var post_data = (arguments.length > 1) ? arguments[1] : {};
	var post_url = uri.match(/^https?:\/\//) ? uri : get_url(uri, false, false, true);

	post_data = set_token(post_data);
	$('<form>', {action: post_url, method: 'post', id: 'tmp_form'}).appendTo(document.body);
	var tmp_form = $('#tmp_form');
	$.each(post_data, function(key, val){
		tmp_form.append($('<input>', {type: 'hidden', name: key, value: val}));
	});
	tmp_form.submit();
}

function post_submit(selfDomElement) {
	var postData    = (arguments.length > 1) ? arguments[1] : {},
			uri         = $(selfDomElement).data('uri'),
			href        = $(selfDomElement).attr('href'),
			confirmMsg  = $(selfDomElement).data('msg'),
			destination = $(selfDomElement).data('destination'),
			postUri;
	if (href && href == '#') href = '';
	if (empty(href) && empty(uri)) return false;
	postUri = href ? href : get_url(uri);
	if (destination) postData['destination'] = destination;
	if (confirmMsg) {
		apprise(confirmMsg, {confirm: true}, function(r) {
			if (r == true) execute_post(postUri, postData);
		});
		return;
	}
	execute_post(postUri, postData);
}

function execute_simple_delete(selfDomElement) {
	var postId  = parseInt($(selfDomElement).data('id'));
	var postUri  = $(selfDomElement).data('uri');
	var parentSelector = $(selfDomElement).data('parent');
	var msg = $(selfDomElement).data('msg') ? $(selfDomElement).data('msg') : '';
	var counterSelector = $(selfDomElement).data('counter') ? $(selfDomElement).data('counter') : '';
	if (!postId && !postUri) return false;
	if (!parentSelector) parentSelector = '#' + postId;
	delete_item(postUri, parentSelector, postId, '', msg, counterSelector);
}

function execute_simple_post(selfDomElement) {
	var post_keys = (arguments.length > 1) ? arguments[1] : [];
	var parent_box = $(selfDomElement).data('parent_box') ? $(selfDomElement).data('parent_box') : 'jqui-sortable';
	var post_uri = $(selfDomElement).data('uri');
	if (!post_uri) return false;

	var post_data = {};
	var has_error = false;
	if (post_keys.length > 0) {
		$.each(post_keys, function(i, post_key) {
			var input_attr = '#input_' + post_key;
			//var value = $(input_attr).val().trim();
			var value = $.trim($(input_attr).val());// for legacy IE.
			if (value.length == 0) {
				has_error = true;
				return false;
			}
			post_data[post_key] = value;
		});
	} else {
		var input_name = $(selfDomElement).data('input_name') ? $(selfDomElement).data('input_name') : 'name';
		var input_attr = '#input_' + input_name;
		//var value = $(input_attr).val().trim();
		var value = $.trim($(input_attr).val());// for legacy IE.
		if (!value.length) return false;
		post_data[input_name] = value;
	}
	if (has_error) return false;

	var id = $(selfDomElement).data('id') ? parseInt($(selfDomElement).data('id')) : 0;
	if (id > 0) post_data['id'] = id;
	var msg_success = '作成しました。';
	var msg_error = '作成に失敗しました。';
	sendArticle(selfDomElement, post_data, post_uri, '#' + parent_box, false, msg_success, msg_error);
}

function check_editable_content(selfDomElement) {
	if (Boolean($(selfDomElement).data('hidden_btn_absolute'))) return true;
	var uid = get_uid();
	if (!uid) return false;
	var auther_id = $(selfDomElement).data('auther_id') ? parseInt($(selfDomElement).data('auther_id')) : 0;
	var parent_auther_id = $(selfDomElement).data('parent_auther_id') ? parseInt($(selfDomElement).data('parent_auther_id')) : 0;
	if (!auther_id && !parent_auther_id) return true;
	if (auther_id && auther_id == uid) return true;
	if (parent_auther_id && parent_auther_id == uid) return true;
	return false;
}

function close_dropdown_menu(selfDomElement) {
	$(selfDomElement).parent('li').parent('ul.dropdown-menu').parent('div').removeClass('open');
}

function removeItems(linkCommentSelector) {
	$(linkCommentSelector).each(function() {
		$(this).remove();
	});
}

function removeNext(selfDomElement) {
	$(selfDomElement).next().remove();
}

function scroll() {
	var targetSelector = (arguments.length > 0) ? arguments[0] : '';
	var easing = (arguments.length > 1) ? arguments[1] : 'swing';

	var position = targetSelector ? $(targetSelector).offset().top : 0;
	$('html,body').animate({scrollTop: position}, easing);
}

function renderSiteSummary() {
	if (!get_config('is_render_site_summary_at_client_side')) return false;

	var template = Handlebars.compile($('#site_summary-template').html());
	var getUrl = get_url('site/opengraph/api/analysis');
	$('.site_summary_unrendered').each(function() {
		var getUri = $(this).data('uri');
		var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
		var $selfObj = $(this);
		$.ajax({
			url : getUrl,
			type : 'GET',
			dataType : 'json',
			data : getData,
			cache : true,
			timeout: get_config('default_ajax_timeout'),
			success: function(result) {
				if (result) {
					$selfObj.html(template(result));
					$selfObj.removeClass('site_summary_unrendered');
					$selfObj.addClass('site_summary');
				} else {
					$selfObj.removeClass('site_summary_unrendered');
					$selfObj.addClass('site_summary_renderfailed');
				}
			},
			error: function(result) {
				//showMessage(get_error_message(result['status'], '読み込みに失敗しました。'));
			}
		});
	});
}

function showCommentInput(id, targetBlockName, postUri, getUri, isFocus) {
	if (!get_uid() || !id || !targetBlockName.length) return;
	if ($('#link_show_comment_form_' + id).size()) $('#link_show_comment_form_' + id).hide();
	var textareaSelector = '#textarea_comment_' + id;
	if ($(textareaSelector).size() == 0) {
		var source   = $("#comment_form-template").html();
		var template = Handlebars.compile(source);
		var val = {
			'id' : id,
			'postUri' : postUri,
			'getUri' : getUri,
			'listSelector' : '#comment_list_' + id,
			'counterSelector' : '#comment_count_' + id
		};
		$('#' + targetBlockName).html(template(val));
	}
	$(textareaSelector).autogrow();
	if (isFocus) $(textareaSelector).focus();
}

function simpleAjaxPost(postUri)
{
	var postData           = (arguments.length > 1) ? arguments[1] : {},
			trigerSelector     = (arguments.length > 2) ? arguments[2] : '',
			defaultMessage     = (arguments.length > 3) ? arguments[3] : '',
			callbackFuncs      = (arguments.length > 4) ? arguments[4] : [],
			trigerSelectorHtml = (trigerSelector) ? $(trigerSelector).html() : '';

	if (GL.execute_flg) return false;
	if (!postUri) return false;

	postData = set_token(postData);
	$.ajax({
		url : get_url(postUri),
		type : 'POST',
		dataType : 'json',
		data : postData,
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			setLoading(null, trigerSelector, 'btn_loading_image');
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			removeLoading(null, trigerSelector, 'btn_loading_image');
			if (trigerSelector) $(trigerSelector).html(trigerSelectorHtml);
		},
		success: function(response){
			var msg = !empty(response.message) ? response.message : defaultMessage;
			showMessage(msg);
			if (callbackFuncs) {
				$.each(callbackFuncs, function() {
					this();
				});
			}
		},
		error: function(response){
			GL.execute_flg = false;
			removeLoading(null, trigerSelector, 'btn_loading_image');
			if (trigerSelector) $(trigerSelector).html(trigerSelectorHtml);
			showMessage(getErrorMessage(response));
		}
	});
}

function popupFacebookShareDialog(obj)
{
	if (typeof FB === 'undefined') return false;

	var facebookObj = arrayMerge({method: 'feed'}, obj);
	function callback(response) {
		if (response && response.post_id) {
			showMessage('Facebook に投稿しました。');
		}
	}
	apprise('Facebook に投稿しますか？', {'confirm':true}, function(r) {
		if (r == true) FB.ui(facebookObj, callback);
	});
}
