<hr>
<footer>
<?php if (!IS_ADMIN && $navs = Config::get('navigation.site.global_footer')): ?>
	<ul class="list-inline">
<?php foreach ($navs as $label => $uri): ?>
		<li<?php if (check_current_uri($uri)): ?> class="active"<?php endif; ?>><?php echo anchor($uri, $label); ?></li>
<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php if (FBD_COPYRIGHT): ?>
	<p><?php echo FBD_COPYRIGHT; ?></p>
<?php else: ?>
	<p>Copyright : <?php echo date('Y'); ?> <?php echo FBD_SITE_NAME; ?></p>
<?php endif; ?>
</footer>
