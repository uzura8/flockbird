<?php
$attr = array(
	'class' => 'js-ajax-delete',
	'id' => $attr_id,
	'data-id' => $id,
	'data-parent' => '#'.$parrent_attr_id,
);
if (!empty($delete_uri)) $attr['data-uri'] = $delete_uri;
if (!empty($counter_selector)) $attr['data-counter'] = $counter_selector;
echo btn('form.delete', '#', 'boxBtn', false, 'xs', 'default', $attr);
?>
