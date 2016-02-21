<?php
if (!isset($input_attr)) $input_attr = array();
$input_attr_default = array(
	'type' => 'text',
	'id' => 'input_search',
	'placeholder' => 'Search',
);
$input_attr = array_merge($input_attr_default, $input_attr);

$btn_attr_default = array(
	'class' => 'js-ajax-loadList',
	//'data-input' => '#input_search',
	'data-position' => 'replace',
	'data-type' => 'button',
	'data-inputs' => json_encode(array('q')),
);
$btn_attr = array_merge($btn_attr_default, $btn_attr);
?>
<div class="formBox mb15" id="form_search">
	<div class="input-group">
		<?php echo Form::input('q', $input_value, $input_attr); ?>
		<span class="input-group-btn">
			<?php echo btn('form.search', null, null, true, null, null, $btn_attr, null, 'button', null, false); ?>
		</span>
	</div><!-- /input-group -->
</div>

