<?php if (!IS_ADMIN || check_acl($uri)): ?>
<?php echo btn(!empty($is_edit) ? 'form.edit' : 'form.create', $uri, 'edit'); ?>
<?php endif; ?>

