<ul class="birdNavi clear mb20">
<?php if (Request::active()->action != 'index'): ?>  <li><a href="/birds/">■鳥をアイウエオ順に探す</a></li><?php endif; ?>
<?php if (Request::active()->action != 'life_place'): ?>  <li><a href="/birds/life_place/">■鳥の生活場所から探す</a></li><?php endif; ?>
<?php if (Request::active()->action != 'watch_spot'): ?>  <li><a href="/birds/watch_spot/">■鳥の見られる場所から探す</a></li><?php endif; ?>
<?php if (Request::active()->action != 'size'): ?>  <li><a href="/birds/size/">■鳥のサイズから探す</a></li><?php endif; ?>
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

<h3 class="topicTitle"><?php echo $subtitle; ?></h3>

<div style="margin-bottom: 10px;">
<p><strong>
<?php $i = 0; ?>
<?php foreach ($parent_list as $key => $value): ?>
<?php if ($i) echo '　'; ?>
<a href="#list<?php echo $key; ?>"><?php echo $value; ?></a>
<?php $i++; ?>
<?php endforeach; ?>
</strong></p>
</div>

<div class="sort-birdName">
<?php foreach ($parent_list as $key => $value): ?>
<?php $var_name = 'birds_listP'.$key; ?>
<?php if (!empty($bird_list[$key])): ?>
<a name="list<?php echo $key; ?>"></a>
<p class="mb10"><?php echo $value; ?></p>
<ul class="clear mb20">
<?php foreach ($bird_list[$key] as $row): ?>
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
</div><!-- sort-birdName -->
