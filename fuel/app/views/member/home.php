<h3><?php echo sprintf('%sさんの%s', $member->name, Config::get('term.timeline')); ?></h3>

<?php echo render('timeline::_parts/list', array('list' => $list, 'is_next' => $is_next, 'member' => $member)); ?>

