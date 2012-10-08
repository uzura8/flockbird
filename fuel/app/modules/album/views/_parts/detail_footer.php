<?php echo Asset::js('bootstrap-carousel.js');?>
<?php echo Asset::js('jquery.masonry.min.js');?>
<script type="text/javascript">
$('.carousel').carousel({
	interval: false
})

$('#ai_container').masonry({
	itemSelector : '.ai_item'
});
</script>
