<h3><?php echo sprintf('%sさんの%s', $member->name, Config::get('term.timeline')); ?></h3>
<div id="article_list">
<?php echo render('_parts/timeline/list', array('list' => $list, 'is_next' => $is_next, 'member' => $member)); ?>
</div>
