<ul class="nav nav-tabs">
	<li<?php if (isset($active_action) && $active_action == 'email'): ?> class="active"<?php endif; ?>><?php echo Html::anchor('member/setting/email', term(array('site.email', 'form.update'))); ?></li>
	<li<?php if (isset($active_action) && $active_action == 'password'): ?> class="active"<?php endif; ?>><?php echo Html::anchor('member/setting/password', term(array('site.password', 'form.update'))); ?></li>
	<li<?php if (isset($active_action) && $active_action == 'leave'): ?> class="active"<?php endif; ?>><?php echo Html::anchor('member/leave', term('site.left')); ?></li>
</ul>
