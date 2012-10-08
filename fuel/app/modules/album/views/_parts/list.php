<?php if ($list): ?>
<div id="article_list">
<?php foreach ($list as $id => $album): ?>
	<div class="article">
		<div class="header">
			<h4><?php echo Html::anchor('album/detail/'.$id, $album->name); ?></h4>

			<div class="member_img_box_s">
				<?php echo img((!empty($album->member))? $album->member->get_image() : '', '30x30', 'member/'.$album->member_id); ?>
				<div class="content">
					<div class="main">
						<b class="fullname"><?php echo Html::anchor('member/'.$album->member_id, (!empty($album->member))? $album->member->name : ''); ?></b>
					</div>
					<small><?php echo site_get_time($album->created_at); ?></small>
				</div>
			</div>
		</div>
		<div class="body"><?php echo nl2br(mb_strimwidth($album->body, 0, \Config::get('album.article_list.trim_width'), '...')) ?></div>
	</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<?php echo \Config::get('album.term.album'); ?>がありません。
<?php endif; ?>
