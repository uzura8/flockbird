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
	<small><?php echo FBD_COPYRIGHT; ?></small>
<?php else: ?>
	<small>Copyright&copy; <?php echo date('Y'); ?> <?php echo Site_Util::get_copyright_name(); ?> All Rights Reserved.</small>
<?php endif; ?>
</footer>
