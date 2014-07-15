<?php echo Asset::js('site/modules/timeline/common/load_timeline.js');?>

<?php
$size = empty($size) ? 'S' : strtoupper($size);
if (IS_SP) $size = Site_Util::convert_img_size_down($size) ?: $size;
$class_name = 'member_img_box_'.strtolower($size);
$img_size   = conf('upload.types.img.types.m.sizes.'.$size);
?>
<script type="text/x-handlebars-template" id="comment_form-template">
<div class="commentPostBox" id="commentPostBox_{{{this.id}}}">
	<div class="<?php echo $class_name; ?>">
		<?php echo img($u->get_image(), $img_size, 'member/'.$u->id, false, site_get_screen_name($u), true); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input">
					<textarea name="body" id="textarea_comment_{{{this.id}}}" class="form-control autogrow" rows="2"></textarea>
				</div>
				<div class="clearfix">
					<a href="#" id="btn_comment_{{{this.id}}}" class="btn btn-default btn-sm pull-right"><i class="glyphicon glyphicon-edit"></i><span class="hidden-xs-inline"> 送信</span></a>
				</div>
			</div>
		</div>
	</div>
</div>
</script>
