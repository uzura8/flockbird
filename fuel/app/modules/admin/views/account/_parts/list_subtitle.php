<?php if (\Admin\Site_AdminUser::check_gruop($u->group, 100)): ?>
<?php echo btn('form.create', 'admin/account/create'); ?>
<?php endif; ?>
