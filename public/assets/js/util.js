jQuery.fn.exists = function(){return Boolean(this.length > 0);}

$(document).on('click', '.void', function(){
	return false;
});

function sleep(sleep_time)
{
	var start_time = new Date().getTime();
	var now = new Date().getTime();
	while (now < start_time + sleep_time) {
		now = new Date().getTime();
	}
	return;
}

function objectSort(object) {
	var isReverse = (arguments.length > 1) ? arguments[1] : false;

	var sorted = {};
	var array = [];
	for (key in object) {
		if (object.hasOwnProperty(key)) {
			array.push(key);
		}
	}
	if (isReverse) {
		array.reverse();
	} else {
		array.sort();
	}
	for (var i = 0; i < array.length; i++) {
		sorted[array[i]] = object[array[i]];
	}
	return sorted;
}

function empty(data) {
	if (data === null) return true;
	if (data === undefined) return true;
	if (data === false) return true;
	if (data === '') return true;
	if (data === 0) return true;
	if (data === '0') return true;
	return false;
}

function focusLast(inputSelector) {
	var body = $(inputSelector).val();
	$(inputSelector).val('');
	$(inputSelector).focus();
	$(inputSelector).val(body);
}

function insertTextAtCaret(target, str) {
	var obj = $(target);
	obj.focus();
	if(navigator.userAgent.match(/MSIE/)) {
		var r = document.selection.createRange();
		r.text = str;
		r.select();
	} else {
		var s = obj.val();
		var p = obj.get(0).selectionStart;
		var np = p + str.length;
		obj.val(s.substr(0, p) + str + s.substr(p));
		obj.get(0).setSelectionRange(np, np);
	}
	return true;
}

function insertHtmlAtCaret(html) {
	var targetSelector = (arguments.length > 1) ? arguments[1] : null;
	var sel, range;
	if (window.getSelection) {
		// IE9 and non-IE
		sel = window.getSelection();
		if (targetSelector && !checkCursorOnTarget(sel.anchorNode, targetSelector)) return false;

		if (sel.getRangeAt && sel.rangeCount) {
			range = sel.getRangeAt(0);
			range.deleteContents();

			// Range.createContextualFragment() would be useful here but is
			// only relatively recently standardized and is not supported in
			// some browsers (IE9, for one)
			var el = document.createElement("div");
			el.innerHTML = html;
			var frag = document.createDocumentFragment(), node, lastNode;
			while ( (node = el.firstChild) ) {
				lastNode = frag.appendChild(node);
			}
			range.insertNode(frag);

			// Preserve the selection
			if (lastNode) {
				range = range.cloneRange();
				range.setStartAfter(lastNode);
				range.collapse(true);
				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
	} else if (document.selection && document.selection.type != "Control") {
		// IE < 9
		document.selection.createRange().pasteHTML(html);
	} else {
		return false;
	}
	return true;
}

function checkCursorOnTarget(selectedNode, targetSelector) {
	if ($(selectedNode).closest(targetSelector).length) return true;

	targetSelectorPrefix = targetSelector.slice(0, 1);
	targetSelectorName = targetSelector.slice(1);
	if (targetSelectorPrefix == '.') {
		if ($(selectedNode).hasClass(targetSelectorName)) return true;
	} else if (targetSelectorPrefix == '#') {
		if ($(selectedNode).attr('id') == targetSelectorName) return true;
	}

	return false;
}

function arrayMerge(obj, objAdded) {
	$.extend(true, obj, objAdded);
	return obj;
}

function strimwidth(str, width) {
	if (!width || str.length <= width) return str;
	return str.substr(0, width) + '...';
}

var escapeHtml = function(val) {
	return $('<div />').text(val).html();
};

