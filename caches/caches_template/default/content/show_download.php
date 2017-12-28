<?php defined('IN_PHPCMS') or exit('No permission resources.'); ?><?php include template("content","header"); ?>
<link href="<?php echo CSS_PATH;?>download.css" rel="stylesheet" type="text/css" />
<div class="main">
	<!--left_bar-->
	<div class="col-left">
    <div class="crumbs"><a href="<?php echo siteurl($siteid);?>">首页</a><span> &gt; </span><?php echo catpos($catid);?><?php echo $title;?></div>
        <div class="box boxsbg">
        	<div class="contents">
      <table cellspacing="1" cellpadding="0" align="center" class="down_info">
        <caption>
        <h5><?php echo $title;?></h5>
        </caption>
        <tbody><tr>
          <th>授权形式：</th>
          <td><?php echo $copytype;?></td>
          <td width="260" rowspan="7" align="center" valign="middle"><?php if($thumb) { ?><img src="<?php echo $thumb;?>" width="250" height="220" /><?php } ?></td>
        </tr>
        <tr>
          <th>更新时间：</th>
          <td><?php echo $updatetime;?></td>
        </tr>
        <tr>
          <th>软件语言：</th>
          <td><?php echo $language;?></td>
        </tr>
        <tr>
          <th>软件平台：</th>
          <td><?php echo $systems;?></td>
        </tr>
        <tr>
          <th>软件类别：</th>
          <td> <?php echo $classtype;?></td>
        </tr>
        <tr>
          <th>文件大小：</th>
          <td> <?php echo $filesize;?></td>
        </tr>
        <tr>
          <th>评论等级：</th>
          <td class="c_orange"> <?php echo $stars;?></td>
        </tr>
<tr>
          <th>浏览次数：</th>
          <td colspan="2"><span id="hits"></span>（今日：<span id="todaydowns"></span>，本周：<span id="weekdowns"></span>，本月：<span id="monthdowns"></span>）</td>
        </tr>
      </tbody></table>
      <h5 class="tit">软件简介</h5>
      <div class="down_intro"><h4 class="tits"><?php echo $title;?></h4><?php echo $content;?></div>
      <h5 class="tit">下载地址</h5>
      <div class="down_address divl wrap">
        	<ul class="l xz_a wrap blue">
			<?php $n=1;if(is_array($downfile)) foreach($downfile AS $r) { ?>
	        	<li><?php echo $r;?></li>
			<?php $n++;}unset($n); ?>
			<?php $n=1;if(is_array($downfiles)) foreach($downfiles AS $r) { ?>
	        	<li><?php echo $r;?></li>
			<?php $n++;}unset($n); ?>			
        	</ul>
        <div class="ad"><script language="javascript" src="<?php echo APP_PATH;?>index.php?m=poster&c=index&a=show_poster&id=12"></script></div>
      </div>
	  </div>
	              </div>
      <div class="bk10"></div>
      <div class="Article-Tool">分享到：
	  <img src="http://v.t.qq.com/share/images/s/weiboicon16.png" style="padding-bottom:3px;" onclick="postToWb();" class="cu" title="分享到腾讯微博"/><script type="text/javascript">
	function postToWb(){
		var _t = encodeURI(document.title);
		var _url = encodeURIComponent(document.location);
		var _appkey = encodeURI("cba3558104094dbaa4148d8caa436a0b");
		var _pic = encodeURI('<?php echo $thumb;?>');
		var _site = '';
		var _u = 'http://v.t.qq.com/share/share.php?url='+_url+'&appkey='+_appkey+'&site='+_site+'&pic='+_pic+'&title='+_t;
		window.open( _u,'', 'width=700, height=680, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, location=yes, resizable=no, status=no' );
	}
</script>
          <script type="text/javascript">document.write('<a href="http://v.t.sina.com.cn/share/share.php?url='+encodeURIComponent(location.href)+'&appkey=3172366919&title='+encodeURIComponent('<?php echo new_addslashes($title);?>')+'" title="分享到新浪微博" class="t1" target="_blank">&nbsp;</a>');</script>
		  <script type="text/javascript">document.write('<a href="http://www.douban.com/recommend/?url='+encodeURIComponent(location.href)+'&title='+encodeURIComponent('<?php echo new_addslashes($title);?>')+'" title="分享到豆瓣" class="t2" target="_blank">&nbsp;</a>');</script>
		  <script type="text/javascript">document.write('<a href="http://share.renren.com/share/buttonshare.do?link='+encodeURIComponent(location.href)+'&title='+encodeURIComponent('<?php echo new_addslashes($title);?>')+'" title="分享到人人" class="t3" target="_blank">&nbsp;</a>');</script>
		  <script type="text/javascript">document.write('<a href="http://www.kaixin001.com/repaste/share.php?rtitle='+encodeURIComponent('<?php echo new_addslashes($title);?>')+'&rurl='+encodeURIComponent(location.href)+'&rcontent=" title="分享到开心网" class="t4" target="_blank">&nbsp;</a>');</script>
		  <script type="text/javascript">document.write('<a href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+encodeURIComponent(location.href)+'" title="分享到QQ空间" class="t5" target="_blank">&nbsp;</a>');</script>
      </div>
      <!--评论-->
      <div class="bk10"></div>
      <?php if($allow_comment && module_exists('comment')) { ?>
   <iframe src="<?php echo APP_PATH;?>index.php?m=comment&c=index&a=init&commentid=<?php echo id_encode("content_$catid",$id,$siteid);?>&iframe=1" width="100%" height="100%" id="comment_iframe" frameborder="0" scrolling="no"></iframe>
	<?php } ?>
    </div>
    <!--right_bar-->
    <div class="col-auto">
    	<!--广告228x162-->
    	<div class="brd mg_b10"><script language="javascript" src="<?php echo APP_PATH;?>index.php?m=poster&c=index&a=show_poster&id=6"></script></div>
        <div class="box">
            <h5 class="title-2">下载排行</h5>
            <ul class="content digg">
			<?php if(defined('IN_ADMIN')  && !defined('HTML')) {echo "<div class=\"admin_piao\" pc_action=\"content\" data=\"op=content&tag_md5=0ad40a45ad075d8f47798a231e25aec2&action=hits&catid=%24catid&num=10&order=views+DESC&cache=3600\"><a href=\"javascript:void(0)\" class=\"admin_piao_edit\">编辑</a>";}$tag_cache_name = md5(implode('&',array('catid'=>$catid,'order'=>'views DESC',)).'0ad40a45ad075d8f47798a231e25aec2');if(!$data = tpl_cache($tag_cache_name,3600)){$content_tag = pc_base::load_app_class("content_tag", "content");if (method_exists($content_tag, 'hits')) {$data = $content_tag->hits(array('catid'=>$catid,'order'=>'views DESC','limit'=>'10',));}if(!empty($data)){setcache($tag_cache_name, $data, 'tpl_data');}}?>
				<?php $n=1;if(is_array($data)) foreach($data AS $r) { ?>
					<li><a href="<?php echo $r['url'];?>" target="_blank"><?php echo $r['title'];?></a></li>
				<?php $n++;}unset($n); ?>
			<?php if(defined('IN_ADMIN') && !defined('HTML')) {echo '</div>';}?>
            </ul>
        </div>
        <div class="bk10"></div>
        <div class="box">
            <h5 class="title-2">相关下载</h5>
            <div class="content">
			    <ul class="list uls fs12">
					<?php if(defined('IN_ADMIN')  && !defined('HTML')) {echo "<div class=\"admin_piao\" pc_action=\"content\" data=\"op=content&tag_md5=39bad5ea0bde6b1e39a4e86ee47ec430&action=relation&relation=%24relation&catid=%24catid&num=5&keywords=%24rs%5Bkeywords%5D\"><a href=\"javascript:void(0)\" class=\"admin_piao_edit\">编辑</a>";}$content_tag = pc_base::load_app_class("content_tag", "content");if (method_exists($content_tag, 'relation')) {$data = $content_tag->relation(array('relation'=>$relation,'catid'=>$catid,'keywords'=>$rs[keywords],'limit'=>'5',));}?>
					<?php $n=1;if(is_array($data)) foreach($data AS $r) { ?>
						<li><span>(<?php echo date('m-d',$r[inputtime]);?>)</span>·<a href="<?php echo $r['url'];?>" target="_blank"><?php echo $r['title'];?></a></li>
					<?php $n++;}unset($n); ?>
					<?php if(defined('IN_ADMIN') && !defined('HTML')) {echo '</div>';}?>
                </ul>
            </div>
        </div>
        <div class="bk10"></div>
        <div class="box">
            <h5 class="title-2">推荐下载</h5>
            <ul class="content digg">
            	<?php if(defined('IN_ADMIN')  && !defined('HTML')) {echo "<div class=\"admin_piao\" pc_action=\"content\" data=\"op=content&tag_md5=450ae8156e03b9eb1468afff22c29126&action=position&posid=5&order=listorder+DESC&num=4\"><a href=\"javascript:void(0)\" class=\"admin_piao_edit\">编辑</a>";}$content_tag = pc_base::load_app_class("content_tag", "content");if (method_exists($content_tag, 'position')) {$data = $content_tag->position(array('posid'=>'5','order'=>'listorder DESC','limit'=>'4',));}?>
        	 	<?php $n=1;if(is_array($data)) foreach($data AS $r) { ?>        
                <li><a href="<?php echo $r['url'];?>" title="<?php echo $r['title'];?>"><?php echo str_cut($r[title],34);?></a></li>
               	<?php $n++;}unset($n); ?>  
             	<?php if(defined('IN_ADMIN') && !defined('HTML')) {echo '</div>';}?> 
            </ul>
        </div>
        <div class="bk10"></div>
        <!--广告228x170-->
    	<div class="brd mg_b10"><script language="javascript" src="<?php echo APP_PATH;?>index.php?m=poster&c=index&a=show_poster&id=7"></script></div>
    </div>
</div>

<div class="bk10"></div>
<script language="JavaScript" src="<?php echo APP_PATH;?>api.php?op=count&id=<?php echo $id;?>&modelid=<?php echo $modelid;?>"></script>
<?php include template("content","footer"); ?>