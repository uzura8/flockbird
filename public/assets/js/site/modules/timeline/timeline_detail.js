$(function() {
	removeLinkCommentBlocks();
})

function removeLinkCommentBlocks() {
	if (get_uid()) return;
	removeItems('.link_show_comment_form');
}
