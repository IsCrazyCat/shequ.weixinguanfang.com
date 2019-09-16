<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:39:"./template/mobile/new2/index\index.html";i:1567479688;s:43:"./template/mobile/new2/public\wx_share.html";i:1558745778;s:49:"./template/mobile/new2/public\wx_share_index.html";i:1558682064;s:47:"./template/mobile/new2/public\wx_subscribe.html";i:1566263775;}*/ ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>首页-<?php echo $tpshop_config['shop_info_store_title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <meta http-equiv="keywords" content="<?php echo $tpshop_config['shop_info_store_keyword']; ?>" />
    <meta name="description" content="<?php echo $tpshop_config['shop_info_store_desc']; ?>" />
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" type="text/css" href="__STATIC__/css/index.css"/>
    <link rel="stylesheet" href="__STATIC__/dki/css/style.css" />

    <script src="__STATIC__/dki/js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="__STATIC__/dki/js/fontsize.js"></script>
    <script type="text/javascript" src="__STATIC__/dki/js/animation.js"></script>
</head>

<body>
<div id="search_hide" class="search_hide" style="padding:0px;margin-top:0px;">
    <div style='background:#FAE651;height:100%;padding:0px;margin-top:0px;padding-top:0.1rem;'>
        <h2> <span class="close"><img style="margin-top:0.55rem;" src="__STATIC__/dki/images/list-return.png"></span></h2>
        <div id="mallSearch" class="search_mid">
            <div id="search_tips" style="display:none;"></div>
            <div class="searchdotm" style="background:#fff;    border-radius: 0.35rem;">
                <form class="set_ip"name="sourch_form" id="sourch_form" method="post" action="<?php echo U('Goods/search'); ?>">
                    <div class="mallSearch-input">
                        <div id="s-combobox-135">
                            <input class="s-combobox-input" name="q" id="q"  placeholder="请输入关键词"  type="text" value="<?php echo I('q'); ?>" />
                        </div>
                        <input type="button" value="" class="button"  onclick="if($.trim($('#q').val()) != '') $('#sourch_form').submit();" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <section class="mix_recently_search"><h3>热门搜索</h3>
        <ul>
            <?php if(is_array($tpshop_config['hot_keywords']) || $tpshop_config['hot_keywords'] instanceof \think\Collection || $tpshop_config['hot_keywords'] instanceof \think\Paginator): if( count($tpshop_config['hot_keywords'])==0 ) : echo "" ;else: foreach($tpshop_config['hot_keywords'] as $k=>$wd): ?>
                <li><a href="<?php echo U('Goods/search',array('q'=>$wd)); ?>" <?php if($k == 0): ?>class="ht"<?php endif; ?>><?php echo $wd; ?></a></li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </section>
</div>
<div id="shoppage">
    <!--<header id="header">
        <a href="<?php echo U('Goods/categoryList'); ?>" class="top_bt"></a>
        <a href="<?php echo U('Cart/cart'); ?>" class='user_btn'></a>
        <span href="javascript:void(0)" class="logo"><?php echo $tpshop_config['shop_info_store_name']; ?></span>
    </header>-->

    <div class="indexTop" >
        <ul class="nav clearfloat model" id="nav">
            <li id="a11" class="active"><a href="<?php echo U('User/super'); ?>">SQX</a></li>
            <li id="a22"><a href="#a2">零售区</a></li>
            <li id="a33"><a href="#a3">批发区</a></li>
            <li style="display: none" id="a44"><a href="#a4">特价区</a></li>
            <li id="a44"><a href="<?php echo U('Mobile/User/daishouqu',array('type'=>'DAISHOU')); ?>">代售区</a></li>
            <li id="a44"><a href="<?php echo U('Mobile/User/chongzhi'); ?>">充值</a></li>
            <button class="search" id="search_text"></button>
        </ul>
        <div id="indexBanner"   style="background-color: #FFF000;">
            <ul class="img" id="a1">
                <?php $pid =2;$ad_position = M("ad_position")->cache(true,TPSHOP_CACHE_TIME)->column("position_id,position_name,ad_width,ad_height","position_id");$result = M("ad")->where("pid=$pid  and enabled = 1 and start_time < 1568170800 and end_time > 1568170800 ")->order("orderby desc")->cache(true,TPSHOP_CACHE_TIME)->limit("1")->select();
if(!in_array($pid,array_keys($ad_position)) && $pid)
{
  M("ad_position")->insert(array(
         "position_id"=>$pid,
         "position_name"=>CONTROLLER_NAME."页面自动增加广告位 $pid ",
         "is_open"=>1,
         "position_desc"=>CONTROLLER_NAME."页面",
  ));
  delFile(RUNTIME_PATH); // 删除缓存
  \think\Cache::clear();  
}


$c = 1- count($result); //  如果要求数量 和实际数量不一样 并且编辑模式
if($c > 0 && I("get.edit_ad"))
{
    for($i = 0; $i < $c; $i++) // 还没有添加广告的时候
    {
      $result[] = array(
          "ad_code" => "/public/images/not_adv.jpg",
          "ad_link" => "/index.php?m=Admin&c=Ad&a=ad&pid=$pid",
          "title"   =>"暂无广告图片",
          "not_adv" => 1,
          "target" => 0,
      );  
    }
}
foreach($result as $key=>$v):       
    
    $v[position] = $ad_position[$v[pid]]; 
    if(I("get.edit_ad") && $v[not_adv] == 0 )
    {
        $v[style] = "filter:alpha(opacity=50); -moz-opacity:0.5; -khtml-opacity: 0.5; opacity: 0.5"; // 广告半透明的样式
        $v[ad_link] = "/index.php?m=Admin&c=Ad&a=ad&act=edit&ad_id=$v[ad_id]";        
        $v[title] = $ad_position[$v[pid]][position_name]."===".$v[ad_name];
        $v[target] = 0;
    }
    ?>
                    <li style="background-color:#ffe749"><a href="<?php echo U('Mobile/User/chongzhi'); ?>" <?php if($v['target'] == 1): ?>target="_blank"<?php endif; ?> ><img src="<?php echo $v[ad_code]; ?>" title="<?php echo $v[title]; ?>"width="100%" style="<?php echo $v[style]; ?>"/></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="dot"></div>
        </div>
    </div>

    <div class="index" style="overflow-x: hidden;">

        <div class="title1"><a href="<?php echo U('mobile/user/chongzhi'); ?>"><img src="__STATIC__/dki/images/index-title1.png" /></a></div>
        <div class="index-infor clearfloat model">
            <div class="left b">社群新零售</div>
            <div class="scrollDiv left" id="s1">
                <ul>

                    <?php if(is_array($dklist) || $dklist instanceof \think\Collection || $dklist instanceof \think\Paginator): $i = 0; $__LIST__ = $dklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <li class="clearfloat">
                            <div class="li">
                                <b>手机号</b>
                                <span><?php echo $v[nickname]; ?></span>
                            </div>
                            <div class="li">
                                <b>注册时间</b>
                                <span><?php echo date('Y/m/d H:i:m',$v[reg_time]); ?></span>
                            </div>
                        </li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div>
            <div class="right imgStar" id="a2"><img src="__STATIC__/dki/images/index-img1.png" /></div>
        </div>

        <div class="index-free clearfloat model">
            <div class="left">
                <div class="box1 model">
                </div>

            </div>
            <div class="right">

            </div>
        </div>

        <div class="index-shop">
            <div class="title">
                <b>—  零售区  —</b>
                <span>RETAIL SHOPPING DISTRICT</span>
            </div>
            <div class="clearfloat">
                <?php
                                   
                                $md5_key = md5("select * from __PREFIX__goods where is_recommend=1 and is_on_sale=1 and cat_id=3 order by sort desc limit 9");
                                $result_name = $sql_result_v = S("sql_".$md5_key);
                                if(empty($sql_result_v))
                                {                            
                                    $result_name = $sql_result_v = \think\Db::query("select * from __PREFIX__goods where is_recommend=1 and is_on_sale=1 and cat_id=3 order by sort desc limit 9"); 
                                    S("sql_".$md5_key,$sql_result_v,1);
                                }    
                              foreach($sql_result_v as $k=>$v): ?>
                    <a href="<?php echo U('Mobile/Goods/goodsInfo',array('id'=>$v[goods_id])); ?>">
                    <dl class="left model">

                        <dt><img src="<?php echo goods_thum_images($v['goods_id'],400,400); ?>" /></dt>

                        <dd>
                            <div class="title"><?php echo $v['goods_name']; ?></div>
                            <div class="clearfloat">
                                <div class="left dkiPrice">价格：<?php echo $v['shop_price']; ?></div>
                                <ul class="clearfloat right str">
                                    <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                    <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                    <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                    <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                    <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                </ul>
                            </div>
                            <div style="display: none" class="price">市场价：<span>￥<?php echo $v['market_price']; ?></span></div>
                            <div class="buy-immediately" style="color: red;margin-top: 9px">点击购买</div>
                        </dd>

                    </dl>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>



        <div class="index-shop">
            <!--批发区-->
            <div class="title2" id="a3"><a href="<?php echo U('Mobile/index/pifaqu'); ?>" ><img src="__STATIC__/dki/images/index-title2.png" /></a></div>
            <div class="clearfloat">
                <?php
                                   
                                $md5_key = md5("select * from __PREFIX__goods where is_recommend=1 and is_on_sale=1 and cat_id=4 order by sort desc limit 9");
                                $result_name = $sql_result_v = S("sql_".$md5_key);
                                if(empty($sql_result_v))
                                {                            
                                    $result_name = $sql_result_v = \think\Db::query("select * from __PREFIX__goods where is_recommend=1 and is_on_sale=1 and cat_id=4 order by sort desc limit 9"); 
                                    S("sql_".$md5_key,$sql_result_v,1);
                                }    
                              foreach($sql_result_v as $k=>$v): ?>
                    <a href="<?php echo U('Mobile/Goods/goodsInfo',array('id'=>$v[goods_id])); ?>">
                        <dl class="left model">

                            <dt><img src="<?php echo goods_thum_images($v['goods_id'],400,400); ?>" /></dt>

                            <dd>
                                <div class="title"><?php echo $v['goods_name']; ?></div>
                                <div class="clearfloat">
                                    <div class="left dkiPrice">价格：<?php echo $v['shop_price']; ?></div>
                                    <ul class="clearfloat right str">
                                        <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                        <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                        <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                        <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                        <li class="right"><img src="__STATIC__/dki/images/star.png" /></li>
                                    </ul>
                                </div>
                                <div style="display: none" class="price">市场价：<span>￥<?php echo $v['market_price']; ?></span></div>
                                <div class="buy-immediately" style="color: red;margin-top: 9px">点击购买</div>
                            </dd>

                        </dl>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <!--特价区-->
        <div class="index-shop index-shop2" style="display: none">
            <div class="title">
                <b>—  特价区  —</b>
                <span>SPECIAL ZONE</span>
            </div>
            <div class="clearfloat">
                <?php
                                   
                                $md5_key = md5("select * from __PREFIX__goods where is_recommend=1 and is_on_sale=1 and cat_id=26 order by sort desc limit 8");
                                $result_name = $sql_result_v = S("sql_".$md5_key);
                                if(empty($sql_result_v))
                                {                            
                                    $result_name = $sql_result_v = \think\Db::query("select * from __PREFIX__goods where is_recommend=1 and is_on_sale=1 and cat_id=26 order by sort desc limit 8"); 
                                    S("sql_".$md5_key,$sql_result_v,1);
                                }    
                              foreach($sql_result_v as $k=>$v): ?>
                    <dl class="left model">
                        <dt><a href="<?php echo U('Mobile/Goods/goodsInfo',array('id'=>$v[goods_id])); ?>"><img src="<?php echo goods_thum_images($v['goods_id'],400,400); ?>"></a></dt>
                        <dd>
                            <style>
                                .yihang{
                                    white-space: nowrap;
                                    text-overflow:ellipsis;
                                    text-overflow: ellipsis;
                                    overflow:hidden;
                                }
                            </style>
                            <div class="title yihang"><a href="#"><?php echo $v['goods_name']; ?></a></div>
                            <div class="clearfloat">
                                <div class="left dkiPrice">DKI价格：<?php echo $v['shop_price']; ?></div>

                            </div>
                            <div class="price">市场价：<span>￥<?php echo $v['market_price']; ?></span></div>
                            <div class="buy-immediately"><a href="javascript:AjaxAddCart(<?php echo $v[goods_id]; ?>,1,0);">立即购买</a></div>
                        </dd>
                    </dl>
                <?php endforeach; ?>
            </div>
        </div>


    </div>
</div>




<script type="text/javascript">
    $(function() {
        $('#search_text').click(function(){
            $("#shoppage").children('div').hide();
            $("#search_hide").css('position','fixed').css('top','0px').css('width','100%').css('z-index','999').show();
        })
        $('#get_search_box').click(function(){
            $("#shoppage").children('div').hide();
            $("#search_hide").css('position','fixed').css('top','0px').css('width','100%').css('z-index','999').show();
        })
        $("#search_hide .close").click(function(){
            $("#shoppage").children('div').show();
            $("#search_hide").hide();
        })
    });
</script>

<!--DKI footer 20190721-->
<div class="footer clearfloat">
    <dl class="left <?php if(CONTROLLER_NAME == 'Index'): ?> active<?php endif; ?> ">
    <a href="<?php echo U('Index/index'); ?>">
        <dt></dt>
        <dd>首页</dd>
    </a>
    </dl>
    <dl class="left">
        <a onclick="alert('客服电话：17564506417 客服QQ:2665871937')">
            <dt></dt>
            <dd>客服</dd>
        </a>
    </dl>
    <dl class="left">
        <a href="#">
            <dt></dt>
            <dd>社群新零售</dd>
        </a>
    </dl>
    <dl class="left  <?php if(CONTROLLER_NAME == 'Cart'): ?> active<?php endif; ?>  ">
    <a href="<?php echo U('Cart/cart'); ?>">
        <dt></dt>
        <dd>购物车</dd>
    </a>
    </dl>
    <dl class="left <?php if(CONTROLLER_NAME == 'User'): ?> active<?php endif; ?> ">
    <li><a href="<?php echo U('User/index'); ?>">
        <dt></dt>
        <dd>个人中心</dd>
    </a>
        </dl>
</div>

<script type="text/javascript">
    /*$(document).ready(function(){
          var cart_cn = getCookie('cn');
          if(cart_cn == ''){
            $.ajax({
                type : "GET",
                url:"/index.php?m=Home&c=Cart&a=header_cart_list",//+tab,
                success: function(data){
                    cart_cn = getCookie('cn');
                    $('#cart_quantity').html(cart_cn);
                }
            });
          }
          $('#cart_quantity').html(cart_cn);
    });*/
</script>
<!-- 微信浏览器 调用微信 分享js-->
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="__PUBLIC__/js/global.js"></script>
<script type="text/javascript">
<?php if(ACTION_NAME == 'goodsInfo'): ?>
   var ShareLink = "http://<?php echo $_SERVER[HTTP_HOST]; ?>/index.php?m=Mobile&c=Goods&a=goodsInfo&id=<?php echo $goods[goods_id]; ?>"; //默认分享链接
   var ShareImgUrl = "http://<?php echo $_SERVER[HTTP_HOST]; ?><?php echo goods_thum_images($goods[goods_id],400,400); ?>"; // 分享图标
<?php else: ?>
   var ShareLink = "http://<?php echo $_SERVER[HTTP_HOST]; ?>/index.php?m=Mobile&c=Index&a=index"; //默认分享链接
   var ShareImgUrl = "http://<?php echo $_SERVER[HTTP_HOST]; ?><?php echo $tpshop_config['shop_info_store_logo']; ?>"; //分享图标
<?php endif; ?>

var is_distribut = getCookie('is_distribut'); // 是否分销代理
var user_id = getCookie('user_id'); // 当前用户id
//alert(is_distribut+'=='+user_id);
// 如果已经登录了, 并且是分销商
if(parseInt(is_distribut) == 1 && parseInt(user_id) > 0)
{									
	ShareLink = ShareLink + "&first_leader="+user_id;									
}

$(function() {
	if(isWeiXin() && parseInt(user_id)>0){
		$.ajax({
			type : "POST",
			url:"/index.php?m=Mobile&c=Index&a=ajaxGetWxConfig&t="+Math.random(),
			data:{'askUrl':encodeURIComponent(location.href.split('#')[0])},		
			dataType:'JSON',
			success: function(res)
			{
				//微信配置
				wx.config({
				    debug: false, 
				    appId: res.appId,
				    timestamp: res.timestamp, 
				    nonceStr: res.nonceStr, 
				    signature: res.signature,
				    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage','onMenuShareQQ','onMenuShareQZone','hideOptionMenu'] // 功能列表，我们要使用JS-SDK的什么功能
				});
			},
			error:function(){
				return false;
			}
		}); 

		// config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在 页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready 函数中。
		wx.ready(function(){
		    // 获取"分享到朋友圈"按钮点击状态及自定义分享内容接口
		    wx.onMenuShareTimeline({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
		    });

		    // 获取"分享给朋友"按钮点击状态及自定义分享内容接口
		    wx.onMenuShareAppMessage({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
		    });
			// 分享到QQ
			wx.onMenuShareQQ({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
			});	
			// 分享到QQ空间
			wx.onMenuShareQZone({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
			});

		   <?php if(CONTROLLER_NAME == 'User'): ?> 
				wx.hideOptionMenu();  // 用户中心 隐藏微信菜单
		   <?php endif; ?>	
		});
	}
});

function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}
</script>
<!-- 微信浏览器 调用微信 分享js-->
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="__PUBLIC__/js/global.js"></script>
<script type="text/javascript">
<?php if(ACTION_NAME == 'goodsInfo'): ?>
   var ShareLink = "http://<?php echo $_SERVER[HTTP_HOST]; ?>/index.php?m=Mobile&c=Goods&a=goodsInfo&id=<?php echo $goods[goods_id]; ?>"; //默认分享链接
   var ShareImgUrl = "http://<?php echo $_SERVER[HTTP_HOST]; ?><?php echo goods_thum_images($goods[goods_id],400,400); ?>"; // 分享图标
<?php else: ?>
   var ShareLink = "http://<?php echo $_SERVER[HTTP_HOST]; ?>/index.php?m=Mobile&c=Index&a=index"; //默认分享链接
   var ShareImgUrl = "http://<?php echo $_SERVER[HTTP_HOST]; ?><?php echo $tpshop_config['shop_info_store_logo']; ?>"; //分享图标
<?php endif; ?>

var is_distribut = getCookie('is_distribut'); // 是否分销代理
var user_id = getCookie('user_id'); // 当前用户id
//alert(is_distribut+'=='+user_id);
// 如果已经登录了, 并且是分销商
if(parseInt(is_distribut) == 1 && parseInt(user_id) > 0)
{									
	ShareLink = ShareLink + "&first_leader="+user_id;									
}

$(function() {
	if(isWeiXin() && parseInt(user_id)>0){
		$.ajax({
			type : "POST",
			url:"/index.php?m=Mobile&c=Index&a=ajaxGetWxConfig&t="+Math.random(),
			data:{'askUrl':encodeURIComponent(location.href.split('#')[0])},		
			dataType:'JSON',
			success: function(res)
			{
				//微信配置
				wx.config({
				    debug: false, 
				    appId: res.appId,
				    timestamp: res.timestamp, 
				    nonceStr: res.nonceStr, 
				    signature: res.signature,
				    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage','onMenuShareQQ','onMenuShareQZone','hideOptionMenu'] // 功能列表，我们要使用JS-SDK的什么功能
				});
			},
			error:function(){
				return false;
			}
		}); 

		// config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在 页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready 函数中。
		wx.ready(function(){
		    // 获取"分享到朋友圈"按钮点击状态及自定义分享内容接口
		    wx.onMenuShareTimeline({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
		    });

		    // 获取"分享给朋友"按钮点击状态及自定义分享内容接口
		    wx.onMenuShareAppMessage({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
		    });
			// 分享到QQ
			wx.onMenuShareQQ({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
			});	
			// 分享到QQ空间
			wx.onMenuShareQZone({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
			});

		   <?php if(CONTROLLER_NAME == 'User'): ?> 
				wx.hideOptionMenu();  // 用户中心 隐藏微信菜单
		   <?php endif; ?>	
		});
	}
});

function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}
</script>
<!-- 微信关注 lishibo 20190524-->

<!--微信关注提醒 start lishibo 20190524-->
<?php if(\think\Session::get('subscribe') == 0): endif; ?>
<!--<div class="guide">-->
<!--    <table class="guide_table">-->
<!--        <tr>-->
<!--            <td class="guide_td"><img src="<?php echo $wx_qr; ?>" style="width:1.3rem;"></td>-->
<!--            <td style="text-align: left;">-->
<!--                <div class="guide_font">欢迎进入<font style="color:#00FF00;weight:600;font-size:0.55rem;">社群新零售</font></div>-->
<!--                <div class="guide_font">关注公众号，享专属权益</div>-->
<!--            </td>-->
<!--            <td class="guide_font_right">-->
<!--                <button class="guide_btn"  onclick="follow_wx()" >点击关注</button>-->
<!--            </td>-->
<!--        </tr>-->
<!--    </table>-->
<!--</div>-->

<style type="text/css">
    .guide_table{
        width:100%;
    }
    .guide_td{
        padding-right:0.3rem;
        text-align: right;
    }
    .guide_font{
        color:#fff;
        font-size:0.45rem;
    }
    .guide_font_right{
        color:#fff;
        padding-top:0.3rem;
        padding-bottom:0.3rem;
        padding-right:0.8rem;
        vertical-align: center;
        text-align: right;
        float:right;
    }

    .guide_btn{
        color:#0000cc;background-color: #00FF00;weight:600;height:1rem;font-size:0.38rem;padding:0.06rem 0.4rem;
        border-radius: 0.5rem;border:1px solid #0bb20c;
    }

    .guide{width:100%;height:2rem;vertical-align:middle;text-align: center;border-radius: 0rem ;font-size:0.5rem;padding:0.2rem 0;background-color: #fff;position: fixed;top: 0rem;background-color:#000000;/* IE6和部分IE7内核的浏览器(如QQ浏览器)下颜色被覆盖 */
        background-color:rgba(0,0,0,0.6); /* IE6和部分IE7内核的浏览器(如QQ浏览器)会读懂，但解析为透明 */}
    #cover{display:none;position:absolute;left:0;top:0;z-index:18888;background-color:#000000;opacity:0.7;}
    #guide{display:none;position:absolute;top:0.1rem;z-index:19999;}
    #guide img{width: 70%;height: auto;display: block;margin: 0 auto;margin-top: 0.2rem;}
</style>
<script type="text/javascript">
    //关注微信公众号二维码
    function follow_wx()
    {
        layer.open({
            type : 1,
            title: '<span style="color:#90B83F">长按识别二维码关注</span>',
            content: '<div style=""><img src="<?php echo $wx_qr; ?>" width="100%"><div style="color:#90B83F;width:100%;height:2rem;text-align: center;">社群新零售</div></div>',
            style: ''
        });
    }

    $(function(){
        if(isWeiXin()){
            var subscribe = getCookie('subscribe'); // 是否已经关注了微信公众号
            if(subscribe == 0){
                $('.guide').show();//无论何时都隐藏
            }else{
                $('.guide').hide();
            }

        }else{
            $('.guide').hide();
        }
    })
</script>
<!--微信关注提醒 start lishibo 20190524-->
<!-- 微信关注 lishibo 20190524-->
<!-- 微信浏览器 调用微信 分享js  end-->
<!-- 微信浏览器 调用微信 分享js  end-->

<script type="text/javascript">
    function AutoScroll(obj){
        $(obj).find("ul:first").animate({
            marginTop:"-35px"
        },500,function(){
            $(this).css({marginTop:"0px"}).find("li:first").appendTo(this);
        });
    }
    $(document).ready(function(){
        setInterval('AutoScroll("#s1")',3000);
    });
</script>


<script type="text/javascript">
    //search在滚动条下滑一定高度后固定置顶
    var titleH = $(".index-video .tab").offset().top;
    $(window).scroll(function () {
        var scroH = $(this).scrollTop();
        if (scroH > titleH) {
            $(".tab").css({ "position": "fixed", "top": 0 ,"width":"7.5rem","background":"#EEF0F0"});
            $(".indexTop .nav").css({ "position": "static" });
        } else if (scroH <= titleH) {
            $(".tab").css({ "position": "static" });
            $(".indexTop .nav").css({ "position": "fixed" });
        }
    });


    $(function(){
        $(window).scroll(function(){
            //为页面添加页面滚动监听事件
            var wst =  $(window).scrollTop() //滚动条距离顶端值
            var i;
            for (i=1; i<5; i++){             //加循环
                if($("#a"+i).offset().top-60<=wst){ //判断滚动条位置
                    $('#nav li').removeClass("active"); //清除c类
                    $("#a"+i+i).addClass("active"); //给当前导航加c类
                }
            }
        })
        $('#nav li').click(function(){
            $('#nav li').removeClass("active");
            $(this).addClass("active");
        });
    })
</script>



</body>
</html>


