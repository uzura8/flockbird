<?php
if (empty($type)) $type = 'default';
$default_attrs = array('class' => array('label', 'label-'.$type));
if (!isset($attrs)) $attrs = array();
$attr = Util_Array::conv_arrays2str(array_merge_recursive($default_attrs, $attrs));
?>
<?php echo html_tag('span', $attr, $name); ?>
