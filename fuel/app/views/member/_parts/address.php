<?php
if (empty($label_col_size)) $label_col_size = 2;
if (empty($display_type)) $display_type = 'summary';
if (! empty($full_name)) $full_name = $member_address->get_full_name(is_lang_ja());
if (! empty($address))   $address = $member_address->get_address(is_lang_ja());
?>
<?php if ($display_type == 'summary'): ?>
<ul class="list-unstyled">
	<li><?php echo $full_name; ?></li>
	<li><?php echo $address; ?></li>
<?php if ($member_address->company_name): ?>
	<li><?php echo $member_address->company_name; ?></li>
<?php endif; ?>
<?php if ($member_address->phone01): ?>
	<li><?php echo $member_address->phone01; ?></li>
<?php endif; ?>
</ul>
<?php else: ?>
<div class="form-horizontal">
	<?php echo form_text(
		$full_name,
		t('member.address.full_name'),
		$label_col_size
	); ?>
	<?php echo form_text(
		$address,
		t('member.address.view'),
		$label_col_size
	); ?>
<?php 	if ($member_address->company_name): ?>
	<?php echo form_text(
		$member_address->company_name,
		t('member.address.company_name'),
		$label_col_size
	); ?>
<?php 	endif; ?>
	<?php echo form_text(
		$member_address->phone01,
		t('member.address.phone01'),
		$label_col_size
	); ?>
</div>
<?php endif; ?>

