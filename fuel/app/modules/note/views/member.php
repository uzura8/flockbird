<?php if ($is_mypage): ?>
<ul class="nav nav-pills">
	<li<?php if (!$is_draft): ?> class="disabled"<?php endif; ?>><?php echo Html::anchor(
		$is_draft ? 'note/member' : '#',
		'公開済み'.Config::get('term.note'),
		$is_draft ? array() : array('onclick' => 'return false;')
	); ?></li>
	<li<?php if ($is_draft): ?> class="disabled"<?php endif; ?>><?php echo Html::anchor(
		$is_draft ? '#' : 'note/member?is_draft=1',
		Config::get('term.draft'),
		$is_draft ? array('onclick' => 'return false;') : array()
	); ?></li>
</ul>
<?php endif; ?>
<?php echo render('_parts/list', array('list' => $list, 'page' => $page, 'is_next' => $is_next, 'member' => $member, 'is_draft' => $is_draft)); ?>
