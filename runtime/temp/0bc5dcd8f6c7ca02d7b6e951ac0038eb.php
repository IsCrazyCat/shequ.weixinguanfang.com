<?php if (!defined('THINK_PATH')) exit(); /*a:8:{s:38:"./template/mobile/new2/user\login.html";i:1566470876;s:41:"./template/mobile/new2/public\header.html";i:1566471081;s:45:"./template/mobile/new2/public\header_nav.html";i:1533297512;s:41:"./template/mobile/new2/public\footer.html";i:1566263775;s:45:"./template/mobile/new2/public\footer_nav.html";i:1564468954;s:43:"./template/mobile/new2/public\wx_share.html";i:1558745778;s:49:"./template/mobile/new2/public\wx_share_index.html";i:1558682064;s:47:"./template/mobile/new2/public\wx_subscribe.html";i:1566263775;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>登录--社群新零售</title>
    <link rel="stylesheet" href="__STATIC__/css/style.css">
    <link rel="stylesheet" type="text/css" href="__STATIC__/css/iconfont.css"/>

    <link rel="stylesheet" type="text/css" href="__STATIC__/sanyu/MyCss/style.css"/><!--头部脚本-->
    <link rel="stylesheet" type="text/css" href="__STATIC__/sanyu/MyCss/geren_zhongxin.css"/><!--body脚本-->

    <script src="__STATIC__/js/jquery-3.1.1.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="__STATIC__/js/style.js" type="text/javascript" charset="utf-8"></script>
    <script src="__STATIC__/js/mobile-util.js" type="text/javascript" charset="utf-8"></script>
    <script src="__PUBLIC__/js/global.js"></script>
    <script src="__STATIC__/js/layer.js"  type="text/javascript" ></script>
    <script src="__STATIC__/js/swipeSlide.min.js" type="text/javascript" charset="utf-8"></script>
</head>
<body class="" >

<div class="classreturn loginsignup ">
    <div class="content">
        <div class="ds-in-bl return">
            <a href="javascript:history.back(-1);"><img src="__STATIC__/images/return.png" alt="返回"></a>
        </div>
        <div class="ds-in-bl search center">
            <span>登录</span>
        </div>
        <div class="ds-in-bl menu">
            <a href="javascript:void(0);"><img src="__STATIC__/images/class1.png" alt="菜单"></a>
        </div>
    </div>
</div>
<div class="flool tpnavf">
    <div class="footer">
        <ul>
            <li  style="width:33.3%">
                <a class="yello" href="<?php echo U('Index/index'); ?>">
                    <div class="icon">
                        <i class="icon-shouye iconfont"></i>
                        <p>首页</p>
                    </div>
                </a>
            </li>
            <!--<li>
                <a href="<?php echo U('Goods/categoryList'); ?>">
                    <div class="icon">
                        <i class="icon-fenlei iconfont"></i>
                        <p>分类</p>
                    </div>
                </a>
            </li>-->
            <li  style="width:33.3%">
                <!--<a href="shopcar.html">-->
                <a href="<?php echo U('Cart/cart'); ?>">
                    <div class="icon">
                        <i class="icon-gouwuche iconfont"></i>
                        <p>购物车</p>
                    </div>
                </a>
            </li>
            <li style="width:33.3%">
                <a href="<?php echo U('User/index'); ?>">
                    <div class="icon">
                        <i class="icon-wode iconfont"></i>
                        <p>我的</p>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="flool loginsignup2" style="background-color:#FFFFFF">
<!--LOGO-->
    <img src="__STATIC__/dki/images/user68.jpg" alt="LOGO"/>
</div>
<div class="loginsingup-input" style="background-color:#FFFFFF;">
<!--登录表单-s-->
    <form  id="loginform" method="post" action="<?php echo U('User/Index'); ?>"  >
        <input type="hidden" name="referurl" id="referurl" value="<?php echo $referurl; ?>">
        <div class="content30">
            <div class="lsu">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="25%;" align="center"><span>手&nbsp;机</span></td>
    <td><input type="text" name="username" id="username" value=""  placeholder="请输入手机号"/></td>
  </tr>
</table>
            </div>
            <div class="lsu">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="25%;" align="center"><span >密&nbsp;码</span></td>
    <td><input type="password" name="password" id="password" value="" placeholder="请输入密码"/></td>
  </tr>
</table>			
			
                
                
                <!--<i></i>-->
            </div>
            <?php if(!(empty($first_login) || (($first_login instanceof \think\Collection || $first_login instanceof \think\Paginator ) && $first_login->isEmpty()))): ?>
            <div class="lsu test" style="display:none;">
                <span>请输入验证码</span>
                <input type="text" name="verify_code" id="verify_code" value="" placeholder="请输入验证码"/>
                <img  id="verify_code_img" src="<?php echo U('Mobile/User/verify'); ?>" onClick="verify()"/>
            </div>
            <?php endif; ?>
            <div class="lsu submit">
                <input type="button"  value="提交"  onclick="submitverify()" class="btn_big1"  />
            </div>
            <div class="radio">
            </div>
            <div class="signup-find p">
                <div class="note fl">
                    <img src="__STATIC__/images/not.png"/>
                    <a href="<?php echo U('User/reg'); ?>"><span>快速注册</span></a>
                </div>
                <div class="note fr">
                    <img src="__STATIC__/images/ru.png"/>
                    <a href="<?php echo U('User/forget_pwd'); ?>"><span>找回密码</span>
                </div>
            </div>
        </div><br />


    </form>
<!--登录表单-e-->
</div>

<!--第三方登陆-s-->
<div class="thirdlogin" style="display:none;background-color:#FFFFFF;">
        <h4>第三方登陆</h4>
        <ul>
<?php
                                   
                                $md5_key = md5("select * from __PREFIX__plugin where type='login' AND status = 1");
                                $result_name = $sql_result_v = S("sql_".$md5_key);
                                if(empty($sql_result_v))
                                {                            
                                    $result_name = $sql_result_v = \think\Db::query("select * from __PREFIX__plugin where type='login' AND status = 1"); 
                                    S("sql_".$md5_key,$sql_result_v,1);
                                }    
                              foreach($sql_result_v as $k=>$v): if($v['code'] == 'weixin' AND is_weixin() != 1): ?>
        <li>
            <a class="ta-weixin" href="<?php echo U('LoginApi/login',array('oauth'=>'weixin')); ?>" target="_blank" title="weixin">
                <div class="icon">
                    <img src="__STATIC__/images/wechat.png"/>
                    <p>微信登陆</p>
                </div>
            </a>
        </li>
    <?php endif; if($v['code'] == 'alipay' AND is_alipay() != 1): ?>
        <li>
            <a href="">
                <div class="icon">
                    <img src="__STATIC__/images/alpay.png"/>
                    <p>支付宝</p>
                </div>
            </a>
        </li>
    <?php endif; if($v['code'] == 'qq' AND is_qq() != 1): ?>
        <li>
            <a class="ta-qq" href="<?php echo U('LoginApi/login',array('oauth'=>'qq')); ?>" target="_blank" title="QQ">
                <div class="icon">
                    <img src="__STATIC__/images/qq.png"/>
                    <p>qq登陆</p>
                </div>
            </a>
        </li>
    <?php endif; endforeach; ?>
        </ul>		
</div>
<!--第三方登陆-e-->

<script type="text/javascript">
    function verify(){
        $('#verify_code_img').attr('src','/index.php?m=Mobile&c=User&a=verify&r='+Math.random());
    }

    //复选框状态
    function remember(obj){
         var che= $(obj).attr("class");
        if(che == 'che check_t'){
            $("#autologin").prop('checked',false);
        }else{
            $("#autologin").prop('checked',true);
        }
    }
    function submitverify()
    {
        var username = $.trim($('#username').val());
        var password = $.trim($('#password').val());
        var remember = $('#remember').val();
        var referurl = $('#referurl').val();
        var verify_code = $.trim($('#verify_code').val());
        if(username == ''){
            showErrorMsg('用户名不能为空!');
            return false;
        }
        if(!checkMobile(username) && !checkEmail(username)){
            showErrorMsg('账号格式不匹配!');
            return false;
        }
        if(password == ''){
            showErrorMsg('密码不能为空!');
            return false;
        }
        var codeExist = $('#verify_code').length;
        if (codeExist && verify_code == ''){
            //showErrorMsg('验证码不能为空!');
            //return false;
        }

        var data = {username:username,password:password,referurl:referurl};
//        if (codeExist) {
//            data.verify_code = verify_code;
//        }
        $.ajax({
            type : 'post',
            url : '/index.php?m=Mobile&c=User&a=do_login&t='+Math.random(),
            data : data,
            dataType : 'json',
            success : function(res){
                if(res.status == 1){
					
                    var url = res.url.toLowerCase();
                    if (url.indexOf('user') !==  false && url.indexOf('login') !== false || url == '') {
                        window.location.href = '/index.php/mobile/Index';
                    }
                    //window.location.href = res.url;
                    window.location.href = '/mobile/User/Index';
                }else{
                    showErrorMsg(res.msg);
                    if (codeExist) {
                        verify();
                    } else {
                        location.reload();
                    }
                }
            },
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                showErrorMsg('网络失败，请刷新页面后重试');
            }
        })
    }
        //切换密码框的状态
        $(function(){
            $('.loginsingup-input .lsu i').click(function(){
                $(this).toggleClass('eye');
                if ($(this).hasClass('eye')) {
                    $(this).siblings('input').attr('type','text')
                } else{
                    $(this).siblings('input').attr('type','password')
                }
            });
        })
    </script>
    <!--底部-start--> 
<footer>
<!--    <div class="flool1">
        <ul>
            <?php if(!empty($_COOKIE['user_id'])): ?>
                <li><a href="<?php echo U('Mobile/User/index'); ?>"><?php echo getSubstr(urldecode($_COOKIE['uname']),0,3);?></a></li>
                <li><a href="<?php echo U('Mobile/User/logout'); ?>">退出</a></li>
            <?php else: ?>
                <li><a href="<?php echo U('Mobile/User/login'); ?>">登录</a></li>
                <li><a href="<?php echo U('Mobile/User/reg'); ?>">注册</a></li>
            <?php endif; ?>
            &lt;!&ndash;<li><a href="">反馈</a></li>
&ndash;&gt;
            <li class="comebackTop">回到顶部</li>
        </ul>
    </div>
    
    <div class="flool3">
        <p>Copyright © 2018 社群新零售</p>
    </div>-->
</footer> 
<!--底部-end--> 

<!--底部导航-start--> 
<div class="foohi">


    <footer class="footer_tou "  style="height:2.13333rem;position: fixed;padding-top:0.08rem;padding-bottom:0.08rem;">

                  <div id="i_shouye" class="footer_div div" style="text-align: center;">
                      <a href="<?php echo U('Index/index'); ?>">
                      <span id="shouye_s" style="height:2rem;width:2rem;"
                      <?php if(CONTROLLER_NAME == 'Index'): ?>class="shouye_s span" <?php endif; if(CONTROLLER_NAME != 'Index'): ?>class="shouye span" <?php endif; ?> >&nbsp;</span>
                      </a>
                  </div>
        <div id="i_erweima" class="footer_div div" style="text-align: center;">
            <a href="<?php echo U('Distribut/qr_code', ['user_id'=>$user_id]); ?>">
                            <span id="erweima_s"style="height:2rem;width:2rem;"
                                  class="erweima span"
                            >&nbsp;</span>
            </a>
        </div>
                    <div id="i_che" class="footer_div div">
                        <a href="<?php echo U('Cart/cart'); ?>">
                            <span id="che_s" style="height:2rem;width:2rem;"
                            <?php if(CONTROLLER_NAME == 'Cart'): ?>class="che_s span" <?php endif; if(CONTROLLER_NAME != 'Cart'): ?>class="che span" <?php endif; ?> >&nbsp;</span>
                        </a>
                    </div>

                    <div id="i_geren" class="footer_div div">
                        <a href="<?php echo U('User/index'); ?>">
                            <span id="geren_s" style="height:2rem;width:2rem;"
                            <?php if(CONTROLLER_NAME == 'User'): ?>class="gerenzhongxin_s span" <?php endif; if(CONTROLLER_NAME != 'User'): ?>class="gerenzhongxin span" <?php endif; ?> >&nbsp;</span>
                        </a>
                    </div>

        </footer>
</div>

<!--底部菜单样式切换 lishibo 2019/02/19-->
<script type="text/javascript">
$(document).ready(function(){

    $(".footer_tou div[id^='i_']").each(function (index, el) {
        $("#" + el.id).on("click", function () {
            add_remove(el.id);
        });
    });

    function add_remove(id) {

        $("#shouye_s").addClass("shouye").removeClass("shouye_s");
        $("#che_s").addClass("che").removeClass("che_s");
        $("#geren_s").addClass("gerenzhongxin").removeClass("gerenzhongxin_s");
        //$("#erweima_s").addClass("erweima").removeClass("erweima_s");

        $(".div").addClass("footer_div").removeClass("footer_div_s");
        $("#" + id).addClass("footer_div_s").removeClass("footer_div");
        if (id == "i_shouye") {
            $("#shouye_s").addClass("shouye_s").removeClass("shouye");
        }
        if (id == "i_che") {
            $("#che_s").addClass("che_s").removeClass("che");
        }
        if (id == "i_geren") {
            $("#geren_s").addClass("gerenzhongxin_s").removeClass("gerenzhongxin");
        }
        if (id == "i_erweima") {
            $("#erweima_s").addClass("erweima_s").removeClass("erweima");
        }
    }

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
});
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
<!--底部导航-end--> 
</body>
</html>
