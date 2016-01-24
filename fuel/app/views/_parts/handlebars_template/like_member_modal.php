<script type="text/x-handlebars-template" id="like_member_modal-template">
<div class="modal fade modal_liked_member" tabindex="-1" role="dialog" aria-hidden="true" id="modal_like_count_{{id}}">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?php echo sprintf('%sした%s', term('form.like'), term('member.view')); ?></h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
</script>

