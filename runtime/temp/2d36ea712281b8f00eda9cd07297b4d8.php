<?php if (!defined('THINK_PATH')) exit(); /*a:6:{s:38:"./template/mobile/new2/user\index.html";i:1567071573;s:41:"./template/mobile/new2/public\header.html";i:1566471081;s:45:"./template/mobile/new2/public\footer_nav.html";i:1564468954;s:43:"./template/mobile/new2/public\wx_share.html";i:1558745778;s:49:"./template/mobile/new2/public\wx_share_index.html";i:1558682064;s:47:"./template/mobile/new2/public\wx_subscribe.html";i:1566263775;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>个人中心--社群新零售</title>
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
<body class="g4" >


<div class="myhearder" style="background-color: white;color:#000;display:none;">
    <div class="person">
        <!--<a href="">-->
        <div class="fl personicon" style="">
            <div class="personicon"  style="width:100%;height:100%;">
                <img style="width:100%;height:100%;" src="<?php echo (isset($user[head_pic]) && ($user[head_pic] !== '')?$user[head_pic]:"__STATIC__/images/user68.jpg"); ?>"/>
            </div>
        </div>


        <div class="fl lors" style="text-align: center;height:2.688rem;padding:0.8rem 0.3rem;margin:0;color:#000;">
            <div style="padding:0.1rem 0;">
                <span style="float:left"><?php echo $user['nickname']; ?></span>

                <span style="float:left; font-size:0.6rem;padding:0.1rem 0;"  >
                        〖
                            <?php if($user[level] == 1): ?>商城代理
                                <?php elseif($user[level] == 2): ?>区级代理
                                <?php elseif($user[level] == 3): ?>市级代理
                                <?php elseif($user[level] == 4): ?>省级代理
                                <?php else: ?>注册会员
                            <?php endif; ?>
                        〗
                    </span>

                <?php if($first_nickname != ''): ?>
                    <br />
                    <span style="font-size:20px">由(<?php echo $first_nickname; ?>)推荐</span>
                <?php endif; if($first_nickname == ''): ?>
                    <br />
                    <span style="font-size:20px">系统推荐</span>
                <?php endif; ?>



            </div>

        </div>
        <!--</a>-->
    </div>


</div>


<div id="section_container" style="display:block;background-color: #5f85f5">


            <div class="use_per_click"   >
                <a class="kehu_xinxi_ti_div content_click"  style="display:block;overflow: hidden; height:5rem;"
                   href="<?php echo U('Mobile/User/userinfo'); ?>">
                    <i class="kehu_personal"></i>
                    <span  class="kehu_xinxi_span"></span>
                </a>
            </div>


            <div style="overflow: hidden; height:5rem;">
            <div style="position: absolute;top:0;left:0;float:left;background-image: url(<?php echo (isset($user[head_pic]) && ($user[head_pic] !== '')?$user[head_pic]:'__STATIC__/images/user68.jpg'); ?>);background-repeat: no-repeat;background-size: 100% auto; opacity: 0.5;">
            </div>
                <div class="pic_div">
                    <!--图片打印模板-->
                    <img id="CUST_AVATAR" class="user_pic" src="<?php echo (isset($user[head_pic]) && ($user[head_pic] !== '')?$user[head_pic]:"__STATIC__/images/user68.jpg"); ?>"></img>
                </div>
                <div class="kehu_div">

                <style>
                    .user_info span samp{

                        font-size:0.56rem;
                    }

                </style>
                    <div class="user_info">

                        <div style="">
                        <!--客户信息打印-->
                        <span style="display: none" class="kehu_huiyuan_div"> <samp class="UserMember" tim="">会员号: <?php echo $user['user_id']; ?></samp></span>

                        <span class="kehu_qita_div VIPName"> <samp>会员昵称: <?php echo $user['nickname']; ?>&nbsp;

                            </samp>
                        </span>
                            <span class="kehu_huiyuan_div"> <samp class="UserMember" tim="">〖
                                  <?php if($user[level] == 1): ?>注册会员
                                        <?php elseif($user[level] == 2): ?>消费商
                                        <?php elseif($user[level] == 3): ?>代理商
                                        <?php elseif($user[level] == 4): ?>董事级
                                        <?php elseif($user[level] == 5): ?>运营中心
                                        <?php else: ?>注册会员
                                    <?php endif; ?>
                                〗</samp></span>
                        <span class="kehu_shu_div">
                            <samp id="REFEREE_name">推荐人:
                             <?php if($first_nickname != ''): ?>
                                     <span id='bamboo'><?php echo $first_nickname; ?></span>
                            <?php endif; if($first_nickname == ''): ?>
                                  <span id='bamboo'>系统推荐</span>
                            <?php endif; ?>
                            </samp>
                        </span>
                    </div>
                    </div>
                </div>
            </div>
    <!--    </div>-->
</div>







<div class="floor my p">
    <div class="content">



        <!--订单管理模块-s-->
        <div class="floor myorder ma-to-20 p" style="display:none;">
            <div class="content30">
                <div class="order">
                    <div class="fl">
                        <img src="__STATIC__/images/mlist.png"/>
                        <span>我的订单</span>
                    </div>
                    <div class="fr">
                        <a href="<?php echo U('Mobile/User/order_list'); ?>">
                            <span>全部订单</span>
                            <i class="Mright"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="floor"  style="display:none;">
            <ul>

                <li>
                    <a href="<?php echo U('/Mobile/User/order_list',array('type'=>'WAITSEND')); ?>">
                        <span><?php echo $user['waitSend']; ?></span>
                        <img src="__STATIC__/images/q1.png" alt="" />
                        <p>待发货</p>
                    </a>
                </li>

                <li>
                    <a href="<?php echo U('/Mobile/User/order_list',array('type'=>'WAITPAY')); ?>">
                        <span><?php echo $user['waitPay']; ?></span>
                        <img src="__STATIC__/images/q4.png" alt="" />
                        <p>待付款</p>
                    </a>
                </li>



                <li>
                    <a href="<?php echo U('/Mobile/User/wait_receive',array('type'=>'WAITRECEIVE')); ?>">
                        <span><?php echo $user['waitReceive']; ?></span>
                        <img src="__STATIC__/images/q2.png" alt="" />
                        <p>待收货</p>
                    </a>
                </li>
                <li>
                    <a href="<?php echo U('Mobile/User/comment',array('status'=>0)); ?>">
                        <span><?php echo $user['uncomment_count']; ?></span>
                        <img src="__STATIC__/images/q3.png" alt="" />
                        <p>待评价</p>
                    </a>
                </li>
                <!--<li>
                    <a href="<?php echo U('Mobile/User/return_goods_list',array('type'=>1)); ?>">
                        <span><?php echo $user['return_count']; ?></span>
                        <img src="__STATIC__/images/q4.png" alt="" />
                        <p>退款/退货</p>
                    </a>
                </li>-->
            </ul>
        </div>
        <!--订单管理模块-e-->


        <div class="mynav_link_list ma-to-8 p">
            <ul>
                <li >

                    <a href="#">
                        <i style="color: orange;font-size:0.76rem;height:1rem;"><?php echo (isset($user['chongzhi_yeji']) && ($user['chongzhi_yeji'] !== '')?$user['chongzhi_yeji']:"0"); ?></i>
                        <span>业绩</span>
                    </a>
                </li>
                <li >
                    <a href="#">
                        <i style="color: orange;font-size:0.76rem;height:1rem;"><?php echo (isset($user['pfj_money']) && ($user['pfj_money'] !== '')?$user['pfj_money']:"0"); ?></i>
                        <span>批发券</span>
                    </a>
                </li>

                <li>

                    <a href="<?php echo U('Mobile/User/withdrawals'); ?>">
                        <i style="color: orange;font-size:0.76rem;height:1rem;"><?php echo (isset($user['user_money']) && ($user['user_money'] !== '')?$user['user_money']:"0"); ?></i>
                        <span>u币</span>
                    </a>
                </li>
                <li style="display: none" >
                    <a href="<?php echo U('Mobile/User/withdrawals'); ?>">
                        <i style="color: orange;font-size:0.76rem;height:1rem;"><?php echo (isset($user['user_money']) && ($user['user_money'] !== '')?$user['user_money']:"0"); ?></i>
                        <span>利润钱包</span>
                    </a>
                </li>
                <li style="display: none" >
                    <a href="#">
                        <i style="color: orange;font-size:0.76rem;height:1rem;">0</i>
                        <span>股权通证</span>
                    </a>
                </li>




                <li style="display: none">
                    <a href="#">
                        <i style="color: orange;font-size:0.76rem;height:1rem;"><?php echo (isset($tui_con) && ($tui_con !== '')?$tui_con:"0"); ?></i>
                        <span>推荐数</span>
                    </a>
                </li>
                <li style="display: none">

                </li>

            </ul>
        </div>


        <div class="mynav_link_list ma-to-8 p">
            <ul>


                <li>
                    <a href="<?php echo U('Mobile/article/article'); ?>">
                        <i class="fx-f5-6"></i>
                        <span>公告信息</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo U('Mobile/user/userinfo'); ?>">
                        <i class="fx-f4-5"></i>
                        <span>修改资料</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo U('Mobile/user/password'); ?>">
                        <i class="fx-f1-z"></i>
                        <span>密码修改</span>
                    </a>
                </li>
<style>

    .fx-f5-6{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/6.png");
    }
    .fx-f1-z{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/4.png");
    }
    .fx-f4-5{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/5.png");
    }
    .fx-f4-q{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/13.png");
    }
    .fx-f2-7{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/7.png");
    }
    .fx-f7-8{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/8.png");
    }
    .fx-f6-9{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/9.png");
    }
    .fx-f8-10{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/10.png");
    }
    .fx-f1-11{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/11.png");
    }
    .fx-f3-12{
        background-position: 0 0;
        height:91.10933px;
        width: 74.088px;
        background-image: url("__PUBLIC__/images/12.png");
    }
</style>

                <li>
                    <a href="#">
                        <i class="fx-f6-9"></i>
                        <span>问题反馈</span>
                    </a>
                </li>

                <li >
                    <!--更改佣金明细查询 lishibo 2018/12/11 -->
                    <a href="<?php echo U('Mobile/Distribut/rebate_log_20181211'); ?>">
                        <i class="fx-f7-8"></i>
                        <span>奖金明细</span>
                    </a>
                </li>
                <li style="float:left">
                    <a href="<?php echo U('Distribut/lower_listq'); ?>">
                        <i class="fx-f2-7"></i>
                        <span>团队信息</span>
                    </a>
                </li>



                <li>
                    <a href="<?php echo U('Mobile/User/huzhuan'); ?>">
                        <i class="fx-f3-12"></i>
                        <span>资金互转</span>
                    </a>
                </li>
                <li >
                    <a href="<?php echo U('/Mobile/User/order_list',array('type'=>'WAITPAY')); ?>"><!-- add by lishibo -->
                        <i class="fx-f1-11"></i>
                        <span>商品订单</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo U('Mobile/User/withdrawals'); ?>">
                        <i class="fx-f8-10"></i>
                        <span>提现管理</span>
                    </a>
                </li>
                <li   style="float: left;display: none">
                    <a href="#">
                        <i class="fx-f4-q"></i>
                        <span>利润转通证</span>
                    </a>
                </li>


            </ul>
        </div>

        <!--资金管理-s-->
        <div class="floor myorder ma-to-20 p"  style="display:none;">
            <div class="content30">
                <div class="order">
                    <div class="fl">
                        <img src="__STATIC__/images/mwallet.png"/>
                        <span>我的钱包</span>
                    </div>
                    <div class="fr">
                        <!--<a href="bankrollmm.html">-->
                        <a href="<?php echo U('Mobile/User/account'); ?>">
                            <span>资金管理</span>
                            <i class="Mright"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="floor w3"  style="display:none;">
            <ul>
                <li>
                    <a href="<?php echo U('Mobile/User/account'); ?>">
                        <h2><?php echo $user['user_money']; ?></h2>
                        <p>可用余额</p>
                    </a>
                </li>
                <!-- <li>
                     <a href="<?php echo U('Mobile/User/coupon'); ?>">
                         <h2><?php echo $user['coupon_count']; ?></h2>
                         <p>优惠券</p>
                     </a>
                 </li>-->
                <li>
                    <a href="<?php echo U('Mobile/User/points'); ?>">
                        <h2><?php echo $user['pay_points']; ?></h2>
                        <p>积分</p>
                    </a>
                </li>
            </ul>
        </div>
        <!--资金管理-e-->

        <div class="floor list7 ma-to-20">



            <div class="myorder p">
                <div class="content30">
                    <a href="<?php echo U('Mobile/User/return_goods_list'); ?>">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/zz8.png"/>
                                <span>售后服务</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>


            <div class="myorder p" style="display:none;">
                <div class="content30">
                    <a href="<?php echo U('Distribut/myrankings',array('order'=>'underling_number')); ?>">
                        <div class="order">
                            <div class="fl">
                               <img src="__STATIC__/images/wcup.png"/>
                                <span>分销排行</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- <div class="myorder p">
                 <div class="content30">
                     <a href="<?php echo U('Mobile/Activity/coupon_list'); ?>">
                         <div class="order">
                             <div class="fl">
                                 <img src="__STATIC__/images/w7.png"/>
                                 <span>领券中心</span>
                             </div>
                             <div class="fr">
                                 <i class="Mright"></i>
                             </div>
                         </div>
                     </a>
                 </div>
             </div>-->
            <div class="myorder p"  style="display:none;">
                <div class="content30">
                    <a href="<?php echo U('Mobile/User/visit_log'); ?>">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/w3.png"/>
                                <span>浏览历史</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="myorder p">
                <div class="content30">
                    <a href="<?php echo U('Mobile/User/address_list'); ?>">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/dw.png"/>
                                <span>收货地址</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="myorder p" style="">
                <div class="content30">
                    <a href="<?php echo U('Mobile/User/comment',array('status'=>1)); ?>">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/w2.png"/>
                                <span>我的评论</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <!--
            <div class="myorder p">
                <div class="content30">
                    <a href="">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/w3.png"/>
                                <span>我的预售</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="myorder p">
                <div class="content30">
                    <a href="">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/w4.png"/>
                                <span>我的拼团</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
           <div class="myorder p">
                <div class="content30">
                    <a href="<?php echo U('Mobile/Goods/integralMall'); ?>">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/w5.png"/>
                                <span>积分商城</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="myorder p">
                <div class="content30">
                    <a href="">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/w6.png"/>
                                <span>帮助中心</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="myorder p">
                <div class="content30">
                    <a href="">
                        <div class="order">
                            <div class="fl">
                                <img src="__STATIC__/images/w7.png"/>
                                <span>意见反馈</span>
                            </div>
                            <div class="fr">
                                <i class="Mright"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            -->
            <link rel="stylesheet" type="text/css" href="__STATIC__/distribut/css/main.css"/>
            <?php if(is_weixin() != 1): ?>
                <div class="myorder p">
                    <div class="w670 ma-to-50" style="background: #84BD00;">
                        <a class="submit_set" style="background: #84BD00;font-size:1rem;" href="<?php echo U('User/logout'); ?>">安全退出</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
<script src="__STATIC__/js/style.js" type="text/javascript" charset="utf-8"></script>
</body>
</html>
