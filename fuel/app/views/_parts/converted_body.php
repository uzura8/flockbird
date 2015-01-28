<div><?php echo $body; ?></div>

<?php if (!empty($read_more_uri)): ?>
<div class="bodyMore"><?php echo Html::anchor($options['read_more_uri'], term('site.see_more')); ?></div>
<?php endif; ?>

<?php if ($display_summary_type == 'client' && !empty($site_summary_url)): ?>
<?php 	echo html_tag('div', array(
	'class' => 'site_summary_unrendered',
	'data-get_data' => json_encode(array('url' => $site_summary_url)),
), ''); ?>
<?php elseif ($display_summary_type == 'server' && !empty($site_summary_data)): ?>
<?php 	echo render('_parts/site_summary', $site_summary_data); ?>
<?php endif; ?>
