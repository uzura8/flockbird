<?php
if (empty($size)) $size = 'sm';
if (empty($relation_type)) $relation_type = 'follow';

$default_attrs = array(
	'class' => array('btn', 'btn-default', 'js-update_toggle', 'btn-'.$size),
	'id' => sprintf('btn_%s_%s', $relation_type, $member_id_to),
	'data-uri' => sprintf('member/relation/api/update/%d/%s.json', $member_id_to, $relation_type),
);
if (!isset($attrs)) $attrs = array();
$attrs = array_merge_recursive($default_attrs, $attrs);
if (empty($name)) $name = $default_attrs['id'];

$status = Model_MemberRelation::check_relation($relation_type, $member_id_from, $member_id_to);
$data = Site_Member_Relation::get_updated_status_info($relation_type, $status);
$label = $data['label'];
$attrs = array_merge_recursive($attrs, $data['attr']);
$attrs = Util_Array::conv_arrays2str($attrs);
?>
<?php echo Form::button($name, $label, $attrs); ?>

