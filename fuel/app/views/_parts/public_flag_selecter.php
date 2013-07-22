<?php
switch ($public_flag)
{
	case PRJ_PUBLIC_FLAG_ALL:
		$type = ' btn-info';
		$icon = '<i class="icon-globe icon-white"></i> ';
		break;
	case PRJ_PUBLIC_FLAG_MEMBER:
		$type = ' btn-success';
		$icon = '';
		break;
	default :
		$type = ' btn-danger';
		$icon = '<i class="icon-lock icon-white"></i> ';
		break;
}
$name = \Config::get('term.public_flag.options.'.$public_flag);
$model_uri = str_replace('_', '/', $model);
?>
<?php if (isset($is_mycontents) && $is_mycontents): ?>
<div class="btn-group">
	<button class="btn dropdown-toggle btn-mini<?php echo $type; ?>" id="public_flag_<?php echo $model; ?>_<?php echo $id; ?>" data-toggle="dropdown">
		<?php echo $icon.$name; ?><span class="caret"></span>
	</button>
	<ul class="dropdown-menu pull-right">
<?php $public_flags = \Site_Util::get_public_flags(); ?>
<?php $term_public_flags = \Config::get('term.public_flag.options'); ?>
<?php foreach ($public_flags as $public_flag_value): ?>
<?php if ($public_flag == $public_flag_value): ?>
		<li><span class="disabled"><?php echo $term_public_flags[$public_flag_value]; ?></span></li>
<?php else: ?>
		<li><a href="#" class="update_public_flag" data-id="<?php echo $id; ?>" data-public_flag="<?php echo $public_flag_value; ?>" data-model="<?php echo $model; ?>" data-model_uri="<?php echo $model_uri; ?>"><?php echo $term_public_flags[$public_flag_value]; ?></a></li>
<?php endif; ?>
<?php endforeach; ?>
	</ul>
</div>
<?php else: ?>
<span class="btn btn-mini<?php echo $type; ?>"><?php echo $icon.$name; ?></span>
<?php endif; ?>
