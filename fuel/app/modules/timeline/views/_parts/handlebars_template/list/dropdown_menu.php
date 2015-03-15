<script type="text/x-handlebars-template" id="tl_dropdown_menu-tpl">
{{#if detail_uri}}
<li><a href="{{{detail_uri}}}"><?php echo icon_label('site.show_detail', 'both', false); ?></a></li>
{{else}}
<li><span class="disabled"><?php echo icon_label('site.show_detail', 'both', false); ?></span></li>
{{/if}}
{{#if delete_uri}}<li><a href="#" class="js-ajax-delete" data-uri="{{{delete_uri}}}" data-parent="#{{{parent_id}}}"><?php echo icon_label('form.do_delete', 'both', false); ?></a></li>{{/if}}
</script>
