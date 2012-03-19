<ul class="birdNavi clear mb20">
  <li><a href="/birds/life_place/">■鳥の生活場所で探す</a></li>
  <li><a href="/birds/wspot/">■鳥を見かける場所で探す</a></li>
  <li><a href="/birds/size/">■鳥のサイズから探す</a></li>
</ul>
<?php
//<div style="float: left;">
//<script type="text/javascript"><!--
//google_ad_client = "pub-2406494893216288";
///* 336x280, 作成済み 08/02/13 */
//google_ad_slot = "8935050335";
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
///* 336x280, 作成済み 08/02/13 */
//google_ad_slot = "8935050335";
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

<h3 class="topicTitle">鳥をアイウエオ順に探す</strong></h3>

<div style="margin-bottom: 10px;">
<p><strong>
<?php foreach ($initial_syllabary_list as $key => $syllabary_row): ?>
<?php if ($key != 'A') echo '　'; ?>
<a href="#list<?php echo $key; ?>"><?php echo $syllabary_row['view']; ?>行</a>
<?php endforeach; ?>
</strong></p>
</div>

<!-- 五十音 -->
<div class="sort-birdName">

<?php foreach ($initial_syllabary_list as $key => $syllabary_row): ?>
<?php $var_name = 'birds_list50'.$key; ?>
<?php if ($$var_name): ?>
<a name="list<?php echo $key; ?>"></a>
<p class="mb10"><?php echo $syllabary_row['view']; ?>行</p>
<ul class="clear mb20">
<?php foreach ($$var_name as $row): ?>
  <li>
    <dl class="clear">
      <dt class="mb5"><a href="/birds/<?php echo $row['url']; ?>/"><?php echo $row['name']; ?></a></dt>
      <dd><?php echo birds_get_bird_image_link($row['url'], $row['img'], 'sml');?></dd>
    </dl>
  </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php endforeach; ?>
