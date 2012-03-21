<?php echo Html::doctype(); ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title><?php echo (!empty($header_title)) ? $header_title : $title; ?></title>
  <meta NAME="robots" CONTENT="index,follow">
  <meta name="description" content="<?php echo (!$header_description) ? $header_description : PRJ_HEADER_DESCRIPTION_DEFAULT; ?>">
  <meta name="keywords" content="<?php echo site_header_keywords($header_keywords); ?>">
  <meta http-equiv="Content-Style-Type" content="text/css">
<?php if (GOOGLE_SITE_VERIFICATION): ?>  <meta name="google-site-verification" content="<?php echo GOOGLE_SITE_VERIFICATION; ?>" /><?php endif; ?>
  <link rel="shortcut icon"href="/favicon.ico">
	<?php //echo Asset::css(array('bootstrap.css', 'torilife/bw.css')); ?>
  <link href="<?php echo Uri::create('/css/bw.css'); ?>" rel="stylesheet" type="text/css">
</head>
<body <?php if (!empty($google_map_on)): ?>onload="onLoad();"<?php endif; ?>>
<div id="all"><!-- #page ページの整形：中央寄せとか -->

<div id="head"><!-- #header 画面上部のヘッド部分 -->
  <h1><a href="/"><img src="/img/logo.gif" alt="<?php echo PRJ_SITE_DESCRIPTION.' '.PRJ_SITE_NAME; ?>"></a></h1>
<?php if (Auth::check()): ?>
  <p><a href="/site/logout">ログアウト</a><p>
<?php endif; ?>
  <p class="sitemap"><a href="/sitemap/"><img src="/img/sitemap.gif" alt="サイトマップ" /></a><p>
</div><!-- head -->

<div id="pankuzu">
<?php foreach ($breadcrumbs as $name => $path): ?>
<?php echo ($path) ? Html::anchor($path, $name).'&nbsp;&gt;&nbsp;' : sprintf('<strong>%s</strong>', $name);?>
<?php endforeach; ?>
</div>
<div class="clear">

<div id="globalNavi">
  <ul>
    <li class="top"><a href="/"><img src="/img/navi_top.gif" alt="TOP" title="TOP" /></a></li>
    <li class="news"><a href="/news/"><img src="/img/navi_news.gif" alt="最新ニュース" title="最新ニュース" /></a></li>
    <li class="bird"><a href="/birds/"><img src="/img/navi_bird.gif" alt="鳥から探す" title="鳥から探す" /></a></li>
    <li class="spot"><a href="/spots/"><img src="/img/navi_spot.gif" alt="スポットから探す" title="スポットから探す" /></a></li>
    <li class="item"><a href="/item/"><img src="/img/navi_item.gif" alt="アイテムを探す" title="アイテムを探す" /></a></li>
    <li class="qa"><a href="/biginner/"><img src="/img/navi_qa.gif" alt="Ｑ＆Ａウォッチ" title="Ｑ＆Ａウォッチ" /></a></li>
    <li class="uranai"><a href="/fortune/"><img src="/img/navi_uranai.gif" alt="鳥占い" title="鳥占い" /></a></li>
    <li class="torilife"><a href="/about/"><img src="/img/navi_torilife.gif" alt="とりらいふについて" title="とりらいふについて" /></a></li>
    <li class="link"><a href="/link/"><img src="/img/navi_link.gif" alt="リンク集" title="リンク集" /></a></li>
  </ul>

<?php /*
<!-- SiteSearch Google -->
<form method="get" action="http://www.google.co.jp/custom" target="_top">
<table border="0" bgcolor="#ffffff">
<tr><td nowrap="nowrap" valign="top" align="left" height="32">

</td>
<td nowrap="nowrap">
<input type="hidden" name="domains" value="<?php echo PRJ_HOSTNAME; ?>"></input>
<label for="sbi" style="display: none">検索用語を入力</label>
<input type="text" name="q" size="20" maxlength="255" value="" id="sbi"></input>
</td></tr>
<tr>
<td>&nbsp;</td>
<td nowrap="nowrap">
<table>
<tr>
<td>
<input type="radio" name="sitesearch" value="" checked id="ss0"></input>
<label for="ss0" title="ウェブ検索"><font size="-1" color="black">Web</font></label></td>
<td>
<input type="radio" name="sitesearch" value="<?php echo PRJ_BASE_URL; ?>" id="ss1"></input>
<label for="ss1" title="検索 <?php echo PRJ_HOSTNAME; ?>"><font size="-1" color="black"><?php echo PRJ_HOSTNAME; ?></font></label></td>
</tr>
</table>
<label for="sbb" style="display: none">検索フォームを送信</label>
<input type="submit" name="sa" value="Google 検索" id="sbb"></input>
<input type="hidden" name="client" value="pub-2406494893216288"></input>
<input type="hidden" name="forid" value="1"></input>
<input type="hidden" name="ie" value="UTF-8"></input>
<input type="hidden" name="oe" value="UTF-8"></input>
<input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#336699;VLC:663399;AH:center;BGC:FFFFFF;LBGC:336699;ALC:0000FF;LC:0000FF;T:000000;GFNT:0000FF;GIMP:0000FF;FORID:1"></input>
<input type="hidden" name="hl" value="ja"></input>
</td></tr></table>
</form>
<!-- SiteSearch Google -->
*/ ?>

<?php /*
<!-- Rakuten Dynamicad FROM HERE -->
<script type="text/javascript">
<!--
rakuten_template = "s_160_600_img";
rakuten_affiliateId = "06e8db06.384faeaf.06e8db07.bc13bd20";
rakuten_target = "_blank";
rakuten_color_bg = "FFFFFF";
rakuten_color_border = "FFFFFF";
rakuten_color_text = "000000";
rakuten_color_link = "0000FF";
rakuten_color_price = "CC0000";
//--></script>
<script type="text/javascript"
  src="http://dynamic.rakuten.co.jp/js/rakuten_dynamic.js">
</script>
<!-- Rakuten Dynamicad TO HERE -->
*/?>
</div><!-- globalNavi -->

<div id="main">
	<div class="padding">
	<div class="pageTitle"><h2><?php echo $title; ?></h2></div>
<?php echo $content; ?>
	</div><!-- padding -->
</div><!-- main -->
</div><!-- clear -->

<div id="foot">
  <p><a href="http://www.birdingtop500.com/cgi-bin/topsite/topsite.cgi?user=kazumaryu" target="_blank"><img src="http://www.birdingtop500.com/cgi-bin/topsite/counter.cgi?user=kazumaryu" alt="Birding Top 500 Counter" border=1></a><p><a href="http://www.birdingtop500.com/cgi-bin/topsite/topsite.cgi?user=kazumaryu" target="_blank">世界の野鳥サイトランキング </a></p>
<?php /*
      <IFRAME frameBorder="0" allowTransparency="true" height="60" width="234" marginHeight="0" scrolling="no" src="http://ad.jp.ap.valuecommerce.com/servlet/htmlbanner?sid=2460434&pid=876762650" MarginWidth="0"><script Language="javascript" Src="http://ad.jp.ap.valuecommerce.com/servlet/jsbanner?sid=2460434&pid=876762650"></script><noscript><a Href="http://ck.jp.ap.valuecommerce.com/servlet/referral?sid=2460434&pid=876762650" target="_blank" ><img Src="http://ad.jp.ap.valuecommerce.com/servlet/gifbanner?sid=2460434&pid=876762650" height="60" width="234" Border="0"></a></noscript></IFRAME>
*/ ?>
  <p><img src="/img/footer.gif" alt="コピーライト" />Copyright : 2007 - <?php echo date('%Y'); ?> とりらいふ</p>
</div><!-- foot -->

</div><!-- all -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-4490132-5");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
