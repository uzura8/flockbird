$(function(){
	document.getElementById('gallery_link').onclick = function (event) {
		event = event || window.event;
		var target = event.target || event.srcElement,
			link = target.src ? target.parentNode : target,
			options = {
				index: link,
				event: event,
				onslideend: function (index, slide) {
					// Callback function executed after the slide change transition.
					//pos = gallery.getIndex();
				},
			};
		var gallery = blueimp.Gallery(links, options);
	};
});
