$(function(){
	var map,
		mapSelector = '#map',
		mapParams  = getMapParams($(mapSelector).data('map_params'), mapSelector),
		mapMarkers = $(mapSelector).data('markers'),
		markerTemplateSelector = $(mapSelector).data('template') ? $(mapSelector).data('template') : null,
		markerTemplate,
		markerImages = $(mapSelector).data('images') ? $(mapSelector).data('images') : {},
		isSetLocation = Boolean($(mapSelector).data('set_location')),
		inputLat   = '#input_lat',
		inputLng   = '#input_lng';

	if (markerTemplateSelector) markerTemplate = Handlebars.compile($(markerTemplateSelector).html());
	if (mapMarkers) {
		map = new GMaps(mapParams);
		$.each(mapMarkers, function(i, markerParams) {
			if (markerTemplate && markerImages) markerParams.infoWindow = {content: markerTemplate(markerImages)};
			map.addMarker(markerParams);
		});
	}
	if (isSetLocation) setLocation(map, inputLat, inputLng, markerTemplate, markerImages);

	$(document).on('click', '.js-display_map', function(){
		var mapSelector = $(this).data('target') ? $(this).data('target') : '#map',
			isLoadCurrentPosition = Boolean($(this).data('load_current_position')),
			btnSubmit  = '.js-set_location',
			markerParams = {};

		$(this).addClass('hidden');
		$(mapSelector).removeClass('hidden');
		$('.btn_set_location').removeClass('hidden');

		if (isLoadCurrentPosition) {
			map = new GMaps(mapParams);
			GMaps.geolocate({
				success: function(position){
					map.setCenter(position.coords.latitude, position.coords.longitude);
				},
				error: function(error){
					alert('位置情報の取得に失敗しました。: '+error.message);
				},
				not_supported: function(){
					alert("Your browser does not support geolocation");
				},
				always: function(){
					//alert("Done!");
				}
			});
		}

		setLocation(map, inputLat, inputLng, markerTemplate, markerImages, btnSubmit);
		return false;
	});

	$(document).on('click', '.js-set_location', function(){
		var postUri  = $(this).data('uri')       ? $(this).data('uri') : '',
				inputLat = $(this).data('input_lat') ? $(this).data('input_lat') : '#input_lat',
				inputLng = $(this).data('input_lng') ? $(this).data('input_lng') : '#input_lng',
				lat = $(inputLat).val(),
				lng = $(inputLng).val();

		if (!lat || !lng) {
			showMessage('位置を指定してください。');
			$(btnSubmit).attr({disabled: 'disabled'});
			return false;
		}
		simpleAjaxPost(postUri, {latitude: lat, longitude: lng}, this);
		return false;
	});
});

function getMapParams(mapParams, mapSelector, mapParamsDefault) {
	if (empty(mapParams)) mapParams = {};
	if (empty(mapSelector)) mapSelector = '#map';
	if (empty(mapParamsDefault)) mapParamsDefault = get_config('mapParams');
	$.each(mapParamsDefault, function(key, value) {
		if (empty(mapParams[key])) mapParams[key] = value;
	});
	if (empty(mapParams.div)) mapParams.div = mapSelector;
	return mapParams;
}

function setLocation(map, inputLat, inputLng, markerTemplate, markerImages, btnSubmit) {
	GMaps.on('click', map.map, function(event) {
		var index = map.markers.length,
			lat = event.latLng.lat(),
			lng = event.latLng.lng();

		map.removeMarkers();
		if (!lat || !lng) {
			showMessage('位置情報の取得に失敗しました。');
			return false;
		}
		$(inputLat).val(lat);
		$(inputLng).val(lng);
		if (btnSubmit) $(btnSubmit).removeAttr('disabled');

		markerParams = {
			lat: lat,
			lng: lng
		};
		if (markerTemplate && markerImages) markerParams.infoWindow = {content: markerTemplate(markerImages)};
		if (markerImages.alt) markerParams.title = markerImages.alt;
		map.addMarker(markerParams);
	});
	GMaps.on('marker_added', map, function(marker) {
	});
}
