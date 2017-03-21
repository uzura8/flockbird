<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (empty($list)): ?>
<?php 	if (!IS_API): ?>
<?php echo __('message_not_registered_for', array('label' => t('news.view'))); ?>
<?php 	endif; ?>
<?php else: ?>
<?php
$is_image_enabled = conf('image.isEnabled', 'news');
$is_tags_enabled = conf('tags.isEnabled', 'news');
$title_trim_width = conf('viewParams.site.list.trim_width.title', 'news');
$body_trim_width = conf('viewParams.site.list.trim_width.body', 'news');
$body_format = \News\Site_Util::convert_format_key2value('raw');
$col_class = 'xs';
?>
<div id="article_list">
<?php foreach ($list as $id => $news): ?>
<?php
$image_col_size = 0;
if ($is_image_enabled && $image = \News\Model_NewsImage::get_one4news_id($id))
{
	$image_col_size = 2;
}
$tags   = $is_tags_enabled ? \News\Model_NewsTag::get_names4news_id($id) : array();
?>
	<div class="article">
		<div class="row">
			<div class="col-<?php echo $col_class; ?>-<?php echo 12 - $image_col_size; ?>">
				<h4 class="media-heading">
					<?php echo Html::anchor('news/detail/'.$news->slug, strim($news->title, $title_trim_width)); ?>
				</h4>
				<?php echo convert_body_by_format($html_bodys[$id], $body_format, $body_trim_width, 'news/detail/'.$news->slug); ?>
			</div>
<?php if ($image_col_size): ?>
			<div class="col-<?php echo $col_class; ?>-<?php echo $image_col_size; ?> article-left">
				<div class="imgBox">
					<?php echo img($image->file_name, 'thumbnail', 'news/detail/'.$news->slug, false, '', false, true, array('class' => 'thumbnail')); ?>
				</div>
			</div>
<?php endif; ?>
		</div>
<?php echo render('news::_parts/news_subinfo', array('news' => $news, 'tags' => $tags, 'is_simple_view' => true)); ?>
	</div>

<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($see_more_link)): ?>
<?php
	$anchor_text_default = icon_label('site.see_more', 'both', false, null, 'fa fa-');
	$href = Uri::create_url($see_more_link['uri']);
	$anchor_text = !empty($see_more_link['text']) ? $see_more_link['text'] : $anchor_text_default;
	$load_after_link_attr = array('class' => 'listMoreBox');
	echo Html::anchor($href, $anchor_text, $load_after_link_attr);
?>
<?php elseif (!empty($next_page)): ?>
<nav id="page-nav">
<?php
$uri = sprintf('news/api4site/list.html?page=%d', $next_page);
if (!empty($category_name)) $uri .= '&category='.$category_name;
if (!empty($tag_string)) $uri .= '&tag='.$tag_string;
?>
<a href="<?php echo Uri::base_path($uri); ?>"></a>
</nav>
<?php endif; ?>

<?php if (IS_API): ?></body></html><?php endif; ?>
