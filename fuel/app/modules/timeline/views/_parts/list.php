<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!IS_API): ?><div id="article_list"><?php endif; ?>
<?php if ($list): ?>
<?php foreach ($list as $id => $timeline_cache): ?>
<?php echo \Timeline\Site_Util::get_article_view($timeline_cache->id, $timeline_cache->timeline_id, $timeline_cache->member_id, \Auth::check() ? $u->id : 0); ?>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($is_next): ?>
<?php
$attr = array(
	'class' => 'listMoreBox js-ajax-Load_timeline',
	'data-last_id' => $timeline_cache->id,
);
$data_array = array('desc' => 1);
if (!empty($member)) $data_array['member_id'] = $member->id;
if (!empty($mytimeline)) $data_array['mytimeline'] = 1;
if ($data_array) $attr['data-get_data'] = $data_array;
?>
<a href="#" <?php echo Util_Array::conv_array2attr_string($attr); ?>><?php echo icon_label('site.see_more', 'both', false, null, 'fa fa-'); ?></a>
<?php endif; ?>

<?php if (!IS_API): ?></div><!-- article_list --><?php endif; ?>
<?php if (IS_API): ?></body></html><?php endif; ?>
