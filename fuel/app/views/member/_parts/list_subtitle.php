<?php if (Auth::check()): ?>
<?php echo btn(null, 'member/search?form_open=1', 'edit', true, null, null, null, null, null, null, false, false, icon('form.search').' '.term('site.detail', 'form.search')); ?>
<?php endif; ?>

