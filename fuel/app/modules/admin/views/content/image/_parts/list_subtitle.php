<?php if (check_acl($uri = 'admin/content/image/upload')): ?>
<?php echo btn(sprintf('%sを%s', term('site.image'), term('form.add')), $uri, 'edit btn-warning', true, null, null, null, 'upload', null, null, false); ?>
<?php endif; ?>
