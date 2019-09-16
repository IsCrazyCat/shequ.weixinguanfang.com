<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:48:"./template/mobile/new2/user\ajax_order_list.html";i:1565596928;}*/ ?>
<?php if(is_array($lists) || $lists instanceof \think\Collection || $lists instanceof \think\Paginator): $i = 0; $__LIST__ = $lists;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?>
    <div class="mypackeg ma-to-20 getmore">
        <div class="packeg p">
            <div class="maleri30">
                <div class="fl">
                    <h1><span></span><span class="bgnum"></span></h1>
                    <p class="bgnum"><span>订单编号:</span><span><?php echo $list['order_sn']; ?></span></p>
                </div>
                <style>
                    .link_button {
                        -webkit-border-radius: 4px;
                        -moz-border-radius: 4px;
                        border-radius: 4px;
                        border: solid 1px #20538D;
                        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.4);
                        -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
                        -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
                        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
                        background: #4479BA;
                        color: #FFF;
                        padding: 8px 12px;
                        text-decoration: none;
                    }
                </style>
                <div class="fr">
                    <?php if($list['order_prom_type'] == 9): ?>
                        <span>
<?php if($list['end'] == 2): ?>
<a href="<?php echo U("Mobile/user/zhuanmai",array('order_id'=>$list['order_id'])); ?>"  class="link_button">转&nbsp;卖</a>&nbsp;&nbsp;&nbsp;<a href="<?php echo U("Mobile/user/fahuo",array('order_id'=>$list['order_id'])); ?>" class="link_button">发&nbsp;货</a>
    <?php else: ?>
      <span style="font-size:18px;"><span class="num" id="dhms">
        <strong id="begin_time<?php echo $list['order_id']; ?>" style="display:none;"><?php echo $list['endTime']; ?></strong>
                    距离结束：
               <strong id="dd<?php echo $list['order_id']; ?>"></strong>天
               <strong id="hh<?php echo $list['order_id']; ?>"></strong>小时
               <strong id="mm<?php echo $list['order_id']; ?>"></strong>分
               <strong id="ss<?php echo $list['order_id']; ?>" style="color:red;"></strong>秒
    </span></span>
<?php endif; ?>


                        </span>
                        <?php else: ?>
                            <span><?php echo $list['order_status_desc']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="shop-mfive p">
            <div class="maleri30">
                    <?php if(is_array($list['goods_list']) || $list['goods_list'] instanceof \think\Collection || $list['goods_list'] instanceof \think\Paginator): if( count($list['goods_list'])==0 ) : echo "" ;else: foreach($list['goods_list'] as $key=>$good): ?>
                        <div class="sc_list se_sclist paycloseto">
                            <a <?php if($list['receive_btn'] == 1): ?>href="<?php echo U('/Mobile/User/order_detail',array('id'=>$list['order_id'],'waitreceive'=>1)); ?>" <?php else: ?> href="<?php echo U('/Mobile/User/order_detail',array('id'=>$list['order_id'])); ?>"<?php endif; ?>>
                            <div class="shopimg fl">
                                <img src="<?php echo goods_thum_images($good[goods_id],200,200); ?>">
                            </div>
                            <div class="deleshow fr">
                                <div class="deletes">
                                    <span class="similar-product-text"><?php echo getSubstr($good[goods_name],0,20); ?></span>
                                </div>
                                <div class="prices  wiconfine">
                                    <p class="sc_pri"><span>￥</span><span><?php echo $good[member_goods_price]; ?></span></p>
                                </div>
                                <div class="qxatten  wiconfine">
                                    <p class="weight"><span>数量</span>&nbsp;<span><?php echo $good[goods_num]; ?></span></p>
                                </div>
                                <div class="buttondde">
                                   <?php if($list[return_btn] == 1): ?>
                                        <a href="<?php echo U('Mobile/User/return_goods',array('rec_id'=>$good['rec_id'])); ?>">申请售后</a>
                                    <?php endif; if($good[is_send] > 1): ?>
                                        <a class="applyafts">已申请售后</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            </a>
                        </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        </div>
        <div class="shop-rebuy-price p">
            <div class="maleri30">
                <span class="price-alln">
                    <!--<span class="red">￥<?php echo $list['order_amount']; ?></span><span class="threel">共<?php echo count($list['goods_list']); ?>件</span>-->
                    <span class="red">￥<?php echo $list['order_amount']; ?></span><span class="threel" id="goodsnum">共<?php echo $list['count_goods_num']; ?>件</span>
                </span>
                <?php if($list['pay_btn'] == 1): ?>
                    <a class="shop-rebuy paysoon" href="<?php echo U('Mobile/Cart/cart4',array('order_id'=>$list['order_id'])); ?>">立即付款</a>
                <?php endif; if($list['cancel_btn'] == 1): ?>
                    <a class="shop-rebuy " onClick="cancel_order(<?php echo $list['order_id']; ?>)">取消订单</a>
                <?php endif; if($list['receive_btn'] == 1): ?>
                    <a class="shop-rebuy paysoon" onclick="orderConfirm(<?php echo $list['order_id']; ?>)">确认收货</a>
                <?php endif; if($list['comment_btn'] == 1): ?>
                    <a class="shop-rebuy" href="<?php echo U('/Mobile/User/comment'); ?>">评价</a>
                <?php endif; if($list['shipping_btn'] == 1): ?>
                    <a class="shop-rebuy" class="shop-rebuy" href="<?php echo U('Mobile/User/express',array('order_id'=>$list['order_id'])); ?>">查看物流</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; endif; else: echo "" ;endif; ?>
<strong id="boss" style="display:none;"><?php echo $arr_id; ?></strong>
<script type="text/javascript">
    //定义方法
    function GetRTime(){
        //获取当前时间
        var NowTime = new Date();

        var Boss = $("#boss").text();
        var arr=Boss.split(",");
        //二维数组遍历
        for(var i=0;i<arr.length;i++){
                //获取终止时间，在HTML中，通过id获取
                begin_time="#begin_time"+arr[i];
                var EndTime = $(begin_time).text();
                //计算终止时间与当前时间的时间差
                //注意js时间戳与PHP时间戳的不同
                var nMS = EndTime - Math.round(new Date().getTime() / 1000 - 28800);
                //定义参数 获得天数
                var nD = Math.floor(nMS / (60 * 60 * 24));
                //定义参数 获得小时
                var nH = Math.floor(nMS / (60 * 60)) % 24;
                //定义参数 获得分钟
                var nM = Math.floor(nMS / (60)) % 60;
                //定义参数 获得秒钟
                var nS = Math.floor(nMS) % 60;
                //显示天数
            dd="#dd"+arr[i];
                $(dd).text(nD);
                //显示小时
            hh="#hh"+arr[i];
                $(hh).text(nH);
                //显示分钟
            mm="#mm"+arr[i];
                $(mm).text(nM);
                //显示秒钟
            ss="#ss"+arr[i];
                $(ss).text(nS);

        }

    }

    $(document).ready(function () {
        //定义参数 显示出GetRTime()方法 1000毫秒以后启动  ，每1秒循环执行
        var timer_rt = window.setInterval("GetRTime()", 1000);
    });
</script>

