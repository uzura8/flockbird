<?php
if (!isset($list_id)) $list_id = 'article_list';
if (!isset($is_simple_list)) $is_simple_list = false;
?>
<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php if (!empty($no_data_message)): ?>
<?php echo $no_data_message; ?>
<?php else: ?>
<?php echo term('member.view'); ?>の<?php echo term('site.registration'); ?>がありません。
<?php endif; ?>
<?php else: ?>
<div id="<?php echo $list_id; ?>">
<?php foreach ($list as $id => $obj): ?>
	<div class="article" id="article_<?php echo $id; ?>">
<?php echo render('_parts/member_profile', array(
	'member' => !empty($related_member_table_name) ? $obj->{$related_member_table_name} : $obj,
	'next_id' => $next_id,
	'access_from' => Auth::check() ? 'member' : 'guest',
	'is_list' => true,
	'page_type' => 'list',
	'display_type' => 'summery',
	'is_simple_list' => $is_simple_list,
)); ?>
	</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($next_id): ?>
<?php
$attr = array(
	'class' => 'listMoreBox js-ajax-loadList',
	'data-uri' => $get_uri,
	'data-list' => '#article_list',
	'data-max_id' => $next_id,
	'data-latest' => 1,
	'data-desc' => 1,
);
if (!empty($since_id)) $attr['data-since_id'] = $since_id;
echo Html::anchor('#', icon_label('site.see_more', 'both', false, null, 'fa fa-'), $attr);
?>
<?php endif; ?>

<?php if (IS_API): ?></body></html><?php endif; ?>
