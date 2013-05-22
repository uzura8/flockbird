<?php echo Asset::js('jquery.masonry.min.js');?>
<script type="text/javascript">
//$('#article_list').masonry({
//	itemSelector : '.article'
//});
$(function(){
	var $container = $('#main_container');
	$container.imagesLoaded(function(){
		$container.masonry({
			itemSelector : '.main_item'
		});

		$container.infinitescroll({
			navSelector  : '#page-nav',   // ページのナビゲーションを選択
			nextSelector : '#page-nav a', // 次ページへのリンク
			itemSelector : '.main_item',    // 持ってくる要素のclass
			loading: {
				finishedMsg: '<?php echo \Config::get('album.term.album'); ?>がありません。', //次のページがない場合に表示するテキスト
				img: '<?php echo \Uri::create('assets/img/site/loading_l.gif'); ?>' //ローディング画像のパス
			}
		},
		function( newElements ) {
			var $newElems = $( newElements ).css({ opacity: 0 });
			$newElems.imagesLoaded(function(){
				$newElems.animate({ opacity: 1 });
				$container.masonry( 'appended', $newElems, true );
			});
		});
	});
});

</script>
