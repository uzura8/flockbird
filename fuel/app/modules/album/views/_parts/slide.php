<?php if (!empty($is_modal) && !empty($title)): ?>
<div class="modal-header">
	<h4 class="modal-title"><?php echo $title; ?></h4>
</div>
<?php endif; ?>

<?php if (!empty($body)): ?>
<p><?php echo nl2br($body) ?></p>
<?php endif; ?>

<?php
$block_attrs = array(
	'class' => 'row',
	'id' => 'img_comment_box',
	'data-content' => !empty($content_type) ? $content_type : 'album',
	'data-content_id' => $content_id,
);
if (!empty($start_id)) $block_attrs['data-start_id'] = $start_id;
?>
<div <?php echo Util_Array::conv_array2attr_string($block_attrs); ?>>
	<div class="col-md-8">
		<a name="slidetop" id="slidetop"></a>
		<div id="myCarousel" class="carousel carousel-flex slide" data-ride="carousel">
			<!-- Wrapper for slides -->
			<div class="carousel-inner"></div>
			<!-- Controls -->
			<a class="left carousel-control" href="#myCarousel" data-slide="prev">
				<span class="glyphicon glyphicon-chevron-left"></span>
			</a>
			<a class="right carousel-control" href="#myCarousel" data-slide="next">
				<span class="glyphicon glyphicon-chevron-right"></span>
			</a>
		</div>
		<div id="slideNumber"></div>
		<div id="link2detail" class="mtb5"></div>
	</div>
	<div class="col-md-4">
		<div id="comment_info"></div>
		<div id="comment_list"></div>
	</div>
</div>

<?php if (!empty($is_modal)): ?>
<hr>
<div class="clearfix">
	<button type="button" class="btn btn-default pull-right" data-dismiss="modal">Close</button>
</div>
<?php endif; ?>

<?php if (!empty($is_modal)): ?>
<?php echo render('_parts/slide_footer', array('is_desc' => true, 'is_modal' => true)); ?>
<?php endif; ?>

