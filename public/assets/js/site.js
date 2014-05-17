$('textarea.autogrow').autogrow();

$(document).on('click','.btn_follow', function(){
	if (GL.execute_flg) return false;
	update_follow_status(this);
	return false;
});

$(document).on('click','.js-simplePost', function(){
	$(this).parent('li').parent('ul.dropdown-menu').parent('div.btn-group').removeClass('open');
	post_submit(this);
	return false;
});

$(document).on('click','.js-ajax-delete', function(){
	$(this).parent('li').parent('ul.dropdown-menu').parent('div.btn-group').removeClass('open');
	execute_simple_delete(this);
	return false;
});

function get_id_from_url()
{
	var is_parseInt = (arguments.length > 0) ? arguments[0] : true;

	var id = url('-1');
	if (is_parseInt) id = parseInt(id);

	return id;
}

function get_id_num(id_string)
{
	if (typeof id_string === "undefined") return false;

	var matches = id_string.match(/^[a-z0-9_]+_(\d+)$/i);
	if (matches) return matches[1];

	return false;
}

function get_url(uri)
{
	return get_baseUrl() + uri;
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

function get_loading_image_tag()
{
	var is_enclose_block = (arguments.length > 0) ? arguments[0] : false;

	var tag = '<img src="' + get_baseUrl() + 'assets/img/loading.gif' + '">';
	if (is_enclose_block) tag = '<div class="loading_image">' + tag + '</div>';

	return tag;
}

function redirect(uri)
{
	location.href = get_url(uri);
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

function show_list(uri, list_block_id) {
	var record_limit         = (arguments.length > 2 && arguments[2]) ? arguments[2] : 0;
	var next_element_id_name = (arguments.length > 3 && arguments[3]) ? arguments[3] : '';
	var is_insert_before     = (arguments.length > 4 && arguments[4]) ? arguments[4] : false;
	var selfDomElement       = (arguments.length > 5) ? arguments[5] : false;
	var get_data_additional  = (arguments.length > 6) ? arguments[6] : {};
	var next_element_id_num  = (arguments.length > 7) ? arguments[7] : 0;

	if (!selfDomElement) {
		$(list_block_id).append('<div class="loading_image" id="list_loading_image">' + get_loading_image_tag() + '</div>');
	}

	var baseUrl = get_baseUrl();
	var get_url = baseUrl + uri;
	var get_data = get_data_additional;
	get_data['nochache']  = (new Date()).getTime();
	get_data['limit'] = record_limit;

	if (!next_element_id_num && next_element_id_name.length > 0) {
		next_element_id_num = get_id_num(next_element_id_name);
	}
	if (next_element_id_num) {
		var key = is_insert_before ? 'after_id' : 'before_id';
		get_data[key] = next_element_id_num;
	}

	$.ajax({
		url : get_url,
		type : 'GET',
		dataType : 'text',
		data : get_data,
		timeout: 10000,
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			if (selfDomElement) {
				$(selfDomElement).html(get_loading_image_tag(true));
			}
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
		},
		success: function(result) {
			if (selfDomElement) $(selfDomElement).remove();

			if (next_element_id_num) {
				if (is_insert_before) {
					$(list_block_id).prepend(result).fadeIn('fast');
				} else {
					$(list_block_id).append(result).fadeIn('fast');
				}
			} else {
				$(list_block_id).html(result).fadeIn('fast');
			}
			$(list_block_id).find('textarea').autogrow();
		},
		error: function(result) {
			$.jGrowl(get_error_message(result['status'], '読み込みに失敗しました。'));
		}
	});

	if (!selfDomElement) $('#list_loading_image').remove();
}

function delete_item(uri)
{
	var id = (arguments.length > 1) ? arguments[1] : 0;
	var target_attribute_prefix = (arguments.length > 2) ? arguments[2] : '';
	var target_attribute_id = (arguments.length > 3) ? arguments[3] : '';
	var item_term = (arguments.length > 4) ? arguments[4] : '';
	var confirm_msg = (arguments.length > 5 && arguments[5].length > 0) ? arguments[5] : '削除します。よろしいですか?';

	apprise(confirm_msg, {'confirm':true}, function(r) {
		if (r == true) delete_item_execute_ajax(uri, id, target_attribute_prefix, target_attribute_id, true, item_term);
	});
}

function delete_item_execute(uri)
{
	var url = get_url(uri) + '?' + set_token();
	location.href = url;
}

function delete_item_execute_ajax(post_uri, id, target_attribute_prefix)
{
	var target_attribute_id = (arguments.length > 3) ? arguments[3] : '';
	var is_display_message_success = (arguments.length > 4) ? arguments[4] : true;
	var item_term = (arguments.length > 5) ? arguments[5] : '';

	var baseUrl = get_baseUrl();

	var token_key = get_token_key();
	var post_data = {};
	if (id) post_data['id'] = id;
	post_data['_method'] = 'DELETE';
	post_data[token_key] = get_token();

	var msg_prefix = '';
	if (item_term.length > 0) msg_prefix = item_term + 'を';

	$.ajax({
		url : baseUrl + post_uri,
		dataType : "text",
		data : post_data,
		type : 'POST',
		success: function(data){
			var delete_target_attribute = target_attribute_id ? target_attribute_id : target_attribute_prefix + '_' + id;
			$(delete_target_attribute).fadeOut();
			if (is_display_message_success) $.jGrowl(msg_prefix + '削除しました。');
		},
		error: function(data){
			$.jGrowl(get_error_message(data['status'], msg_prefix + '削除できませんでした。'));
		}
	});
}

function reset_textarea()
{
	var textarea_attribute = (arguments.length > 0) ? arguments[0] : 'textarea';
	var textarea_height    = (arguments.length > 1) ? arguments[1] : '33px';

	$(textarea_attribute).val('');
	$(textarea_attribute).css('height', textarea_height);
}

function create_comment(parent_id, post_uri, get_uri, before_element_id_name)
{
	var selfDomElement       = (arguments.length > 4)  ? arguments[4]  : false;
	var public_flag          = (arguments.length > 5)  ? String(arguments[5]) : '';
	var textarea_attribute   = (arguments.length > 6)  ? arguments[6]  : '#textarea_comment';
	var list_block_id        = (arguments.length > 7)  ? arguments[7]  : '#comment_list';
	var post_data_additional = (arguments.length > 8)  ? arguments[8]  : {};
	var get_data_additional  = (arguments.length > 9)  ? arguments[9]  : {};
	var is_check_input_body  = (arguments.length > 10) ? arguments[10] : true;
	var textarea_height      = (arguments.length > 11) ? arguments[11] : '33px';
	var is_insert_before     = (arguments.length > 12) ? arguments[12] : false;
	var article_name         = (arguments.length > 13) ? arguments[13] : 'コメント';
	var count_attr_prefix    = (arguments.length > 14) ? arguments[14] : '#comment_count_';

	var body = $(textarea_attribute).val().trim();
	if (is_check_input_body && body.length <= 0) return;

	var selfDomElement_html = (selfDomElement) ? $(selfDomElement).html() : '';
	var post_data = post_data_additional;
	post_data['body'] = body;
	var count_attribute = '';
	if (parent_id) {
		post_data['id'] = parent_id;
		count_attribute = count_attr_prefix + parent_id;
	}
	if (public_flag.length > 0) post_data['public_flag'] = public_flag;
	post_data = set_token(post_data);
	var ret = false;
	$.ajax({
		url : get_baseUrl() + post_uri,
		type : 'POST',
		dataType : 'text',
		data : post_data,
		timeout: 10000,
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			if (selfDomElement) {
				$(selfDomElement).attr('disabled', true);
				$(selfDomElement).html(get_loading_image_tag());
			}
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			if (selfDomElement) {
				$(selfDomElement).attr('disabled', false);
				$(selfDomElement).html(selfDomElement_html);
			}
		},
		success: function(result){
			$.jGrowl(article_name + 'を投稿しました。');
			show_list(get_uri, list_block_id, 'all', before_element_id_name, is_insert_before, false, get_data_additional);
			if (count_attribute && $(count_attribute) != null) {
				var count = parseInt($(count_attribute).html()) + 1;
				$(count_attribute).html(count);
			}
			$(textarea_attribute).val('');
			$(textarea_attribute).css('height', textarea_height);

			var ret = true;
		},
		error: function(result){
			$.jGrowl(get_error_message(result['status'], article_name + 'の投稿に失敗しました。'));
		}
	});

	return ret;
}

function set_datetimepicker(attribute)
{
	var is_accept_futer = (arguments.length > 1) ? arguments[1] : false;
	$(attribute).datetimepicker({
		dateFormat: 'yy-mm-dd',
		hourMax: 23,
		changeYear: true,
		changeMonth: true,
		prevText: '&#x3c;前',
		nextText: '次&#x3e;',
		timeFormat: 'HH:mm',
		hourGrid: 6,
		minuteGrid: 15,
		addSliderAccess: true,
		sliderAccessArgs: { touchonly: false },
		maxDateTime: is_accept_futer ? null : new Date()
	});
}

function load_item(container_attribute, item_attribute)
{
	var finished_msg = (arguments.length > 2) ? arguments[2] : '';
	var loading_image_url = (arguments.length > 3) ? arguments[3] : get_url('assets/img/site/loading_l.gif');

	var $container = $(container_attribute);
	$container.infinitescroll({
		navSelector  : '#page-nav',   // ページのナビゲーションを選択
		nextSelector : '#page-nav a', // 次ページへのリンク
		itemSelector : item_attribute,    // 持ってくる要素のclass
		loadingImg   : loading_image_url,
	});
}

function load_popover(link_attribute, content_attribute, content_url) {
	var input_attrs = (arguments.length > 3) ? arguments[3] : '';

	$(link_attribute).popover({html: true})
	$(link_attribute).click(function(){
		$(content_attribute).load(content_url);
		//if (input_attrs.length > 0) $(input_attrs).focus();
		$(window).resize(function(e) {
			e.preventDefault()
			$(link_attribute).each(function (){
				if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('a[data-toggle=popover]').has(e.target).length === 0 && check_is_input(input_attrs) === false) {
					$(this).popover('hide');
					return;
				}
			});
		});
		return false;
	})
}

function update_public_flag(selfDomElement) {
	var public_flag          = $(selfDomElement).data('public_flag');
	var public_flag_original = $(selfDomElement).data('public_flag_original') ? $(selfDomElement).data('public_flag_original') : null;

	if (public_flag_original != null && is_expanded_public_range(public_flag_original, public_flag)) {
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
		url : get_baseUrl() + uri,
		type : 'POST',
		dataType : 'text',
		data : post_data,
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
	html += '<ul class="dropdown-menu pull-right">' + "\n";
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
	var input_val = $(input_atter).val().trim();

	if (input_val.length == 0) {
		$(input_atter).popover('show');
		isPopover = true;
		return false;
	}
	return true;
}

function check_is_input(input_attrs) {
	var is_input = false;
	for (i = 0; i < input_attrs.length; i++) {
		var val = $(input_attrs[i]).val();
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
				var $newElems = $( newElements ).css({ opacity: 0 });
				$newElems.imagesLoaded(function(){
					$newElems.animate({ opacity: 1 });
					$container.masonry( 'appended', $newElems, true );
				});
			}
		);
	}
}

function load_default_timeline()
{
	var mytimeline = (arguments.length > 0) ? arguments[0] : false;
	var member_id = (arguments.length > 1) ? arguments[1] : 0;

	load_timeline(mytimeline, member_id);
}

function load_more_timeline(selfDomElement)
{
	var mytimeline = $(selfDomElement).data('mytimeline') ? parseInt($(selfDomElement).data('mytimeline')) : 0;
	var member_id  = $(selfDomElement).data('member_id') ?  parseInt($(selfDomElement).data('member_id')) : 0;

	load_timeline(mytimeline, member_id, selfDomElement);
}

function load_timeline()
{
	var mytimeline = (arguments.length > 0) ? arguments[0] : false;
	var member_id  = (arguments.length > 1) ? arguments[1] : 0;
	var clickDomElement = (arguments.length > 2) ? arguments[2] : '';
	var limit = (arguments.length > 3) ? arguments[3] : 0;

	if (limit == 0) limit = get_config('timeline_list_limit');
	var get_uri = 'timeline/api/list.html';
	if (mytimeline) get_uri += '?mytimeline=1';
	if (member_id > 0) {
		var delimitter = mytimeline ? '&' : '?';
		get_uri += delimitter + 'member_id=' + member_id;
	}
	show_list(get_uri, '#article_list', limit, $('.timelineBox').last().attr('id'), false, clickDomElement);

	return false;
}

function send_article(btnObj, post_data, post_uri, parent_box_attr) {
	var add_before  = (arguments.length > 4 && arguments[4]) ? arguments[4] : false;
	var input_attr  = (arguments.length > 5 && arguments[5]) ? arguments[5] : '';
	var msg_success = (arguments.length > 6 && arguments[6]) ? arguments[6] : '投稿に成功しました。';
	var msg_error   = (arguments.length > 7 && arguments[7]) ? arguments[7] : '投稿に失敗しました。';

	post_data = set_token(post_data);
	var btn_html = $(btnObj).html();
	$.ajax({
		url : get_url(post_uri),
		type : 'POST',
		dataType : 'text',
		data : post_data,
		timeout: 10000,
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
			if (input_attr.length > 0) $(input_attr).val('');
			$.jGrowl('profile 選択肢を作成しました。');
		},
		error: function(result){
			$.jGrowl(get_error_message(result['status'], 'profile 選択肢の作成に失敗しました。'));
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

function update_follow_status(selfDomElement) {
	var id = $(selfDomElement).data('id');
	var selfDomElement_html = $(selfDomElement).html();

	var post_data = {'id': id};
	post_url = get_url('member/relation/api/update/follow.json');
	post_data = set_token(post_data);

	var ret = false;
	$.ajax({
		url : post_url,
		type : 'POST',
		dataType : 'text',
		data : post_data,
		timeout: 10000,
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
			var message
					resData = $.parseJSON(result);
			if (resData.status) {
				$(selfDomElement).addClass('btn-primary');
				selfDomElement_html = '<span class="glyphicon glyphicon-ok"></span> ' + get_term('follow') + '中';
				msg = get_term('follow') + 'しました。';
			} else {
				$(selfDomElement).removeClass('btn-primary');
				selfDomElement_html = get_term('follow') + 'する';
				msg = get_term('follow') + 'を解除しました。';
			}
			$.jGrowl(msg);
			$(selfDomElement).html(selfDomElement_html);
		},
		error: function(result){
			$(selfDomElement).html(selfDomElement_html);
			$.jGrowl(get_error_message(result['status'], get_term('follow') + 'に失敗しました。'));
		}
	});
}

function execute_post(uri){
	var post_data = (arguments.length > 1) ? arguments[1] : {};

	var post_url = get_url(uri);
	post_data = set_token(post_data);

	$('<form>', {action: post_url, method: 'post', id: 'tmp_form'}).appendTo(document.body);
	var tmp_form = $('#tmp_form');
	$.each(post_data, function(key, val){
		tmp_form.append($('<input>', {type: 'hidden', name: key, value: val}));
	});
	tmp_form.submit();
}

function post_submit(selfDomElement) {
	var post_data = (arguments.length > 1) ? arguments[1] : {};
	var uri = $(selfDomElement).data('uri') ? $(selfDomElement).data('uri') : '';
	var confirm_msg = $(selfDomElement).data('msg') ? $(selfDomElement).data('msg') : '';
	var destination = $(selfDomElement).data('destination') ? $(selfDomElement).data('destination') : '';

	if (destination.length > 0) post_data['destination'] = destination;

	if (confirm_msg.length > 0) {
		apprise(confirm_msg, {'confirm':true}, function(r) {
			if (r == true) execute_post(uri, post_data);
		});
		return;
	}

	execute_post(uri, post_data);
}

function execute_simple_delete(selfDomElement) {
	var post_id  = parseInt($(selfDomElement).data('id'));
	var post_uri  = $(selfDomElement).data('uri');
	var parent_id = $(selfDomElement).data('parent');
	var msg = $(selfDomElement).data('msg') ? $(selfDomElement).data('msg') : '';
	if (!post_id && !post_uri) return false;

	var parent_attr = parent_id ? '#' + parent_id : '#' + post_id;

	delete_item(post_uri, post_id, '', parent_attr, '', msg);
}

function execute_simple_post(selfDomElement) {
	var post_data = (arguments.length > 1) ? arguments[1] : {};
	var id = $(selfDomElement).data('id') ? parseInt($(selfDomElement).data('id')) : 0;
	if (id > 0) post_data['id'] = id;

	var post_uri   = $(selfDomElement).data('uri');
	if (!post_uri) return false;

	var parent_box = $(selfDomElement).data('parent_box') ? $(selfDomElement).data('parent_box') : 'jqui-sortable';
	var input_name = $(selfDomElement).data('input_name') ? $(selfDomElement).data('input_name') : 'name';

	var input_attr = '#input_' + input_name;
	var value = $(input_attr).val().trim();
	if (!value.length) return false;

	post_data[input_name] = value;
	var msg_success = '作成しました。';
	var msg_error = '作成に失敗しました。';
	send_article(selfDomElement, post_data, post_uri, '#' + parent_box, false, input_attr, msg_success, msg_error);
}

