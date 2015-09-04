$(function(){
	$(window).on('beforeunload', function() {
		if (checkInput()) return '投稿が完了していません。このまま移動しますか？';
	});
	$("button[type=submit]").click(function() {
		$(window).off('beforeunload');
	});
	$("#btn_delete").click(function() {
		$(window).off('beforeunload');
	});

	showEditor($('#form_format').val());
});

$('.submit_btn').on('click', function(){
	if ($('#form_format').val() == 1) return;
	$('.note-editable').html($('#form_body').val());
});

$(document).on('change', '#form_format', function(){
	showEditor($(this).val(), true);
});

function showEditor(selected_format) {
	var isFocus = (arguments.length > 1) ? Boolean(arguments[1]) : false;

	if (selected_format == 1) {
		showSummernoteForm();
	} else if (selected_format == 2) {
		showMarkdownForm();
		if (isFocus) focusLast('#form_body');
	} else {
		// TODO: display normal textarea
	}
}

function showMarkdownForm() {
	var body = $('.note-editable').html();
	if (!empty(body) && body.length) {
		$('#form_body').val(decodeForMarkdown(body));
	}
	$('.note-editor').hide();
	$('.md-header').show();
	if ($('div.md-preview').exists()) {
		$('.md-preview').show();
	} else {
		$('#form_body').show();
	}
	changeAttrForEitor(false);
}

function showSummernoteForm() {
	var body = $('#form_body').val();
	$('.note-editable').html(body);
	$('.md-header').hide();
	$('.md-preview').hide();
	$('#form_body').hide();
	$('.note-editor').show();
	changeAttrForEitor(true);
}

function decodeForMarkdown(value) {
	value = value.replace(/&gt;/g, '>');
	return value;
}

function changeAttrForEitor(isSummernote) {
	var bodySelector = isSummernote ? '.note-editable' : '#form_body';
	var insertAsElementValue = isSummernote ? 1 : 0;
	$('#insert_target').val(bodySelector);
	$('.js-insert_img').each(function() {
		var btn = document.getElementById($(this).attr('id'));
		btn.setAttribute('data-body', bodySelector);
	});
}

