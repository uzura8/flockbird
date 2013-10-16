$('textarea.autogrow').autogrow();

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

	if (!selfDomElement) {
		$(list_block_id).append('<div class="loading_image" id="list_loading_image">' + get_loading_image_tag() + '</div>');
	}

	var baseUrl = get_baseUrl();
	var get_url = baseUrl + uri;
	var get_data = {};
	get_data['nochache']  = (new Date()).getTime();
	get_data['limit'] = record_limit;

	var next_element_id_num  = (next_element_id_name.length > 0) ? get_id_num(next_element_id_name) : 0;
	if (next_element_id_num) {
		var key = (is_insert_before)? 'after_id' : 'before_id';
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
	var item_term = (arguments.length > 3) ? arguments[3] : '';

	apprise('削除しますか?', {'confirm':true}, function(r) {
		if (id > 0 && target_attribute_prefix.length > 0) {
			if (r == true) delete_item_execute_ajax(uri, id, target_attribute_prefix, true, item_term);
		} else {
			if (r == true) delete_item_execute(uri);
		}
	});
}

function delete_item_execute(uri)
{
	var url = get_url(uri) + '?' + set_token();
	location.href = url;
}

function delete_item_execute_ajax(post_uri, id, target_attribute_prefix)
{
	var is_display_message_success = (arguments.length > 3) ? arguments[3] : true;
	var item_term = (arguments.length > 4) ? arguments[4] : '';

	var baseUrl = get_baseUrl();

	var token_key = get_token_key();
	var post_data = {};
	post_data['id'] = id;
	post_data[token_key] = get_token();

	var msg_prefix = '';
	if (item_term.length > 0) msg_prefix = item_term + 'を';

	$.ajax({
		url : baseUrl + post_uri,
		dataType : "text",
		data : post_data,
		type : 'POST',
		success: function(data){
			$(target_attribute_prefix + '_' + id).fadeOut();
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
	var selfDomElement     = (arguments.length > 4) ? arguments[4] : false;
	var public_flag        = (arguments.length > 5) ? String(arguments[5]) : '';
	var textarea_attribute = (arguments.length > 6) ? arguments[6] : '#textarea_comment';
	var list_block_id      = (arguments.length > 7) ? arguments[7] : '#comment_list';
	var textarea_height    = (arguments.length > 8) ? arguments[8] : '33px';
	var is_insert_before   = (arguments.length > 9) ? arguments[9] : false;
	var article_name       = (arguments.length > 10) ? arguments[10] : 'コメント';

	var body = $(textarea_attribute).val().trim();
	if (body.length <= 0) return;

	var selfDomElement_html = (selfDomElement) ? $(selfDomElement).html() : '';
	var data = {'body':body};
	if (parent_id) data['id'] = parent_id;
	if (public_flag.length > 0) data['public_flag'] = public_flag;
	data = set_token(data);
	$.ajax({
		url : get_baseUrl() + post_uri,
		type : 'POST',
		dataType : 'text',
		data : data,
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
			show_list(get_uri, list_block_id, 0, before_element_id_name, is_insert_before);
			$(textarea_attribute).val('');
			$(textarea_attribute).css('height', textarea_height);
		},
		error: function(data){
			$.jGrowl(get_error_message(data['status'], article_name + 'の投稿に失敗しました。'));
		}
	});
}

function set_datetimepicker(attribute)
{
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
		maxDateTime: new Date()
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
		//if (input_attribute.length > 0) $(input_attribute).focus();
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
	var model_uri   = $(selfDomElement).data('model_uri');
	var public_flag = $(selfDomElement).data('public_flag');
	var icon_only_flag = $(selfDomElement).data('icon_only') ? $(selfDomElement).data('icon_only') : 0;
	var have_children_public_flag = $(selfDomElement).data('have_children_public_flag') ? $(selfDomElement).data('have_children_public_flag') : 0;
	var child_model    = $(selfDomElement).data('child_model') ? $(selfDomElement).data('child_model') : '';
	var is_refresh     = $(selfDomElement).data('is_refresh') ? $(selfDomElement).data('is_refresh') : 0;

	var parentElement = $(selfDomElement).parent('li');
	var text = $(selfDomElement).html();
	var buttonDomElement = $('#public_flag_' + model + '_' + id).parent('.btn-group');

	var post_data = {
		'id'             : id,
		'public_flag'    : public_flag,
		'model'          : model,
		'icon_only_flag' : icon_only_flag,
		'have_children_public_flag'      : have_children_public_flag,
		'is_update_children_public_flag' : is_update_children_public_flag,
	};
	post_data = set_token(post_data);
	$.ajax({
		url : get_baseUrl() + model_uri +'/api/update_public_flag.html',
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
			$(buttonDomElement).html(result);
			var msg = get_term('public_flag') + 'を変更しました。';
			if (is_refresh) {
				var delimitter = (url('?').length > 0) ? '&' : '?';
				location.href=url() + delimitter + 'msg=' + msg;
			} else {
				$.jGrowl(msg);
			}
		},
		error: function(result){
			$(parentElement).html(selfDomElement);

			var resData = $.parseJSON(result.responseText);
			var message = resData.error.message ? resData.error.message : get_term('public_flag') + 'の変更に失敗しました。';
			$.jGrowl(get_error_message(result['status'], message));
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
