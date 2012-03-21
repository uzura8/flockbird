<div class="birdInfo mb30">
<div class="clear">
  <div class="L">
    <dl>
      <dt><span>■</span><?php echo $bird['name']; ?></dt>
      <dd><?php echo birds_get_bird_image_tag($bird['url'], $bird['img'], '', 300);?></dd>
    </dl>
  </div>

  <div class="R">
    <table border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <th colspan="2" class="group">
<?php if ($bird['moku']): ?>
          <?php echo $bird['moku']; ?>
<?php endif; ?>
<?php if ($bird['ka']): ?>
          <?php echo $bird['ka']; ?>
<?php endif; ?>
        </th>
      </tr> 
<?php if ($bird['life_type']): ?>
      <tr>
        <th>生活型</th>
        <td><?php echo $bird['life_type']; ?></td>
      </tr>
<?php endif; ?>
<?php if ($bird['b_life_place_id']): ?>
      <tr>
        <th>生息地</th>
        <td><?php echo $bird['place']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['b_watch_spot_id']): ?>
      <tr>
        <th>見られる場所</th>
        <td><?php echo $bird['spot']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['distoribution']): ?>
      <tr>
        <th>分布</th>
        <td><?php echo $bird['distoribution']; ?></td>
      </tr>
<?php endif; ?>
<?php if ($bird['season']): ?>
      <tr>
        <th>見られる時期</th>
        <td><?php echo $bird['season']; ?></td>
      </tr>
<?php endif; ?>
<?php if ($bird['size']): ?>
      <tr>
        <th>体長</th>
        <td>約<?php echo $bird['size']; ?>cm</td>
	  </tr>
<?php endif; ?>
<?php if ($bird['b_size_id']): ?>
      <tr>
        <th>大きさの目安</th>
        <td><?php echo $bird['size_meyasu']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['bill_type']): ?>
      <tr>
        <th>くちばし</th>
        <td><?php echo $bird['bill_type']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['tail_type']): ?>
      <tr>
        <th>尾羽</th>
        <td><?php echo $bird['tail_type']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['voice']): ?>
      <tr>
        <th>鳴き声</th>
        <td><?php echo $bird['voice']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['fly_type']): ?>
      <tr>
        <th>飛び方</th>
        <td><?php echo $bird['fly_type']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['sex_dif']): ?>
      <tr>
        <th>雌雄の特徴</th>
        <td><?php echo $bird['sex_dif']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['r_level']): ?>
      <tr>
        <th>レア度</th>
        <td><?php echo $bird['r_level']; ?></td>
	  </tr>
<?php endif; ?>
<?php if ($bird['exp']): ?>
      <tr>
        <th>説明</th>
        <td><?php echo $bird['exp|nl2br']; ?></td>
	  </tr>
<?php endif; ?>
    </table>
  </div>
</div>

<div style="float: left;">
<?php
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
?>
</div>
<div style="clear: left;"></div>

<?php /*
{if $wiki_data.body}
  <dl class="wiki mt15">
    <dt class="mb10"><strong>{$birds_info.name}について(wikipedia)</strong></dt>
    <dd class="mb5">{$wiki_data.body}</dd>
    <dd class="serviceBy right"><a href="http://www.simpleapi.net/">Webサービス by Simple API</a></dd>
  </dl>
{/if}

</div>

<a name="posts">
<h3 class="topicTitle">{$birds_info.name}に関する投稿</h3>
<div class="topicBox">
  <ul class="imgSearch">
    <li>
<p><strong><a href="/form/index.php?b_id={$birds_info.id}&s_id=">{$birds_info.name}の投稿フォーム</a></strong></p>
    </li>
{section name=sec1 loop=$postblog_list}
    <li>
      <dl class="clear">
        <dt>{if $postblog_list[sec1].blog_url}<a href="{$postblog_list[sec1].blog_url}" target="_blank"><img src="/upload/{$postblog_list[sec1].blog_pic}" width="145"></a>{else}<a href="/upload/{$postblog_list[sec1].blog_pic}" target="_blank"><img src="/upload/{$postblog_list[sec1].blog_pic}" width="145"></a>{/if}</dt>
        <dd>
          <p class="ttl">{if $postblog_list[sec1].blog_url}<a href="{$postblog_list[sec1].blog_url}" target="_blank">{$postblog_list[sec1].title}</a>{else}<a href="/upload/{$postblog_list[sec1].blog_pic}" target="_blank">{$postblog_list[sec1].title}</a>{/if}</p>
          <p>{$postblog_list[sec1].comment}</p>
          <p>(登録日:{$postblog_list[sec1].blog_date|replace:"-":"/"|truncate:16:""})</p>
		</dd>
	  </dl>
	</li>
{sectionelse}
    <li><p>現在{$birds_info.name}に関する投稿はありません。<br><a href="/form/index.php?b_id={$birds_info.id}&s_id=">投稿フォーム</a>より、画像やコメントなどぜひぜひご投稿ください。</p></li>
{/section}
  </ul>
</div>

<h3 class="topicTitle">イメージ検索結果</h3>
<div class="topicBox">
  <ul class="imgSearch">
{section name=sec1 loop=$pict_data}
    <li>
	  <dl class="clear">
	    <dt><a href="{$pict_data[sec1].ClickUrl}" target="_blank"><img src="{$pict_data[sec1].Thumbnail}"></a></dt>
        <dd>
		  <p class="ttl"><a href="{$pict_data[sec1].RefererUrl}" target="_blank">{$pict_data[sec1].Title}</a></p>
          <p>{$pict_data[sec1].Summary}</p>
		</dd>
	  </dl>
	</li>
{sectionelse}
    <li>写真を見つけられませんでした。</li>
{/section}
  </ul>
  <p class="serviceBy"><a href="http://developer.yahoo.co.jp/about">Webサービス by Yahoo! JAPAN</a></p>
</div>

<h3 class="topicTitle">{$birds_info.name}ブログ検索結果</h3>
{include file="parts/blog_search_results.tpl"}
*/ ?>
