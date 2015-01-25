<div><?php echo $body; ?></div>

<?php if (!empty($read_more_uri)): ?>
<div class="bodyMore"><?php echo Html::anchor($options['read_more_uri'], term('site.see_more')); ?></div>
<?php endif; ?>

<?php if (!empty($site_summary_data)): ?>
<?php echo render('_parts/site_summary', $site_summary_data); ?>
<?php endif; ?>
