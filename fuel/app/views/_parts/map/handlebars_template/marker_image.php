<script type="text/x-handlebars-template" id="map-marker-image-template">
{{# if alt}}
<h4>{{alt}}</h4>
{{/if}}
<img src="{{site_url uri}}"{{# if alt}} alt="{{alt}}"{{/if}} />
</script>
