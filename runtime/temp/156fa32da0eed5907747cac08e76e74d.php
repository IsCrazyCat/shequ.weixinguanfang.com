<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:44:"./template/mobile/new2/user\withdrawals.html";i:1567395799;s:41:"./template/mobile/new2/public\header.html";i:1566471081;s:45:"./template/mobile/new2/public\header_nav.html";i:1533297512;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>申请提现--社群新零售</title>
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
            <a href="javascript:history.back(-1)"><img src="__STATIC__/images/return.png" alt="返回"></a>
        </div>
        <div class="ds-in-bl search center">
            <span>申请提现</span>
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
		<div class="loginsingup-input ma-to-20">
			<form action="?" method="post" onsubmit="return checkSubmit()">
				<div class="content30">
					<div class="lsu" style="display: none">
						<span>银行卡号：</span>
						<input type="text" name="account_bank" id="account_bank" value=""  placeholder="银行卡号" onKeyUp="this.value=this.value.replace(/[^\d]/g,'')">
					</div>
					<div class="lsu">
						<span>姓&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;名：</span>
						<input type="text" name="account_name" id="account_name" value=""  placeholder="姓名">
					</div>
					<div class="lsu" style="display: none">
						<span>开户银行：</span>
						<input type="text" name="bank_name" id="bank_name" value="" placeholder="如：工商银行济南分行">
					</div>

                    <div class="lsu" style="display: none">
					<span>微信账号：</span>
					<input type="text" name="account_weixin" id="account_weixin" value=""  placeholder="可输入收款微信账号（选填）" >
				</div>
					<div class="lsu" >
						<span>手&nbsp;&nbsp;机&nbsp;号：</span>
						<input type="text" name="tongzhishoujihao" id="tongzhishoujihao" value=""  placeholder="" >
					</div>
                    <div class="lsu">
                        <span>支付宝号：</span>
                        <input type="text" name="account_alipay" id="account_alipay" value=""  placeholder="可输入收款支付宝账号（必填）" >
                    </div>
					<div class="lsu">
						<span>提现金额：</span>
						<input onblur="jisuan()" type="text" name="money" id="money" value="" usermoney="<?php echo $user_money; ?>" placeholder="可提现金额：<?php echo $user_money; ?>元" onKeyUp="this.value=this.value.replace(/[^\d.]/g,'')">
					</div>
					<div>备注：*<span style="color: red">提现手续费为10%，提现金额为100整数倍。</span></div>
<!--                    <div class="lsu test" style="display: none">-->
<!--                        <span>验证码&nbsp;&nbsp;&nbsp;：</span>-->
<!--                        <input type="text" name="verify_code" id="verify_code" value="" placeholder="请输入验证码">-->
<!--                        <img  id="verify_code_img" src="<?php echo U('User/verify',array('type'=>'withdrawals')); ?>" onClick="verify()" style=""/>-->
<!--                    </div>-->
					<div class="lsu submit">
						<input type="submit" name="" id="" value="提交申请">
					</div>
				</div>
			</form>
		</div>

<script type="text/javascript" charset="utf-8">
	function jisuan(){
		var money = parseFloat($.trim($('#money').val()));
		var ti=money*0.90;
		var ts='提现：'+money+'元，实际到账为：'+ti+'元';
		alert(ts);
	}
    // 验证码切换
    function verify(){
        $('#verify_code_img').attr('src','/index.php?m=Mobile&c=User&a=verify&type=withdrawals&r='+Math.random());
    }

    /**
     * 提交表单
     * */
    function checkSubmit(){
        var bank_name = $.trim($('#bank_name').val());
        var account_bank = $.trim($('#account_bank').val());
        var account_name = $.trim($('#account_name').val());
        var money = parseFloat($.trim($('#money').val()));
        var usermoney = parseFloat(<?php echo $user_money; ?>);  //可用余额
        var verify_code = $.trim($('#verify_code').val());
        //验证码
        // if(verify_code == '' ){
        //     showErrorMsg('验证码不能空')
        //     return false;
        // }
		if(money%100===0){

		}else{
			showErrorMsg("提现金额为100的倍数");
			return false;
		}
        if(tongzhishoujihao == ''  || account_name=='' || money == ''){
            showErrorMsg("所有信息为必填")
            return false;
        }
        if(money > usermoney){
            showErrorMsg("提现金额大于您的可用余额")
            return false;
        }
    }
</script>
	</body>
</html>
