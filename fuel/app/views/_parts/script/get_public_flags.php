function get_public_flags() {
	var public_flags = new Object();
<?php $public_flag_values = Site_Util::get_public_flags(); ?>
<?php foreach ($public_flag_values as $value): ?>
<?php list($name, $icon, $btn_color) = get_public_flag_label($value); ?>
	public_flags['<?php echo $value; ?>'] = new Object();
	public_flags['<?php echo $value; ?>']['name']      = '<?php echo $name; ?>';
	public_flags['<?php echo $value; ?>']['icon']      = '<?php echo $icon; ?>';
	public_flags['<?php echo $value; ?>']['btn_color'] = '<?php echo $btn_color; ?>';
<?php endforeach; ?>
	return public_flags;
}
