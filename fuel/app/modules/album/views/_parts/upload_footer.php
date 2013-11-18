<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-default btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>{%=locale.fileupload.start%}</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-default btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>{%=locale.fileupload.cancel%}</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td colspan="2"></td>
        {% } %}
<?php if (isset($display_delete_button) && $display_delete_button === true): ?>
        <td class="delete">
            <button class="btn btn-default btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
                <i class="icon-trash icon-white"></i>
                <span>{%=locale.fileupload.destroy%}</span>
            </button>
            <input type="checkbox" name="delete" value="1">
        </td>
<?php endif; ?>
    </tr>
{% } %}
</script>
<?php echo asset::js('jqueryfileupload/vendor/jquery.ui.widget.js');?>

<!-- The Templates plugin is included to render the upload/download listings -->
<!-- <script src="http://blueimp.github.com/JavaScript-Templates/tmpl.min.js"></script> -->
<?php echo Asset::js('JavaScript-Templates/tmpl.min.js');?>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<!-- <script src="http://blueimp.github.com/JavaScript-Load-Image/load-image.min.js"></script> -->
<?php echo Asset::js('JavaScript-Load-Image/load-image.min.js');?>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<!-- <script src="http://blueimp.github.com/JavaScript-Canvas-to-Blob/canvas-to-blob.min.js"></script> -->
<?php echo Asset::js('JavaScript-Canvas-to-Blob/canvas-to-blob.min.js');?>
<!-- Bootstrap JS and Bootstrap Image Gallery are not required, but included for the demo -->
<!-- <script src="http://blueimp.github.com/cdn/js/bootstrap.min.js"></script> -->
<?php echo Asset::js('bootstrap.min.js');?>
<!-- <script src="http://blueimp.github.com/Bootstrap-Image-Gallery/js/bootstrap-image-gallery.min.js"></script> -->
<?php echo Asset::js('Bootstrap-Image-Gallery/js/bootstrap-image-gallery.min.js');?>

<?php echo asset::js('jqueryfileupload/jquery.iframe-transport.js');?>
<?php echo asset::js('jqueryfileupload/jquery.fileupload.js');?>
<?php echo asset::js('jqueryfileupload/jquery.fileupload-fp.js');?>
<?php echo asset::js('jqueryfileupload/jquery.fileupload-ui.js');?>
<?php echo asset::js('jqueryfileupload/locale.js');?>
<?php echo asset::js('jqueryfileupload/main.js');?>

<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="js/cors/jquery.xdr-transport.js"></script><![endif]-->
