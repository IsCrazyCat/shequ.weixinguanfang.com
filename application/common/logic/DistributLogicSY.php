<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 聊城博商网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.boshang3710.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: 博商网络
 * Date: 2015-09-09
 * 
 * TPshop 公共逻辑类  将放到Application\Common\Logic\   由于很多模块公用 将不在放到某个单独模下面
 */

namespace app\common\logic;

use think\Model;
use think\Log;
//use think\Page;

/**
 * 分销逻辑层
 * Class CatsLogic
 * @package Home\Logic
 */
class DistributLogicSY //extends Model
{
     public function hello(){
        echo 'function hello(){'; 
     }

    /**
     *  根据当前等级核算新增标准
     * @autho lishibo 20190226
     * @param $level
     * @return int
     */
    function checkNewUndelineNumber($level){
        $numbs = 0;
        if($level == 3 ){//主管
            $numbs = 2;
        }elseif($level == 4){//经理
            $numbs = 8 ;
        }elseif($level == 5){//总监
            $numbs=15;
        }elseif($level == 6){//合伙人
            $numbs=100;
        }
        return $numbs;
    }



    /**
     * 检查累计人数对应的等级
     * @author  lishibo 20190215
     * @param $underling_number
     * @return int
     */
    function checkLevelUndelineNumber($underling_number){
        $tmpLevel = 0;
        if(10 <= $underling_number && $underling_number < 50){//主管
            $tmpLevel=3;
        }elseif(50 <= $underling_number && $underling_number < 400){//经理
            $tmpLevel=4;
        }elseif(400 <= $underling_number && $underling_number < 1500){//总监
            $tmpLevel=5;
        }elseif(1500 <= $underling_number){//合伙人
            $tmpLevel=6;
        }
        return $tmpLevel;
    }

    /**
     * 检查直推人数对应的等级
     * @author  lishibo 20190215
     * @param $underling_direct_number
     * @return int
     */
    function checkLevelUndelineDirectNumber ($underling_direct_number){
        $tmpLevel = 0;
        if(5 <= $underling_direct_number && $underling_direct_number < 20){//主管
            $tmpLevel=3;
        }elseif(20 <= $underling_direct_number && $underling_direct_number < 80){//经理
            $tmpLevel=4;
        }elseif(80 <= $underling_direct_number && $underling_direct_number < 200){//总监
            $tmpLevel=5;
        }elseif(200 <= $underling_direct_number){//合伙人
            $tmpLevel=6;
        }
        return $tmpLevel;
    }


    /**
     * @author  lishibo 2019-02-13
     * @param $user 消费者
     * @param $tmp_user 分成者
     * @param $order 订单
     * @param $money 分成（一级奖励 or 渠道奖励）
     * @param $level 获取分成得目标级别
     * @param $jxmc  奖项名称
     * @param $jssdk weixin api
     */
    public function rebate_log_business($user,$tmp_user,$order,$money,$level,$jxmc,$jssdk,$buy_user_level,$user_level){

        $content = "您的".$level."级代理,下了一笔订单:{$order['order_sn']} 如果交易成功您将获得 ￥".sprintf("%.2f", $money)."奖励 !";
        $data = array(
            'user_id' =>$tmp_user['user_id'],
            'buy_user_id'=>$user['user_id'],
            'nickname'=>$user['nickname'],
            'order_sn' => $order['order_sn'],
            'order_id' => $order['order_id'],
            'goods_price' => $order['goods_price'],
            'money' =>$money,
            'level' => $level,
            'create_time' => time(),
            'remark' => $content,
            'jxmc' => $jxmc,
            'status' => 1,//0未付款,1已付款, 2等待分成(已收货) 3已分成, 4已取消
            'buy_user_level_name' => $buy_user_level['level_name'],
            'user_level_name' => $user_level['level_name'],
        );

        M('rebate_log')->add($data);

        // 微信推送消息
        //$tmp_user = M('users')->where("user_id", $user['third_leader'])->find();
        if($tmp_user['oauth']== 'weixin')
        {
            $jssdk->push_msg($tmp_user['openid'],$content);
        }
    }

    /**
     * @author  lishibo 2019-02-13
     *  分销记录
     */
     public function rebate_log($order){

		if ( $order['pay_status'] == 0 ) {
		   exit;
		}
		$mymap = array('order_id' => $order['order_id']);
		$abccount = 0;
		$abccount =  M('rebate_log')->where($mymap)->count(); // 查询满足要求的总记录数
		if ((int)$abccount >= 1) { //防止重复
			exit;
		}
	     
         $user = M('users')->where("user_id", $order['user_id'])->find();
         $pattern = tpCache('distribut.pattern'); // 分销模式

        $order_rate = tpCache('distribut.order_rate'); // 订单分成比例
        $commission = $order['goods_price'] * ($order_rate / 100); // 订单的商品总额 乘以 订单分成比例
        if($commission == 0) {return false;} // 如果这笔订单没有分销金额

        //  微信消息推送
        $wx_user = M('wx_user')->find();
        $jssdk = new \app\mobile\logic\Jssdk($wx_user['appid'],$wx_user['appsecret']);

        /**************************************START！！ 三渔最新分销体系 20190522*******************************************/

         /*******************会员等级2、3以上复购，拥有对应等级的自购分成***************/
         $buy_user_level = M('user_level')->where("level_id", (int)$user['level'])->find();
         $channel=0; //复购自分成标识
         if($buy_user_level){
             if($buy_user_level['level_id']>1){
                 $channel = 1;
             }
         }else{ exit; }
         /*******************会员等级2、3以上复购，拥有对应等级的自购分成***************/

         if($user['first_leader'] > 0 ){
             $first_leader = M('users')->where("user_id = ".$user['first_leader'])->find();
             if($first_leader){
                 $this->checkingDistributionProfit($order,$first_leader,$user, $commission,$jssdk,1,0,$channel,$buy_user_level);
             }
         }else{

             /*******************START!! step3: 过滤条件,至少是2、3以上才有分成资格*********************/
             if($channel>0){
                 $buy_commission_rate_sel = (int)$buy_user_level['commission_rate'];
                 $distributMondy_self = $commission * ( $buy_commission_rate_sel / 100 );
                 $this->rebate_log_business_new($user,$user,$order,$distributMondy_self,0,$jssdk,1);
             }
             /*******************START!! step3: 过滤条件,至少是2、3以上才有分成资格*********************/

         }

         /**************************************END！！ 三渔最新分销体系 20190522*******************************************/

         M('order')->where("order_id", $order['order_id'])->save(array("is_distribut"=>1));  //修改订单为已经分成
     }


     public function rebate_log_活动产品不参与分成($order){

		if ( $order['pay_status'] == 0 ) {
		   exit;
		}
		$mymap = array('order_id' => $order['order_id']);
		$abccount = 0;
		$abccount =  M('rebate_log')->where($mymap)->count(); // 查询满足要求的总记录数
		if ((int)$abccount >= 1) { //防止重复
			exit;
		}

         $user = M('users')->where("user_id", $order['user_id'])->find();
         $pattern = tpCache('distribut.pattern'); // 分销模式

        $order_rate = tpCache('distribut.order_rate'); // 订单分成比例
        $commission = $order['goods_price'] * ($order_rate / 100); // 订单的商品总额 乘以 订单分成比例
        if($commission == 0) {return false;} // 如果这笔订单没有分销金额

        //  微信消息推送
        $wx_user = M('wx_user')->find();
        $jssdk = new \app\mobile\logic\Jssdk($wx_user['appid'],$wx_user['appsecret']);

        /**************************************START！！ 三渔最新分销体系 20190522*******************************************/

         /*******************会员等级3以上复购，拥有对应等级的自购分成***************/
         $buy_user_level = M('user_level')->where("level_id", (int)$user['level'])->find();
         $channel=0; //复购自分成标识
         if($buy_user_level){
             if($buy_user_level['level_id']>2){ //顾客与体验会员无自购分成 20190522
                 $channel = 1;
             }
         }else{ exit; }
         /*******************会员等级3以上复购，拥有对应等级的自购分成***************/

         if($user['first_leader'] > 0 ){
             $first_leader = M('users')->where("user_id = ".$user['first_leader'])->find();
             if($first_leader){
                 $this->checkingDistributionProfit($order,$first_leader,$user, $commission,$jssdk,1,0,$channel,$buy_user_level);
             }
         }

         /**************************************END！！ 三渔最新分销体系 20190522*******************************************/

         M('order')->where("order_id", $order['order_id'])->save(array("is_distribut"=>1));  //修改订单为已经分成
     }





    /**
     * 分成记录&&消息通知
     * @author lishibo 20190522
     * @param $leader
     * @param $user
     * @param $order
     * @param $commission
     * @param $jssdk
     * @param int $level
     * @param int $numbers
     */
    function checkingDistributionProfit($order,$leader,$user, $commission,$jssdk,$level = 0,$numbers=0,$channel=0,$buy_user_level_model){

        if( $level == 1 ){

            $all_order = $user['all_leader'];
            $arr_ = explode(',',$all_order);//验证前后关系
            $arr = array_reverse($arr_);
            $k = 1;

            $buy_user_level = (int)$user['level'];
            $user['level_name'] = $buy_user_level_model['level_name'];

            //确定初始分成等级
            $tmp_level = 2;
            if($channel>0){
                $tmp_level = $buy_user_level;
            }else{
                $tmp_level = 2;
            }

            foreach ($arr as $val){ //分销商分成
                //if($k<$numbers){ //不限制层级

                    if((int)$val>0){
                        $tmp_leader = M('users')->where("user_id = ".(int)$val)->find();
                        $tmp_leader_level = (int)$tmp_leader['level'];

                        $leader_user_model = M('user_level')->where("level_id = ".(int)$tmp_leader['level'])->find();
                        $tmp_leader['level_name'] = $leader_user_model['level_name'];

                        //当前界别对应的分成
                        $leader_commission_rate = (int)$leader_user_model['commission_rate'];


                        //f分成资格，高低差额，同级截流
                        if($tmp_leader_level>=2){ //初始分成级别


                            /*******************START!! step1: 过滤条件,至少是4、5才有分成资格*********************/
                            if($tmp_leader_level>$tmp_level){

                                /**********step2 如何分：同级截流，高低差额***********/
                                if($k==1){ //直推人判断（特殊）

                                    if($buy_user_level<2){//足额分成
                                        $distributMondy = $commission * ( $leader_commission_rate / 100 );
                                    }else{
                                        if($tmp_leader_level>$buy_user_level){//取差额分成

                                            //$buy_user_level_model = M('user_level')->where("level_id = ".$buy_user_level)->find();
                                            $buy_commission_rate = (int)$buy_user_level_model['commission_rate'];
                                            $distributMondy = $commission * ( ($leader_commission_rate-$buy_commission_rate) / 100 );
                                        }

                                    }

                                }else{
                                    $tmp_level_model = M('user_level')->where("level_id = ".$tmp_level)->find();
                                    $tmp_commission_rate = (int)$tmp_level_model['commission_rate'];
                                    $distributMondy = $commission * ( ($leader_commission_rate-$tmp_commission_rate) / 100 );
                                }


                                if($distributMondy>0){  //分成入口
                                    $this->rebate_log_business_new($tmp_leader,$user,$order,$distributMondy,$k,$jssdk,0);
                                }

                                /**********step2 如何分：同级截流，高低差额***********/



                                //更新级差
                                $tmp_level = $tmp_leader_level;
                            }
                            /*******************END!!! step1: 过滤条件,至少是4、5才有分成资格*********************/

                        }

                    }

                $k++;
            }


            /*******************START!! step3: 过滤条件,至少是3才有分成资格*********************/
            if($channel>0){
                $buy_commission_rate_sel = (int)$buy_user_level_model['commission_rate'];
                $distributMondy_self = $commission * ( $buy_commission_rate_sel / 100 );
                $this->rebate_log_business_new($user,$user,$order,$distributMondy_self,0,$jssdk,1);
            }
            /*******************END!! step3: 过滤条件,至少是3才有分成资格*********************/

        }

    }

    /**
     * 分成日志与消息通知
     * @author lishibo 20190522
     * @param $tmp_leader
     * @param $user
     * @param $order
     * @param $distributMondy
     * @param $level
     * @param $jssdk
     */
    public function rebate_log_business_new($tmp_leader,$user,$order,$distributMondy,$level,$jssdk,$type){

        if($type==0){
            $wx_content = "您的".$level."级下线,刚刚下了一笔订单:{$order['order_sn']} 如果交易成功你将获得价值 ￥".$distributMondy."的平台奖励（含税）!";
            $jxmc = '分销佣金';
        }else{
            $wx_content = "您刚刚下了一笔订单:{$order['order_sn']} 如果交易成功你将获得价值 ￥".$distributMondy."的平台奖励（含税） !";
            $jxmc = '自购佣金';
        }

        $data = array(
            'user_id' =>$tmp_leader['user_id'], //上级id
            'buy_user_id'=>$user['user_id'],
            'nickname'=>$user['nickname'],
            'order_sn' => $order['order_sn'],
            'order_id' => $order['order_id'],
            'goods_price' => $order['goods_price'],
            'money' =>  $distributMondy,
            'status' =>  1, //已付款
            'remark' =>  $wx_content,
            'store_id' =>  $order['store_id'],//店铺id
            'level' => $tmp_leader['level'], //获分成级别
            'create_time' => time(),
            'jxmc' => $jxmc,
            'status' => 1,//0未付款,1已付款, 2等待分成(已收货) 3已分成, 4已取消
            'buy_user_level_name' => $user['level_name'],
            'user_level_name' => $tmp_leader['level_name'],
        );

        M('rebate_log')->add($data);

        // 微信推送消息
        if($tmp_leader['oauth']== 'weixin')
        {
            $jssdk->push_msg($tmp_leader['openid'],$wx_content);
        }

    }


    public function rebate_log_bak_第一版分成($order){

		if ( $order['pay_status'] == 0 ) {
		   exit;
		}
		$mymap = array('order_id' => $order['order_id']);
		$abccount = 0;
		$abccount =  M('rebate_log')->where($mymap)->count(); // 查询满足要求的总记录数
		if ((int)$abccount >= 1) {
			exit;
		}

         $user = M('users')->where("user_id", $order['user_id'])->find();
         $pattern = tpCache('distribut.pattern'); // 分销模式

        /*查看购买者等级  区分渠道分成体系*/
        $buy_user_level = M('user_level')->where("level_id", (int)$user['level'])->find();
        $channel=0;//默认
        if($buy_user_level){
           if($buy_user_level['level_id']>1){//复购
               $channel = 1;
           }
        }else{ exit; }

        $order_rate = tpCache('distribut.order_rate'); // 订单分成比例
        $commission = $order['goods_price'] * ($order_rate / 100); // 订单的商品总额 乘以 订单分成比例
        if($commission == 0) {return false;} // 如果这笔订单没有分销金额

        //  微信消息推送
        $wx_user = M('wx_user')->find();
        $jssdk = new \app\mobile\logic\Jssdk($wx_user['appid'],$wx_user['appsecret']);

        $today_time = time();
       // $rebate_log_arr = M('rebate_log')->where(" status = 2  and  is_show = 0 ")->select();
            //查找三级关系，判断有无上级
            $first_leader_id=(int)$user['first_leader'];
            $second_leader_id=(int)$user['second_leader'];
            $third_leader_id=(int)$user['third_leader'];
            $channel_rewards_first = array(8,10,13,17);
            $channel_rewards_more = array(3,4,5,6);
            $tmp_level=0;//上线等级

            //直推人
            if($first_leader_id>0){

                 $first_leader = M('users')->where("user_id", $first_leader_id)->find();
                 $first_leader_level = (int)$first_leader['level'];
                 $user_level =  M('user_level')->where("level_id", $first_leader_level)->find();
                 $tmp_level = $first_leader_level;

                 //直推人（一级奖励）与（渠道奖励）
                 if($first_leader_level <= 2 ){
                     if($channel >0  ){ //一级奖励（复购3%）
                         $this->rebate_log_business($user,$first_leader,$order,$commission * (3/100),1,'一级奖励',$jssdk,$buy_user_level,$user_level);
                     }else{//一级奖励（首购10%）
                         $this->rebate_log_business($user,$first_leader,$order,$commission * (10/100),1,'一级奖励',$jssdk,$buy_user_level,$user_level);
                     }
                 }else{ //渠道奖励（主管及以上）
                     if($channel >0  ){//渠道奖励（复购）
                         $this->rebate_log_business($user,$first_leader,$order,$commission * ($first_leader_level/100),1,'渠道奖励',$jssdk,$buy_user_level,$user_level);
                     }else{//渠道奖励（首购）
                         $tmpv = $channel_rewards_first[$first_leader_level-3];
                         $this->rebate_log_business($user,$first_leader,$order,$commission * ($tmpv/100),1,'渠道奖励',$jssdk,$buy_user_level,$user_level);
                     }
                 }

                 if($second_leader_id>0){

                     $second_leader = M('users')->where("user_id", $second_leader_id)->find();
                     $second_leader_level = (int)$second_leader['level'];
                     $user_level =  M('user_level')->where("level_id", $second_leader_level)->find();

                     if($second_leader_level > 2 ){

                        if($tmp_level < $second_leader_level ) {

                            if($channel >0  ){//渠道奖励（复购）
                                $tmpv = $second_leader_level - $tmp_level;//取差值
                            }else{//渠道奖励（首购）
                                $tmpv = $channel_rewards_first[$second_leader_level-3]-$channel_rewards_first[$tmp_level-3];//取差值
                            }
                            $this->rebate_log_business($user,$second_leader,$order,$commission * ($tmpv/100),1,'渠道奖励',$jssdk,$buy_user_level,$user_level);
                            $tmp_level = $second_leader_level;
                        }

                     }

                     if($third_leader_id>0){

                         $third_leader = M('users')->where("user_id", $third_leader_id)->find();
                         $third_leader_level = (int)$third_leader['level'];
                         $user_level =  M('user_level')->where("level_id", $third_leader_level)->find();

                         if($tmp_level < $third_leader_level ) {

                             if($channel >0  ){//渠道奖励（复购）
                                 $tmpv = $third_leader_level - $tmp_level;//取差值
                             }else{//渠道奖励（首购）
                                 $tmpv = $channel_rewards_first[$third_leader_level-3]-$channel_rewards_first[$tmp_level-3];//取差值
                             }
                             $this->rebate_log_business($user,$third_leader,$order,$commission * ($tmpv/100),1,'渠道奖励',$jssdk,$buy_user_level,$user_level);
                         }

                     }

                 }
             }

         M('order')->where("order_id", $order['order_id'])->save(array("is_distribut"=>1));  //修改订单为已经分成
     }







	 
     /**
      * 分销关系之间进行分成
      * 确认收货后判断分成条件
      * @author  lishibo
      * 20190214
      */
     function confirm_justs($order_id)
     {

         $switch = tpCache('distribut.switch');
         if ($switch == 0) {
             return false;
         }

         //当前订单待分成记录
         $where['order_id'] = $order_id;
         $where['status'] = 2;
         $rebate_log_arr = M('rebate_log')->where($where)->select();

         //判断分成条件
         $today_time = time();
         $distribut_date = tpCache('distribut.date');
         if ($distribut_date == '0') {
             //------------
             foreach ($rebate_log_arr as $key => $val) {
                 $tmp_user = M('users')->where("user_id", $val['user_id'])->find();
                 $tmp_buy_user = M('users')->where("user_id", $val['buy_user_id'])->find();

                 if (strlen($val['jxmc']) > 0) {

                     $vv['is_show'] = 1;
                     $vv['create_time'] = $today_time;
                     $vv['remark'] = "达到" . $val['jxmc'] . "条件,确认收货,自动分成.";
                     $vv['status'] = 3;
                     $vv['confirm_time'] = $today_time;
                     M("rebate_log")->where("id", $val['id'])->save($vv);

                     $delivery = M('delivery_doc')->where("order_id", $val['order_id'])->find();
                     my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣", $val['money'], $val['order_id'], $val['order_sn'], $delivery['id'], $val['goods_price'], $val['jxmc']);

                 } else {
                     accountLog($val['user_id'], $val['money'], 0, "来自" . $tmp_buy_user['nickname'] . "（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣", $val['goods_price'], $val['order_id'], $val['order_sn']);
                 }

             }
             //------------

         } else {//待分成

          /*   foreach ($rebate_log_arr as $key => $val) {
                 //$tmp_user = M('users')->where("user_id", $val['user_id'])->find();
                 //$tmp_buy_user = M('users')->where("user_id", $val['buy_user_id'])->find();
                 if (strlen($val['jxmc']) > 0) {
                     $vv['is_show'] = 1;
                     $vv['create_time'] = $today_time;
                     $vv['remark'] = $val['jxmc'] . " 自动分成{$distribut_date}天等待期,期满后平台自动分成.";
                     M("rebate_log")->where("id", $val['id'])->save($vv);
                 }
             }*/

         }
     }


    /**
     * 筛选符合条件的分成记录进行分成
     * @author  lishibo
     * 20190214
     */
     function auto_confirm(){

         echo "CONSOLE: win计划任务自动分成".date('Y-m-d H:i:s')."\r\n";
         Log::write("LOG: win计划任务自动分成".date('Y-m-d H:i:s'),true);

         $switch = tpCache('distribut.switch');
         if($switch == 0){ return false;}
         $today_time = time();
         $distribut_date = tpCache('distribut.date');
         $distribut_time = $distribut_date * (60 * 60 * 24)-1; // 计算天数 时间戳  多减60秒
         $rebate_log_arr = M('rebate_log')->where("status = 2 and ($today_time - confirm) >  $distribut_time")->select();

         foreach ($rebate_log_arr as $key => $val)
         {
			$tmp_user = M('users')->where("user_id", $val['user_id'])->find();
			$tmp_buy_user = M('users')->where("user_id", $val['buy_user_id'])->find();

			if ( strlen($val['jxmc'])>0 ) {

                   $vv['is_show'] = 1;
                   $vv['remark'] = "达到".$val['jxmc']."条件,确认收货,自动分成.";
                   $vv['status'] = 3;
                   $vv['confirm_time'] = $today_time;
                   M("rebate_log")->where("id", $val['id'])->save($vv);

                   $delivery = M('delivery_doc')->where("order_id", $val['order_id'])->find();
                   my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money'],$val['order_id'] ,$val['order_sn'],$delivery['id'],$val['goods_price'],$val['jxmc']);

			}else{
				accountLog($val['user_id'], $val['money'], 0,"来自".$tmp_buy_user['nickname']."（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money']);
			}

         }
     }


         /**
          * 初备
          * @return bool
          */
     function confirm__copy(){

         $switch = tpCache('distribut.switch');
         if($switch == 0){ return false;}
         $today_time = time();
         $distribut_date = tpCache('distribut.date');
         $rebate_log_arr = M('rebate_log')->where(" status = 2 ")->select();

         foreach ($rebate_log_arr as $key => $val)
         {
			$tmp_user = M('users')->where("user_id", $val['user_id'])->find();
			$tmp_buy_user = M('users')->where("user_id", $val['buy_user_id'])->find();

			if ( strlen($val['jxmc'])>0 ) {

                   $vv['is_show'] = 1;
                   $vv['create_time'] = $today_time;
                   $vv['remark'] = "达到".$val['jxmc']."条件,确认收货,自动分成.";
                   $vv['status'] = 3;
                   $vv['confirm_time'] = $today_time;
                   M("rebate_log")->where("id", $val['id'])->save($vv);

                   $delivery = M('delivery_doc')->where("order_id", $val['order_id'])->find();
                   my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money'],$val['order_id'] ,$val['order_sn'],$delivery['id'],$val['goods_price'],$val['jxmc']);

			}else{
				accountLog($val['user_id'], $val['money'], 0,"来自".$tmp_buy_user['nickname']."（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money']);
			}

         }
     }


     /**
      * 自动分成 符合条件的 分成记录
      */
     function auto_confirm_copy(){

         $switch = tpCache('distribut.switch');
         if($switch == 0)
             return false;

		 $today_time = time();
		 $rebate_log_arr = M('rebate_log')->where(" status = 2  and  is_show = 0 ")->select();

		 foreach($rebate_log_arr as $key => $val)
		 {
			$tmp_user = M('users')->where("user_id", $val['user_id'])->find();
			$tmp_buy_user = M('users')->where("user_id", $val['buy_user_id'])->find();

			if ( strstr($val['jxmc'],'市场维护费') &&  $tmp_user['level'] >= 3 &&  $tmp_user['sfffyj3'] == 3  ) {

					$vv['is_show'] = 1;
					$vv['create_time'] = $today_time;
					$vv['remark'] = " 达到市场维护费补齐条件X。 ";
					$vv['status'] = 3;
                    $vv['confirm_time'] = $today_time;
					M("rebate_log")->where("id", $val['id'])->save($vv);

					my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money'],$val['order_id'] ,$val['order_sn'],$delivery['id'],$val['goods_price'],$val['jxmc']);

			}

			if ( strstr($val['jxmc'],'领导奖')  && $tmp_user['level'] == 4   ) { // && $tmp_user['sfffyj4'] == 4

					$vv['is_show'] = 1;
					$vv['create_time'] = $today_time;
					$vv['remark'] = " 达到领导奖补齐条件Y。 ";
					$vv['status'] = 3;
                    $vv['confirm_time'] = $today_time;
					M("rebate_log")->where("id", $val['id'])->save($vv);

					my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money'],$val['order_id'] ,$val['order_sn'],$delivery['id'],$val['goods_price'],$val['jxmc']);

			}

		 }




         $distribut_date = tpCache('distribut.date');
         $distribut_time = $distribut_date * (60 * 60 * 24)-1; // 计算天数 时间戳  多减60秒
         //$rebate_log_arr = M('rebate_log')->where("status = 2 and ($today_time - confirm) >  $distribut_time")->select();
         $rebate_log_arr = M('rebate_log')->where(" status = 2 and  is_show = 1 ")->select();

//		 $wx_user = M('wx_user')->find();
//       $jssdk = new \app\mobile\logic\Jssdk($wx_user['appid'],$wx_user['appsecret']);



         foreach ($rebate_log_arr as $key => $val)
         {
			$tmp_user = M('users')->where("user_id", $val['user_id'])->find();
			$tmp_buy_user = M('users')->where("user_id", $val['buy_user_id'])->find();

			if ( strlen($val['jxmc'])>0 ) {
				$delivery = M('delivery_doc')->where("order_id", $val['order_id'])->find();

				if ( strstr($val['jxmc'],'市场维护费') &&  $tmp_user['level'] >= 3 &&  $tmp_user['sfffyj3'] == 3  ) {

					my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money'],$val['order_id'] ,$val['order_sn'],$delivery['id'],$val['goods_price'],$val['jxmc']);
				} elseif ( strstr($val['jxmc'],'领导奖')  && $tmp_user['level'] == 4   ) { // && $tmp_user['sfffyj4'] == 4

					my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money'],$val['order_id'] ,$val['order_sn'],$delivery['id'],$val['goods_price'],$val['jxmc']);
				} //elseif ( strstr($val['jxmc'],'销售佣金') || strstr($val['jxmc'],'市场开拓费') ) {
					else {
					my_accountLog($val['user_id'], $val['money'], 0, "来自{$tmp_buy_user['nickname']}（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money'],$val['order_id'] ,$val['order_sn'],$delivery['id'],$val['goods_price'],$val['jxmc']);
				}


			}else{
				accountLog($val['user_id'], $val['money'], 0,"来自".$tmp_buy_user['nickname']."（{$val['buy_user_id']}）订单:{$val['order_sn']}分佣",$val['money']);
			}

             $val['status'] = 3;
             $val['confirm_time'] = $today_time;
			 if ( $distribut_date == '0' ) {
				 $val['remark'] = $val['remark']." 确认收货,自动分成.";
			 } else {
				 $val['remark'] = $val['remark']." 满{$distribut_date}天,自动分成.";
			 }

             M("rebate_log")->where("id", $val['id'])->save($val);
         }
     }
}