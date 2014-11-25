<hr>
<footer>
<?php if (!IS_ADMIN && $navs = Config::get('navigation.site.global_footer')): ?>
	<ul class="list-inline">
<?php foreach ($navs as $label => $uri): ?>
		<li><?php echo anchor($uri, $label); ?></li>
<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php if (PRJ_COPYRIGHT): ?>
	<p><?php echo PRJ_COPYRIGHT; ?></p>
<?php else: ?>
	<p>Copyright : <?php echo date('Y'); ?> <?php echo PRJ_SITE_NAME; ?></p>
<?php endif; ?>
</footer>
