<?php
if (!isset($auther_member_id)) $auther_member_id = 0;
if (!isset($locations)) $locations = array();
?>
<?php if ($locations || Auth::check()): ?>
<?php
$block_attrs = array('id' => 'map');
if ($locations) $block_attrs['data-map_params'] = array('lat' => $locations[0], 'lng' => $locations[1]);
if ($markers) $block_attrs['data-markers'] = $markers;
if ($marker_template) $block_attrs['data-template'] = $marker_template;
if ($marker_images) $block_attrs['data-images'] = $marker_images;
?>
<h3>Map</h3>
<div class="popin<?php if (!$locations): ?> hidden<?php endif; ?>">
	<div <?php echo Util_Array::conv_array2attr_string($block_attrs); ?>></div>
</div>
<?php
if (is_editable($auther_member_id))
{
	$btn_attr = array(
		'class' => 'js-display_map',
		'data-target' => '.popin',
		'data-load_current_position' => $locations ? 0 : 1,
	);
	echo btn('form.set_location', '#', 'btn_display_map', true, null, null, $btn_attr);

	$btn_attr = array(
		'class' => 'js-set_location hidden',
		'disabled' => 'disabled',
		'data-uri' => $save_uri,
		'data-lat_input' => '#input_lat',
		'data-lng_input' => '#input_lng',
	);
	echo btn('form.do_set_location', '#', 'btn_set_location', true, null, 'primary', $btn_attr);
	echo Form::hidden('lat', $locations ? $locations[0] : '', array('id' => 'input_lat'));
	echo Form::hidden('lng', $locations ? $locations[1] : '', array('id' => 'input_lng'));
}
?>
<?php endif; ?>

