<?php
if (empty($size)) $size = 'sm';
$default_attrs = array(
	'class' => array('btn', 'btn_follow', 'btn-'.$size),
	'id' => 'btn_follow_'.$member_id_to,
	'data-id' => $member_id_to,
);
if (!isset($attrs)) $attrs = array();
$attrs = array_merge_recursive($default_attrs, $attrs);

if (empty($name)) $name = $default_attrs['id'];
if (Model_MemberRelation::check_relation('follow', $member_id_from, $member_id_to))
{
	$label = '<span class="glyphicon glyphicon-ok"></span> '.term('followed');
	$attrs['class'][] = 'btn-primary';
}
else
{
	$label = term('do_follow');
	$attrs['class'][] = 'btn-default';
}

$attrs = Util_Array::conv_arrays2str($attrs);
?>
<?php echo Form::button($name, $label, $attrs); ?>
