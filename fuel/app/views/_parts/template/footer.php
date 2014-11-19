<hr>
<footer>
	<ul class="list-inline">
<?php foreach (Config::get('navigation.site.global_footer') as $label => $uri): ?>
		<li><?php echo anchor($uri, $label); ?></li>
<?php endforeach; ?>
	</ul>
<?php if (PRJ_COPYRIGHT): ?>
	<p><?php echo PRJ_COPYRIGHT; ?></p>
<?php else: ?>
	<p>Copyright : <?php echo date('Y'); ?> <?php echo PRJ_SITE_NAME; ?></p>
<?php endif; ?>
</footer>
