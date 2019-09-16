<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:43:"./template/mobile/new2/user\order_list.html";i:1566034686;s:41:"./template/mobile/new2/public\header.html";i:1566471081;s:45:"./template/mobile/new2/public\header_nav.html";i:1533297512;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>我的订单--社群新零售</title>
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

<div class="classreturn loginsignup ">
    <div class="content">
        <div class="ds-in-bl return">
            <a href="<?php echo U('/Mobile/User/index'); ?>"><img src="__STATIC__/images/return.png" alt="返回"></a>
        </div>
        <div class="ds-in-bl search center">
            <span>我的订单</span>
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
<script>
   $(function(){
       pushHistory();
       window.addEventListener("popstate", function(e) {
           window.location.href="<?php echo U('Mobile/User/index'); ?>";
       }, false);

       function pushHistory() {
           var state = {
               title: "title",
               url: "#"
           };
           window.history.pushState(state, state.title, '#');
       }
   });
</script>

<div class="tit-flash-sale p mytit_flash" onpageshow() >
    <div class="maleri30">
        <ul class="">
<!--            <li <?php if(\think\Request::instance()->param('type') == ''): ?>class="red"<?php endif; ?>>-->
<!--                <a href="<?php echo U('/Mobile/User/order_list'); ?>" class="tab_head">全部订单</a>-->
<!--            </li>-->
            <li id="WAITPAY" <?php if(\think\Request::instance()->param('type') == 'WAITPAY'): ?>class="red"<?php endif; ?>">
                <a href="<?php echo U('/Mobile/User/order_list',array('type'=>'WAITPAY')); ?>" class="tab_head" >未付款</a>
            </li>
            <li id="WAITSEND" <?php if(\think\Request::instance()->param('type') == 'WAITSEND'): ?>class="red"<?php endif; ?>>
                <a href="<?php echo U('/Mobile/User/order_list',array('type'=>'WAITSEND')); ?>"  class="tab_head">未发货</a>
            </li>
            <!--<li id="WAITRECEIVE"><a href="<?php echo U('/Mobile/User/order_list',array('type'=>'WAITRECEIVE')); ?>"  class="tab_head <?php if(\think\Request::instance()->param('type') == 'WAITRECEIVE'): ?>on<?php endif; ?>">待收货</a></li>-->
            <li id="WAITCCOMMENT"  <?php if(\think\Request::instance()->param('type') == 'WAITCCOMMENT'): ?>class="red"<?php endif; ?>>
                <a href="<?php echo U('/Mobile/User/order_list',array('type'=>'WAITCCOMMENT')); ?>" class="tab_head">已完成</a>
            </li>
            <li id="DAISHOU"  <?php if(\think\Request::instance()->param('type') == 'DAISHOU'): ?>class="red"<?php endif; ?>>
            <a href="<?php echo U('Mobile/User/daishouqu',array('type'=>'DAISHOU')); ?>" class="tab_head">代售</a>
            </li>
        </ul>
    </div>
</div>

    <!--订单列表-s-->
    <div class="ajax_return">
        <?php if(count($lists) == 0): ?>
            <!--没有内容时-s--->
            <div class="comment_con p">
                <div class="none">
                    <img src="__STATIC__/images/none2.png">
                    <br><br>
                    抱歉未查到数据！
                    <div class="paiton">
                        <div class="maleri30">
                            <a class="soon" href="/"><span>去逛逛</span></a>
                        </div>
                    </div>
                </div>
            </div>
            <!--没有内容时-e--->
        <?php endif; ?>
    </div>
    <!--订单列表-e-->
<script type="text/javascript" src="__STATIC__/js/sourch_submit.js"></script>
<script type="text/javascript">
    /**
     * 加载订单*/
    ajax_sourch_submit();

    /**
     * 取消订单
     */
    function cancel_order(id){
        if(!confirm("确定取消订单?"))
            return false;
        $.ajax({
            type: 'GET',
            dataType:'JSON',
            url:"/index.php?m=Mobile&c=User&a=cancel_order&id="+id,
            success:function(data){
                if(data.code == 1){
                    layer.open({content:data.msg,time:2});
                    location.href = "/index.php?m=Mobile&c=User&a=order_list";
                }else{
                    layer.open({content:data.msg,time:2});
                    location.href = "/index.php?m=Mobile&c=User&a=order_list";
                    return false;
                }
            },
            error:function(){
                layer.open({content:'网络失败，请刷新页面后重试',time:3});
            },
        });
    }

    /**
     * 确定收货
     */
    function orderConfirm(id){
        if(!confirm("确定收到该订单商品吗?"))
            return false;
        location.href = "/index.php?m=Mobile&c=User&a=order_confirm&id="+id;
    }

    var  page = 1;
    /**
     *加载更多
     */
    function ajax_sourch_submit()
    {
        page += 1;
        $.ajax({
            type : "GET",
            url:"/index.php?m=Mobile&c=User&a=order_list&type=<?php echo \think\Request::instance()->param('type'); ?>&is_ajax=1&p="+page,//+tab,
//			url:"<?php echo U('Mobile/User/order_list',array('type'=>$_GET['type']),''); ?>/is_ajax/1/p/"+page,//+tab,
            //data : $('#filter_form').serialize(),
            success: function(data)
            {
                if(data == '')
                    $('#getmore').hide();
                else
                {
                    $(".ajax_return").append(data);
                    $(".m_loading").hide();
                }
            }
        });
    }
</script>



</body>
</html>
