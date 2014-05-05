<?php if (!empty($descriptions) || !empty($exists_required_fields)): ?>
<div class="form_description">
<ul>
<?php if (!empty($descriptions)): ?>
<?php foreach ($descriptions as $description): ?>
	<li><?php echo $description; ?></li>
<?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($exists_required_fields)): ?>
	<li><span class="required">*</span>の項目は入力必須</li>
<?php endif; ?>
</ul>
</div>
<?php endif; ?>
