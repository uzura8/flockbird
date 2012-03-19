<div class="mainMenu mb10">
  <ul class="clear mb10">
    <li class="news"><a href="/news/"><img src="/img/main_menu_news.gif" alt="最新ニュース" title="最新ニュース" /></a></li>
    <li class="bird"><a href="/birds/"><img src="/img/main_menu_bird.gif" alt="鳥から探す" title="鳥から探す" /></a></li>
    <li class="spot"><a href="/spots/"><img src="/img/main_menu_spot.gif" alt="スポットから探す" title="スポットから探す" /></a></li>
    <li class="item end"><a href="/item/"><img src="/img/main_menu_item.gif" alt="アイテムを探す" title="アイテムを探す" /></a></li>
  </ul>
  <ul class="clear">
	<li class="qa"><a href="/biginner/"><img src="/img/main_menu_qa.gif" alt="Ｑ＆Ａウォッチ" title="Ｑ＆Ａウォッチ" /></a></li>
    <li class="uranai"><a href="/fortune/"><img src="/img/main_menu_uranai.gif" alt="鳥占い" title="鳥占い" /></a></li>
    <li class="torilife"><a href="/about/"><img src="/img/main_menu_torilife.gif" alt="とりらいふについて" title="とりらいふについて" /></a></li>
    <li class="link end"><a href="/link/"><img src="/img/main_menu_link.gif" alt="リンク集" title="リンク集" /></a></li>
  </ul>
</div>

<h3 class="topicTitle">最新の投稿コメント＆写真</h3>
<div class="topicBox">
  <ul class="imgSearch">
<?php
//{section name=sec1 loop=$posts_list}
//    <li>
//      <dl class="clear">
//        <dt>{if $posts_list[sec1].blog_pic}<a href="/birds/{$posts_list[sec1].url}/#posts"><img src="/upload/{$posts_list[sec1].blog_pic}" width="145"></a>{/if}</dt>
//        <dd>
//          <p class="ttl"><a href="/birds/{$posts_list[sec1].url}/#posts"><strong>■{$posts_list[sec1].title}</strong> - {$posts_list[sec1].name}</a></p>
//          <p>{$posts_list[sec1].comment|truncate:300}</p>
//          <p>（投稿日時：{$posts_list[sec1].blog_date|replace:"-":"/"|truncate:16:""}）</p>
//        </dd>
//      </dl>
//    </li>
//{/section}
?>
  </ul>
</div>

<?php
//<div style="float: left;">
//<script type="text/javascript"><!--
//google_ad_client = "pub-2406494893216288";
///* 336x280, 作成済み 08/05/23 */
//google_ad_slot = "6133795075";
//google_ad_width = 336;
//google_ad_height = 280;
////-->
//</script>
//<script type="text/javascript"
//src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
//</script>
//</div>
//<div>
//<script type="text/javascript"><!--
//google_ad_client = "pub-2406494893216288";
///* 336x280, 作成済み 08/05/23 */
//google_ad_slot = "6133795075";
//google_ad_width = 336;
//google_ad_height = 280;
////-->
//</script>
//<script type="text/javascript"
//src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
//</script>
//</div>
//<div style="clear: left;"></div>
?>

<h3 class="topicTitle">野鳥ピックアップ　<a href="/birds/">⇒もっと見る</a></h3>
<div /* class="sort-birdName">
<?php
//{if $count_result != 0}
//<ul class="clear mb20">
//{section name=50A loop=$birds_list}
//  <li>
//    <dl class="clear">
//      <dt class="mb5"><a href="/birds/{$birds_list[50A].url}/">{$birds_list[50A].name}</a></dt>
//	  {if $birds_list[50A].pic_link}
//      <dd>{$birds_list[50A].pic_link}</dd>
//	  {else}
//	  <dd><a href="/birds/{$birds_list[50A].url}/"><img src="/img/birds/no_img_s.gif" alt="No Image" /></a></dd>
//	  {/if}
//    </dl>
//  </li>
//{/section}
//</ul>
//{/if}
?>
</div>

<h3 class="topicTitle">野鳥に関連したホットキーワード</h3>
<div class="keywords mb20">
  <ul class="clear">
<?php
//{section name=sec1 loop=$kizasi_data}
//    <li><a href="/news/{$kizasi_data[sec1].title_url}/">{$kizasi_data[sec1].title}</a></li>
//{/section}
?>
  </ul>
  <p class="serviceBy"><a href="http://kizasi.jp/tool/kizapi.html">Webサービス by kizasi.jp</a></p>
</div>
