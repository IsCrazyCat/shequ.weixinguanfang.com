<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 聊城博商网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.boshang3710.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * $Author: 博商网络 2015-08-10 $
 */ 
namespace app\mobile\controller;
use app\admin\model\GroupBuy;
use app\home\logic\CartLogic;
use app\home\logic\OrderLogic;
use think\Db;
use app\home\logic\GoodsLogic;
class Cart extends MobileBase {
    
    public $cartLogic; // 购物车逻辑操作类    
    public $user_id = 0;
    public $user = array();        
    /**
     * 析构流函数
     */
    public function  __construct() {
        parent::__construct();
        $this->cartLogic = new \app\home\logic\CartLogic();
        if (session('?user')) {
            $user = session('user');
            $user = M('users')->where("user_id", $user['user_id'])->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user', $user); //存储用户信息
            // 给用户计算会员价 登录前后不一样

            /*************START!!计算当前等级折扣 by lishibo 2019/02/12************/
            $level_discount = $user['level'];
            if($level_discount>1){
                $level_info = D('user_level')->where('level_id='.$level_discount)->find();
                $discount_ = $level_info['discount']/100;
            }else{
                $discount_ =1;
            }
            $user['discount']= $discount_;
            M('users')->where('user_id',$this->user_id )->update(['discount' => $discount_]);
            if ($user) {
                $user['discount'] = (empty($user['discount'])) ? 1 : $user['discount'];
                if ($user['discount'] != 1) {
                    $c = Db::name('cart')
                        ->where(['user_id' => $user['user_id'], 'prom_type' => 0])
                        ->where('member_goods_price = goods_price')
                        ->count();
                    $c && Db::name('cart')
                        ->where(['user_id' => $user['user_id'], 'prom_type' => 0])
                        ->update(['member_goods_price' => ['exp', 'goods_price*' . $user['discount']]]);
                }
            }
        }
    }
    
    public function cart(){
        //获取热卖商品
        $hot_goods = M('Goods')->where('is_hot=1 and is_on_sale=1')->limit(20)->cache(true,TPSHOP_CACHE_TIME)->select();
        $this->assign('hot_goods',$hot_goods);
        return $this->fetch('cart');
    }

    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart()
    {
        $goods_id = I("goods_id/d"); // 商品id
        $goods_num = I("goods_num/d");// 商品数量
        $goods_spec = I("goods_spec/a",array()); // 商品规格
        if(empty($goods_id)){
            $this->ajaxReturn(['status'=>0,'msg'=>'请选择要购买的商品','result'=>'']);
        }
        if(empty($goods_num)){
            $this->ajaxReturn(['status'=>0,'msg'=>'购买商品数量不能为0','result'=>'']);
        }
        $spec_key = array_values($goods_spec);
        if($spec_key){
            sort($spec_key);
            $goods_spec_key = implode('_', $spec_key);
        }else{
            $goods_spec_key = '';
        }
        $this->cartLogic->setGoodsModel($goods_id);
        $this->cartLogic->setUserId($this->user_id);
        $result = $this->cartLogic->addGoodsToCart($goods_num, $goods_spec_key); // 将商品加入购物车
//        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车
        exit(json_encode($result));
    }

    /**
     * 购物车第二步确定页面  去结算
     */
    public function cart2()
    {
        $address_id = I('address_id/d');
        $cartLogic = new CartLogic();
        $cid = I('cid/d');
        if($this->user_id == 0){
            $this->error('请先登陆',U('Mobile/User/login'));
        }
        if($address_id){
            $address = M('user_address')->where("address_id", $address_id)->find();
        } else {
            $address = M('user_address')->where(['user_id'=>$this->user_id,'is_default'=>1])->find();
        }
        if(empty($address)){
        	header("Location: ".U('Mobile/User/add_address',array('source'=>'cart2')));
            exit;
        }else{
        	$this->assign('address',$address);
        }
        $cartLogic->setUserId($this->user_id);
        if($cartLogic->getUserCartOrderCount() == 0){
            $this->error ('你的购物车没有选中商品','Cart/cart');
        }
        $result =$cartLogic->getUserCartList(1); // 获取购物车商品

        // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的
        $couponWhere = [
            'c2.uid' => $this->user_id,
            'c1.use_end_time' => ['gt', time()],
            'c1.use_start_time' => ['lt', time()],
            'c1.condition' => ['elt', $result['total_price']['total_fee']]
        ];
        $couponList = Db::name('coupon')->alias('c1')
            ->field('c1.name,c1.money,c1.condition,c2.*')
            ->join('__COUPON_LIST__ c2', ' c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0', 'inner')
            ->where($couponWhere)
            ->select();
        if(!empty($cid)){
            $checkconpon = M('coupon')->field('id,name,money')->where("id", $cid)->find();    //要使用的优惠券
            $checkconpon['lid'] = I('lid/d');
        }

        $shippingList = M('Plugin')->where("`type` = 'shipping' and status = 1")->cache(true,TPSHOP_CACHE_TIME)->select();// 物流公司
        foreach($shippingList as $k => $v) {
            /*****优化物流信息 lishibo******/
            $dispatchs = calculate_price_sanyu($this->user_id, $result['cartList'], $v['code'], 0, $address['province'], $address['city'], $address['district']);
            if ($dispatchs['status'] == -10) {
                $test11=M('config')->where(array('name'=>"test11"))->find();
                $test11=$test11['value'];
                $duans=M('config')->where(array('name'=>'dingshi'))->find();
                $ph="已抢购空了！!！   抢购时间段：".$test11."  ".$duans['value'];
                $this->error($ph);
            }
            if ($dispatchs['status'] !== 1) {
                $this->error('物流配置有问题');
            }
            $shippingList[$k]['freight'] = $dispatchs['result']['shipping_price'];
        }
        
        $this->assign('couponList', $couponList); // 优惠券列表
        $this->assign('shippingList', $shippingList); // 物流公司

        //判断提交结算的购物车中是否同时有 零售区和批发区的商品
        foreach ($result['cartList'] as $k => $val) {
            $chuli = M('goods')->where(array('goods_id' => $val['goods_id']))->value('cat_id');
            $biaoji=$chuli;//标记是零售区(3)的订单还是批发区(4)的订单
            if ($k != 0) {
                if ($chuli != $zhong) {
            $this->error('结算中的商品不能同时有两个区的商品！',U('Mobile/Cart/cart'));
                }
            }
            $zhong = $chuli;
               $shu += $val['goods_num'];
        }
        //判断提交结算的购物车中是否同时有 零售区和批发区的商品  end
        /*
if($biaoji==3){
    if($shu != 1  ){
        $this->error ('购买零售区商品一次一件','Cart/cart');
    }
}
        */
//批发区 每天每人只能买一次
        if($biaoji==4){
            //抢购间隔
            $jiange=M('config')->where(array('name'=>"jiange"))->find();
            $jiange=$jiange['value']*86400;//间隔天数
//            $u_ge=M('users')->where("user_id",$this->user_id)->find();
//            $cha_time=time()-$u_ge['qianggou_time'];
            //抢购间隔 end
            //判断批发区商品是否在可购买时间段内
            //日期判断
            $test11=M('config')->where(array('name'=>"test11"))->find();
            $test11=$test11['value'];

            //时间段判断
            $checkDayStr = date('Y-m-d ',time());
            $duans=M('config')->where(array('name'=>'dingshi'))->find();
            $duan=explode('-',$duans['value']);
            $timeBegin1 = strtotime($checkDayStr.$duan[0]);
            $timeEnd1 = strtotime($checkDayStr.$duan[1]);
            $curr_time = time();
            $jin=date('Y年m月d日',time());
            //抢购间隔
//            if($cha_time<$jiange){//如果不够间隔天数
//                $this->error('已被抢购空！！！！抢购时间段为：'.$test11."  ".$duans['value']);
//            }

            $you=M('daishou_goods')->where("user_id",$this->user_id)->select();
            if($you){//如果不是注册用户
                $mei['user_id']=$this->user_id;
                $mei['status']=0;
                 $gua=M('daishou_goods')->where($mei)->select();
                if($gua){//如果有挂卖中的单子，不可以再次购买批发区商品
                     $this->error('已被抢购空！！！！抢购时间段为：'.$test11."  ".$duans['value']);
              }else{//如果所有挂卖的单子都卖出了，获取最后一单挂卖出的时间
                    $max_t=M('daishou_goods')->where("user_id",$this->user_id)->max('addtime');
                        $cha=time()-$max_t;
                          if($cha<$jiange){//如果不够间隔天数
                $this->error('已被抢购空！！！！抢购时间段为：'.$test11."  ".$duans['value']);
            }
               }
            }

            //抢购间隔 end
            if($jin!=$test11){
                $this->error('抢购时间段为：'.$test11."  ".$duans['value']);
            }
            if($curr_time < $timeBegin1 || $curr_time > $timeEnd1)
            {
                $this->error('抢购时间段为：'.$test11."  ".$duans['value']);
            }
            //判断批发区商品是否在可购买时间段内  end
            $strt=date("Y-m-d",time())." 0:0:0";
            $star=strtotime($strt);
            $strt=date("Y-m-d",time())." 24:00:00";
            $end=strtotime($strt);
            $countp=M('order')->where("pay_time> {$star} And pay_time<{$end} And user_id={$this->user_id} And pay_status=1 And biao=9")->count();
            if($countp>0){
                $this->error ('您今天已经购买过批发区商品','Cart/cart');
            }
            if($shu != 1){
                $this->error ('购买批发区商品一次一件','Cart/cart');
            }
        }

        $this->assign('cartList', $result['cartList']); // 购物车的商品
        $this->assign('total_price', $result['total_price']); // 总计
        $this->assign('checkconpon', $checkconpon); // 使用的优惠券
        $this->assign('biaoji', $biaoji);
        if($biaoji==4){
            $this->assign('xianshi',$_SESSION['keyong']);
        }
        return $this->fetch();
    }

    /**
     * ajax 获取订单商品价格 或者提交 订单
     */
    public function cart3(){

        if($this->user_id == 0){
            exit(json_encode(array('status'=>-100,'msg'=>"登录超时请重新登录!",'result'=>null))); // 返回结果状态
        }
        $address_id = I("address_id/d"); //  收货地址id
        $shipping_code =  I("shipping_code"); //  物流编号        
        $invoice_title = I('invoice_title'); // 发票
        $coupon_id =  I("coupon_id/d"); //  优惠券id
        $couponCode =  I("couponCode"); //  优惠券代码
        $fenlei =  I("fenlei"); //  零售区3  批发区4
        $fangshi =  I("fangshi"); //  寄卖0还是发货1
        $pay_points =  I("pay_points/d",0); //  使用积分
        $user_money =  I("user_money/f",0); //  使用余额

        $user_note = trim(I('user_note'));   //买家留言
        $paypwd =  I("paypwd",''); // 支付密码
        
        $user_money = $user_money ? $user_money : 0;
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($this->user_id);
        if($cartLogic->getUserCartOrderCount() == 0 ) {
            exit(json_encode(array('status'=>-2,'msg'=>'你的购物车没有选中商品','result'=>null))); // 返回结果状态
        }
        if(!$address_id) exit(json_encode(array('status'=>-3,'msg'=>'请先填写收货人信息','result'=>null))); // 返回结果状态
        if(!$shipping_code) exit(json_encode(array('status'=>-4,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
		
		$address = M('UserAddress')->where("address_id", $address_id)->find();
		$order_goods = M('cart')->where(['user_id'=>$this->user_id,'selected'=>1])->select();

		/*************优化物流d问题 by lishibo ***************/
		$result = calculate_price_sanyu($this->user_id,$order_goods,$shipping_code,0,$address['province'],$address['city'],$address['district'],$pay_points,$user_money,$coupon_id,$couponCode);
                
		if($result['status'] < 0)	
			exit(json_encode($result));      	
	// 订单满额优惠活动		                
        $order_prom = get_order_promotion($result['result']['order_amount']);
        $result['result']['order_amount'] = $order_prom['order_amount'] ;
        $result['result']['order_prom_id'] = $order_prom['order_prom_id'] ;
        $result['result']['order_prom_amount'] = $order_prom['order_prom_amount'] ;

        $car_price = array(
            'postFee'      => $result['result']['shipping_price'], // 物流费
            'couponFee'    => $result['result']['coupon_price'], // 优惠券
            'balance'      => $result['result']['user_money'], // 使用可用余额
            'pointsFee'    => $result['result']['integral_money'], // 积分支付
            'payables'     => $result['result']['order_amount'], // 应付金额
            'goodsFee'     => $result['result']['goods_price'],// 商品价格
            'order_prom_id' => $result['result']['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['result']['order_prom_amount'], // 订单优惠活动优惠了多少钱            
        );
       
        // 提交订单        
        if($_REQUEST['act'] == 'submit_order') {
            if($fenlei==4){//如果是批发区
                if($fangshi==0){//如果选择了平台代售
                    $orderLogic = new OrderLogic();
                    $result = $orderLogic->addPiFa($this->user_id,$car_price['balance']); // 添加订单
                    exit(json_encode($result));
                }
            }
            $pay_name = '';
            if ($pay_points || $user_money) {
                if ($this->user['is_lock'] == 1) {
                    exit(json_encode(array('status'=>-5,'msg'=>"账号异常已被锁定，不能使用余额支付！",'result'=>null))); // 用户被冻结不能使用余额支付
                }
                $pay_name = $fenlei==3 ? '余额支付' : '批发券支付';
            }
            if(empty($coupon_id) && !empty($couponCode)){
                $coupon_id = M('CouponList')->where("code", $couponCode)->getField('id');
            }
            $orderLogic = new OrderLogic();
            $result = $orderLogic->addOrder($this->user_id,$address_id,$shipping_code,$invoice_title,$coupon_id,$car_price,$user_note,$pay_name); // 添加订单
            exit(json_encode($result));
        }
            $return_arr = array('status'=>1,'msg'=>'计算成功','result'=>$car_price); // 返回结果状态
            exit(json_encode($return_arr));
    }
    /*
     * 订单支付页面
     */
    public function cart4(){

        $order_id = I('order_id/d');
        $order = M('Order')->where("order_id", $order_id)->find();
        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
        if($order['pay_status'] == 1){
            if($order['order_prom_type']==9){
                M('order')->where(array('order_id' => $order_id))->update(['order_prom_type' => 0]);
                $order_detail_url = U( "User/order_list",array('id'=>$order_id,'type'=>'WAITSEND') );
            }else{
                $order_detail_url = U( "User/order_list",array('id'=>$order_id) );
            }

            header("Location: $order_detail_url");
            exit;
        }
        $payment_where['type'] = 'payment';
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            //微信浏览器
            if($order['order_prom_type'] == 4 || $order['order_prom_type'] == 1){
                //预售订单和抢购不支持货到付款d
                $payment_where['code'] = 'weixin';
            }else{
                $payment_where['code'] = array('in',array('weixin','cod'));
            }
        }else{
            if($order['order_prom_type'] == 4 || $order['order_prom_type'] == 1){
                //预售订单和抢购不支持货到付款ddd
                $payment_where['code'] = array('neq','cod');
            }
            $payment_where['scene'] = array('in',array('0','1'));
        }
        if($order['order_prom_type'] != 4){
            $userlogic = new \app\home\logic\UsersLogic();
            $res = $userlogic->abolishOrder($order['user_id'],$order['order_id'],$order['add_time']);  //检测是否超时没支付
            if($res['status']==1)
                $this->error('订单超时未支付已自动取消',U("Mobile/User/order_detail",array('id'=>$order_id)));
        }

        $payment_where['status'] = 1;
        //预售和抢购暂不支持货到付款
        $orderGoodsPromType = M('order_goods')->where(['order_id'=>$order['order_id']])->getField('prom_type',true);
        if($order['order_prom_type'] == 4 || in_array(1,$orderGoodsPromType)){
            $payment_where['code'] = array('neq','cod');
        }
        $paymentList = M('Plugin')->where($payment_where)->select();
        $paymentList = convert_arr_key($paymentList, 'code');

        foreach($paymentList as $key => $val)
        {
            $val['config_value'] = unserialize($val['config_value']);
            if($val['config_value']['is_bank'] == 2)
            {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);
            }
            //判断当前浏览器显示支付方式
            if(($key == 'weixin' && !is_weixin()) || ($key == 'alipayMobile' && is_weixin())){
                unset($paymentList[$key]);
            }
        }

        $bank_img = include APP_PATH.'home/bank.php'; // 银行对应图片
        $payment = M('Plugin')->where("`type`='payment' and status = 1")->select();
        $this->assign('paymentList',$paymentList);
        $this->assign('bank_img',$bank_img);
        $this->assign('order',$order);
        $this->assign('bankCodeList',$bankCodeList);
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        return $this->fetch();
    }


    /*
    * ajax 请求获取购物车列表
    */
    public function ajaxCartList()
    {
        $post_goods_num = I("goods_num/a"); // goods_num 购物车商品数量
        $post_cart_select = I("cart_select/a"); // 购物车选中状态
        $goodsLogic = new GoodsLogic();
        $where['session_id'] = $this->session_id; // 默认按照 session_id 查询
        //如果这个用户已经登录则按照用户id查询
        if ($this->user_id) {
            unset($where);
            $where['user_id'] = $this->user_id;
        }
        $cartList = M('Cart')->where($where)->getField("id,goods_num,selected,prom_type,prom_id,goods_id,goods_price,spec_key");
        if ($post_goods_num) {
            // 修改购物车数量 和勾选状态
            foreach ($post_goods_num as $key => $val) {
                $data['goods_num'] = $val < 1 ? 1 : $val;
                $data['selected'] = $post_cart_select[$key] ? 1 : 0;
                //普通商品
                if($cartList[$key]['prom_type'] == 0 && (empty($cartList[$key]['spec_key']))){
                    $goods = Db::name('goods')->where('goods_id', $cartList[$key]['goods_id'])->find();
                    // 如果有阶梯价格
                    if (!empty($goods['price_ladder'])) {
                        $price_ladder = unserialize($goods['price_ladder']);
                        $data['member_goods_price'] = $data['goods_price'] = $goodsLogic->getGoodsPriceByLadder($data['goods_num'], $cartList[$key]['goods_price'], $price_ladder);
                    }
                }
                //限时抢购 不能超过购买数量
                if ($cartList[$key]['prom_type'] == 1) {
                    $FlashSaleLogic = new \app\admin\logic\FlashSaleLogic($cartList[$key]['prom_id']);
                    $data['goods_num'] = $FlashSaleLogic->getUserFlashResidueGoodsNum($this->user_id,$data['goods_num']); //获取用户剩余抢购商品数量
                }
                //团购 不能超过购买数量
                if($cartList[$key]['prom_type'] == 2){
                    $groupBuyLogic =  new \app\admin\logic\GroupBuyLogic($cartList[$key]['prom_id']);
                    $groupBuySurplus = $groupBuyLogic->getPromotionSurplus();//团购剩余库存
                    if($data['goods_num'] > $groupBuySurplus){
                        $data['goods_num'] = $groupBuySurplus;
                    }
                }
                if (($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected'])){
                    M('Cart')->where("id", $key)->save($data);
                }
            }
            $this->assign('select_all', input('post.select_all')); // 全选框
        }
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($this->user_id);
        $result = $cartLogic->getUserCartList(1);
//        $result = $this->cartLogic->cartList($this->user, $this->session_id,1);
        if(empty($result['total_price'])){
            $result['total_price'] = array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0, 'atotal_fee' =>0, 'acut_fee' =>0, 'anum' => 0);
        }
        $this->assign('cartList', $result['cartList']); // 购物车的商品
        $this->assign('total_price', $result['total_price']); // 总计
        return $this->fetch('ajax_cart_list');
    }

    /*
 * ajax 获取用户收货地址 用于购物车确认订单页面
 */
    public function ajaxAddress(){
        $regionList = get_region_list();
        $address_list = M('UserAddress')->where("user_id", $this->user_id)->select();
        $c = M('UserAddress')->where("user_id = {$this->user_id} and is_default = 1")->count(); // 看看有没默认收货地址
        if((count($address_list) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $address_list[0]['is_default'] = 1;

        $this->assign('regionList', $regionList);
        $this->assign('address_list', $address_list);
        return $this->fetch('ajax_address');
    }

    /**
     * ajax 删除购物车的商品
     */
    public function ajaxDelCart()
    {
        $ids = I("ids"); // 商品 ids
        $result = M("Cart")->where("id","in",$ids)->delete(); // 删除id为5的用户数据
        $return_arr = array('status'=>1,'msg'=>'删除成功','result'=>''); // 返回结果状态
        exit(json_encode($return_arr));
    }

}
