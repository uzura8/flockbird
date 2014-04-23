<?php
$data_attr = conv_data_atter(array(
	'uri' => $uri,
	'msg' => '削除します。よろしいですか？',
));
$default_attrs = array('id' => 'link_delete') + $data_attr;
if (!isset($attrs)) $attrs = array();
$attrs = Util_Array::conv_arrays2str(array_merge_recursive($default_attrs, $attrs));
?>
<?php echo Html::anchor('#', icon_label('trash', 'form.do_delete'), $attrs); ?>
