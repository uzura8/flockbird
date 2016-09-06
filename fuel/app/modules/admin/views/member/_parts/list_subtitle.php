<?php if (conf('member.inviteFromAdmin.isEnabled', 'admin')): ?>
<?php 	echo btn(null, 'admin/member/invite', 'edit', true, null, 'warning', null, null, null, null, false, false, sprintf('%s %s', icon('form.send'), term('member.view', 'form.invite'))); ?>
<?php endif; ?>

