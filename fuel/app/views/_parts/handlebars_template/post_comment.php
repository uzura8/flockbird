<script type="text/x-handlebars-template" id="comment_form-template">
<div class="commentPostBox" id="commentPostBox_{{{this.id}}}">
	<div class="member_contents row">
		<div class="col-xs-1">
			<?php echo img($u->get_image(), $size, 'member/'.$u->id, false, site_get_screen_name($u), true, true); ?>
		</div>
		<div class="col-xs-11">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input">
					<textarea name="body" id="textarea_comment_{{{this.id}}}" class="form-control autogrow" rows="1"></textarea>
				</div>
				<div class="clearfix">
					<a href="#" id="btn_comment_{{{this.id}}}" class="btn btn-default btn-sm pull-right js-ajax-postComment" data-template="#comment-template" data-list="{{{this.listSelector}}}"{{#if this.counterSelector}} data-counter="{{{this.counterSelector}}}"{{/if}} data-get_uri="{{{this.getUri}}}" data-latest="1" data-post_uri="{{{this.postUri}}}" data-textarea="#textarea_comment_{{{this.id}}}"><i class="glyphicon glyphicon-edit"></i><span class="hidden-xs-inline"> <?php echo term('form.submit'); ?></span></a>
				</div>
			</div>
		</div>
	</div>
</div>
</script>
