<?php
if (empty($action)) $action = 'publish';

$data_attr = conv_data_atter(array(
	'uri' => $uri,
	'msg' => sprintf('%sします。よろしいですか？', ($action == 'publish') ? term('form.publish') : term('form.unpublish').'に'),
));
$default_attrs = array('id' => 'link_publish') + $data_attr;
if (!isset($attrs)) $attrs = array();
$attrs = Util_Array::conv_arrays2str(array_merge_recursive($default_attrs, $attrs));
?>
<?php echo Html::anchor('#', icon_label(($action == 'publish') ? 'globe' : 'lock', 'form.do_'.$action, false, false), $attrs); ?>
