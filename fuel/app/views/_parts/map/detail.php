<?php
if (!isset($auther_member_id)) $auther_member_id = 0;
if (!isset($locations) || (!$locations[0] && !$locations[1])) $locations = array();
if (!isset($is_form_view)) $is_form_view = false;
$is_location_settable = ($is_form_view && !empty($locations[0]) && !empty($locations[1]));
?>
<?php if ($locations || is_editable($auther_member_id || $is_form_view)): ?>
<?php
$block_attrs = array('id' => 'map');
if ($is_location_settable) $block_attrs['data-set_location'] = 1;
if ($locations) $block_attrs['data-map_params'] = array('lat' => $locations[0], 'lng' => $locations[1]);
if ($markers) $block_attrs['data-markers'] = $markers;
if (!empty($marker_template)) $block_attrs['data-template'] = $marker_template;
if (!empty($marker_images)) $block_attrs['data-images'] = $marker_images;
?>
<?php if (!$is_form_view): ?>
<h3>Map</h3>
<?php endif; ?>
<div class="popin<?php if ($is_form_view): ?> popin-noshadow<?php endif; ?><?php if (!$locations): ?> hidden<?php endif; ?>">
	<div <?php echo Util_Array::conv_array2attr_string($block_attrs); ?>></div>
</div>
<?php
if (is_editable($auther_member_id) || $is_form_view)
{
	$btn_attr = array(
		'class' => 'js-display_map',
		'data-target' => '.popin',
		'data-load_current_position' => $locations ? 0 : 1,
	);
	if (!$is_location_settable)
	{
		echo btn('form.set_location', '#', 'btn_display_map', true, null, null, $btn_attr);
	}
	echo Form::hidden('latitude', $locations ? $locations[0] : '', array('id' => 'input_lat'));
	echo Form::hidden('longitude', $locations ? $locations[1] : '', array('id' => 'input_lng'));
}
if (is_editable($auther_member_id))
{
	$btn_attr = array(
		'class' => 'js-set_location hidden',
		'disabled' => 'disabled',
		'data-uri' => $save_uri,
		'data-lat_input' => '#input_lat',
		'data-lng_input' => '#input_lng',
	);
	echo btn('form.do_set_location', '#', 'btn_set_location', true, null, 'primary', $btn_attr);
}
?>
<?php endif; ?>

