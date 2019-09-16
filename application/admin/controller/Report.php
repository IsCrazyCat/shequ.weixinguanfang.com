<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 聊城博商网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.boshang3710.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: 当燃      
 * Date: 2015-12-21
 */

namespace app\admin\controller;
use app\admin\logic\GoodsLogic;
use think\Db;
use think\Page;

class Report extends Base{
	public $begin;
	public $end;
	public function _initialize(){
        parent::_initialize();
		
		if(I('start_time')){
                        $begin = I('start_time');
                        $end = I('end_time');
		}else{
                        $begin = date('Y-m-d', strtotime("-3 month"));//30天前
                        $end = date('Y-m-d', strtotime('+1 days'));
		}
		$this->assign('start_time',$begin);
		$this->assign('end_time',$end);
		$this->begin = strtotime($begin);
		$this->end = strtotime($end)+86399;
	}
	
	public function index(){
		$now = strtotime(date('Y-m-d'));
		$today['today_amount'] = M('order')->where("add_time>$now AND (pay_status=1 or pay_code='cod') and order_status in(1,2,4)")->sum('order_amount');//今日销售总额
		$today['today_order'] = M('order')->where("add_time>$now and (pay_status=1 or pay_code='cod')")->count();//今日订单数
		$today['cancel_order'] = M('order')->where("add_time>$now AND order_status=3")->count();//今日取消订单
		if ($today['today_order'] == 0) {
			$today['sign'] = round(0, 2);
		} else {
			$today['sign'] = round($today['today_amount'] / $today['today_order'], 2);
		}
		$this->assign('today',$today);
		$sql = "SELECT COUNT(*) as tnum,sum(order_amount) as amount, FROM_UNIXTIME(add_time,'%Y-%m-%d') as gap from  __PREFIX__order ";
		$sql .= " where add_time>$this->begin and add_time<$this->end AND (pay_status=1 or pay_code='cod') and order_status in(1)  and shipping_status =1 group by gap ";
//		$sql .= " where add_time>$this->begin and add_time<$this->end AND (pay_status=1 or pay_code='cod') and order_status in(1,2,4) group by gap ";
		$res = DB::query($sql);//订单数,交易额
		
		foreach ($res as $val){
			$arr[$val['gap']] = $val['tnum'];
			$brr[$val['gap']] = $val['amount'];
			$tnum += $val['tnum'];
			$tamount += $val['amount'];
		}

		for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
			$tmp_num = empty($arr[date('Y-m-d',$i)]) ? 0 : $arr[date('Y-m-d',$i)];
			$tmp_amount = empty($brr[date('Y-m-d',$i)]) ? 0 : $brr[date('Y-m-d',$i)];
			$tmp_sign = empty($tmp_num) ? 0 : round($tmp_amount/$tmp_num,2);						
			$order_arr[] = $tmp_num;
			$amount_arr[] = $tmp_amount;			
			$sign_arr[] = $tmp_sign;
			$date = date('Y-m-d',$i);
			$list[] = array('day'=>$date,'order_num'=>$tmp_num,'amount'=>$tmp_amount,'sign'=>$tmp_sign,'end'=>date('Y-m-d',$i+24*60*60));
			$day[] = $date;
		}
		rsort($list);
		$this->assign('list',$list);
		$result = array('order'=>$order_arr,'amount'=>$amount_arr,'sign'=>$sign_arr,'time'=>$day);
		$this->assign('result',json_encode($result));
		return $this->fetch();
	}

	public function saleTop(){
		$sql = "select goods_name,goods_sn,sum(goods_num) as sale_num,sum(goods_num*goods_price) as sale_amount from __PREFIX__order_goods ";
		$sql .=" where is_send = 1 group by goods_id order by sale_amount DESC limit 100";
		$res = DB::cache(true,3600)->query($sql);
		$this->assign('list',$res);
		return $this->fetch();
	}
	
	public function userTop(){
//		$p = I('p',1);
//		$start = ($p-1)*20;
		$mobile = I('mobile');
		$email = I('email');
		if($mobile){
			$where =  "and b.mobile='$mobile'";
		}		
		if($email){
			$where = "and b.email='$email'";
		}
		$sql = "select count(a.order_id) as order_num,sum(a.total_amount) as amount,a.user_id,b.mobile,b.email,b.nickname from __PREFIX__order as a left join __PREFIX__users as b ";
		$sql .= " on a.user_id = b.user_id where a.add_time>$this->begin and a.add_time<$this->end and a.pay_status=1 and  order_status in(1,2,4)  $where group by user_id order by amount DESC limit 0,100";
		$res = DB::cache(true)->query($sql);
		$this->assign('list',$res);
//		if(empty($where)){
//			$count = M('order')->where("add_time>$this->begin and add_time<$this->end and pay_status=1")->group('user_id')->count();
//			$Page = new Page($count,20);
//			$show = $Page->show();
//			$this->assign('page',$show);
//		}
		return $this->fetch();
	}


    /**\
     * 推广业绩统计
     * @author lishibo 20190612
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
	public function performance(){

		$mobile = I('mobile');
		$email = I('email');
		if($mobile){
			$where =  "and b.mobile='$mobile'";
		}
		if($email){
			$where = "and b.email='$email'";
		}

        $list = array();

		//查询所有参与交易的用户
        $sql_user_ids =" SELECT DISTINCT(user_id) FROM ( ";
        $sql_user_ids .= " SELECT ";
        $sql_user_ids .= "            count(a.order_id) AS order_num,";
        $sql_user_ids .= "            sum(a.total_amount) AS amount,";
        $sql_user_ids .= "            a.user_id,";
        $sql_user_ids .= "            b.mobile,";
        $sql_user_ids .= "            b.email,";
        $sql_user_ids .= "            b.nickname,";
        $sql_user_ids .= "            b.`level`";
        $sql_user_ids .= "        FROM ";
        $sql_user_ids .= "            __PREFIX__order AS a ";
        $sql_user_ids .= "        LEFT JOIN __PREFIX__users AS b ON a.user_id = b.user_id  ";
        $sql_user_ids .= "        WHERE ";
        $sql_user_ids .= "	        a.add_time > $this->begin ";
        $sql_user_ids .= "           AND a.add_time < $this->end ";
        $sql_user_ids .= "           AND a.pay_status = 1 ";
        $sql_user_ids .= "           AND order_status IN (1, 2, 4) ";
        $sql_user_ids .= "        GROUP BY user_id ORDER BY b. LEVEL DESC ) AS USER ";
        $res_userids = DB::cache(true)->query($sql_user_ids);
        $res_userids_ = array();
        foreach ($res_userids as $val_res_id){
            array_push($res_userids_,$val_res_id['user_id']);
        }

        $distribut_allnum = 0;
        $distribut_amount_allnum = 0;
        foreach ($res_userids as $val){

            $tmp_user_id = $val['user_id'];//当前用户id
            $sql_distribut_user_ids = " SELECT user_id FROM __PREFIX__users  where FIND_IN_SET($tmp_user_id,all_leader) ";
            $distribut_user_ids = DB::cache(false)->query($sql_distribut_user_ids);

            $distribut_user_ids_ = array();
            foreach ($distribut_user_ids as $val_distribut_id){
                array_push($distribut_user_ids_,$val_distribut_id['user_id']);
            }

            array_push($distribut_user_ids_,$tmp_user_id);//当前用户以及所有下级ids
            $array_curr_ids = array_intersect($distribut_user_ids_,$res_userids_);//取交集

            $str_curr_ids = implode(',', $array_curr_ids);
            unset($distribut_user_ids);
            unset($distribut_user_ids_);
            unset($array_curr_ids);

           // echo "<pre>";print_r($str_curr_ids);echo "<pre>";

            if(strlen($str_curr_ids)>0){
                //查询当前用户以及下级交易数量与总额
                $sql_distribut_ids =" SELECT sum(USER. order_num) as distribut_num, sum(USER. amount) as distribut_amount FROM ( ";
                $sql_distribut_ids .= "        SELECT ";
                $sql_distribut_ids .= "            count(a.order_id) AS order_num,";
                $sql_distribut_ids .= "            sum(a.total_amount) AS amount,";
                $sql_distribut_ids .= "            a.user_id,";
                $sql_distribut_ids .= "            b.mobile,";
                $sql_distribut_ids .= "            b.email,";
                $sql_distribut_ids .= "            b.nickname,";
                $sql_distribut_ids .= "            b.`level`";
                $sql_distribut_ids .= "        FROM ";
                $sql_distribut_ids .= "            __PREFIX__order AS a ";
                $sql_distribut_ids .= "        LEFT JOIN __PREFIX__users AS b ON a.user_id = b.user_id  ";
                $sql_distribut_ids .= "        WHERE ";
                $sql_distribut_ids .= "	        a.add_time > $this->begin ";
                $sql_distribut_ids .= "           AND a.add_time < $this->end ";
                $sql_distribut_ids .= "           AND a.pay_status = 1 ";
                $sql_distribut_ids .= "           AND order_status IN (1, 2, 4) ";
                $sql_distribut_ids .= "           AND a.user_id in  ($str_curr_ids)"; //*
                $sql_distribut_ids .= "        GROUP BY user_id ORDER BY b. LEVEL DESC ) AS USER ";
                $curr_distribut = DB::cache(true)->query($sql_distribut_ids);

                //追加到个人信息
                $curr_user_sql ="select * from __PREFIX__users where user_id = $tmp_user_id";
                $curr_user = DB::cache(true)->query($curr_user_sql);
                $curr_user[0]['distribut_num'] = $curr_distribut[0]['distribut_num'];
                $curr_user[0]['distribut_amount'] = $curr_distribut[0]['distribut_amount'];

                $item = "";
                $item['user_id'] = $curr_user[0]['user_id'];
                $item['distribut_num'] = $curr_user[0]['distribut_num']?$curr_user[0]['distribut_num']:0;
                $item['distribut_amount'] = $curr_user[0]['distribut_amount']?$curr_user[0]['distribut_amount']:0;
                $item['nickname'] = $curr_user[0]['nickname']?$curr_user[0]['nickname']:"无名氏";
                if($item){
                    //将业绩用户展示
                    $distribut_allnum = $distribut_allnum + $item['distribut_num'];
                    $distribut_amount_allnum = $distribut_amount_allnum + $item['distribut_amount'];
                    array_push($list,$item);
                }
            }
        }

        $this->assign('list', array_sort($list,'distribut_amount'));
        $this->assign('distribut_allnum',$distribut_allnum);
        $this->assign('distribut_amount_allnum',$distribut_amount_allnum);

		return $this->fetch();
	}


    /**\
     * 推广业绩统计
     * @author lishibo 20190612
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
	public function performanceQuery(){

		$user_id = I('user_id')?(int)I('user_id'):0;
        $list = array();

        //查询所有参与交易的用户
        $sql_user_ids =" SELECT DISTINCT(user_id) FROM ( ";
        $sql_user_ids .= " SELECT ";
        $sql_user_ids .= "            count(a.order_id) AS order_num,";
        $sql_user_ids .= "            sum(a.total_amount) AS amount,";
        $sql_user_ids .= "            a.user_id,";
        $sql_user_ids .= "            b.mobile,";
        $sql_user_ids .= "            b.email,";
        $sql_user_ids .= "            b.nickname,";
        $sql_user_ids .= "            b.`level`";
        $sql_user_ids .= "        FROM ";
        $sql_user_ids .= "            __PREFIX__order AS a ";
        $sql_user_ids .= "        LEFT JOIN __PREFIX__users AS b ON a.user_id = b.user_id  ";
        $sql_user_ids .= "        WHERE ";
        $sql_user_ids .= "	        a.add_time > $this->begin ";
        $sql_user_ids .= "           AND a.add_time < $this->end ";
        $sql_user_ids .= "           AND a.pay_status = 1 ";
        $sql_user_ids .= "           AND order_status IN (1, 2, 4) ";
        $sql_user_ids .= "        GROUP BY user_id ORDER BY b. LEVEL DESC ) AS USER ";
        $res_userids = DB::cache(true)->query($sql_user_ids);
        $res_userids_ = array();
        foreach ($res_userids as $val_res_id){
            array_push($res_userids_,$val_res_id['user_id']);
        }

        $distribut_allnum = 0;
        $distribut_amount_allnum = 0;

            $tmp_user_id = $user_id;//当前用户id
            $sql_distribut_user_ids = " SELECT user_id FROM __PREFIX__users  where FIND_IN_SET($tmp_user_id,all_leader) ";
            $distribut_user_ids = DB::cache(false)->query($sql_distribut_user_ids);

            $distribut_user_ids_ = array();
            foreach ($distribut_user_ids as $val_distribut_id){
                array_push($distribut_user_ids_,$val_distribut_id['user_id']);
            }

            array_push($distribut_user_ids_,$tmp_user_id);//当前用户以及所有下级ids
            $array_curr_ids = array_intersect($distribut_user_ids_,$res_userids_);//取交集

            $str_curr_ids = implode(',', $array_curr_ids);
            unset($distribut_user_ids);
            unset($distribut_user_ids_);
            unset($array_curr_ids);

           // echo "<pre>";print_r($str_curr_ids);echo "<pre>";

            if(strlen($str_curr_ids)>0){
                //查询当前用户以及下级交易数量与总额
                $sql_distribut_ids =" SELECT sum(USER. order_num) as distribut_num, sum(USER. amount) as distribut_amount FROM ( ";
                $sql_distribut_ids .= "        SELECT ";
                $sql_distribut_ids .= "            count(a.order_id) AS order_num,";
                $sql_distribut_ids .= "            sum(a.total_amount) AS amount,";
                $sql_distribut_ids .= "            a.user_id,";
                $sql_distribut_ids .= "            b.mobile,";
                $sql_distribut_ids .= "            b.email,";
                $sql_distribut_ids .= "            b.nickname,";
                $sql_distribut_ids .= "            b.`level`";
                $sql_distribut_ids .= "        FROM ";
                $sql_distribut_ids .= "            __PREFIX__order AS a ";
                $sql_distribut_ids .= "        LEFT JOIN __PREFIX__users AS b ON a.user_id = b.user_id  ";
                $sql_distribut_ids .= "        WHERE ";
                $sql_distribut_ids .= "	        a.add_time > $this->begin ";
                $sql_distribut_ids .= "           AND a.add_time < $this->end ";
                $sql_distribut_ids .= "           AND a.pay_status = 1 ";
                $sql_distribut_ids .= "           AND order_status IN (1, 2, 4) ";
                $sql_distribut_ids .= "           AND a.user_id in  ($str_curr_ids)"; //*
                $sql_distribut_ids .= "        GROUP BY user_id ORDER BY b. LEVEL DESC ) AS USER ";
                $curr_distribut = DB::cache(true)->query($sql_distribut_ids);

                //追加到个人信息
                $curr_user_sql ="select * from __PREFIX__users where user_id = $tmp_user_id";
                $curr_user = DB::cache(true)->query($curr_user_sql);
                $curr_user[0]['distribut_num'] = $curr_distribut[0]['distribut_num'];
                $curr_user[0]['distribut_amount'] = $curr_distribut[0]['distribut_amount'];

                $item = "";
                $item['user_id'] = $curr_user[0]['user_id'];
                $item['distribut_num'] = $curr_user[0]['distribut_num']?$curr_user[0]['distribut_num']:0;
                $item['distribut_amount'] = $curr_user[0]['distribut_amount']?$curr_user[0]['distribut_amount']:0;
                $item['nickname'] = $curr_user[0]['nickname']?$curr_user[0]['nickname']:"无名氏";
                if($item){
                    //将业绩用户展示
                    $distribut_allnum = $distribut_allnum + $item['distribut_num'];
                    $distribut_amount_allnum = $distribut_amount_allnum + $item['distribut_amount'];
                    array_push($list,$item);
                }
            }


        $this->assign('list', array_sort($list,'distribut_amount'));
        $this->assign('distribut_allnum',$distribut_allnum);
        $this->assign('distribut_amount_allnum',$distribut_amount_allnum);

		return $this->fetch();
	}


    /**
     * 二维数组排序
     * @author  lishibo 20190612
     * @param $arr
     * @param $keys
     * @param string $type
     * @return array
     */
    function array_sort($arr,$keys,$type='asc'){
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }


	public function saleList(){		 
		$cat_id = I('cat_id',0);
		$brand_id = I('brand_id',0);
		$where = "where b.add_time>$this->begin and b.add_time<$this->end ";
		if($cat_id>0){
			$where .= " and g.cat_id=$cat_id";
			$this->assign('cat_id',$cat_id);
		}
		if($brand_id>0){
			$where .= " and g.brand_id=$brand_id";
			$this->assign('brand_id',$brand_id);
		}
                
		$sql2 = "select count(*) as tnum from __PREFIX__order_goods as a left join __PREFIX__order as b on a.order_id=b.order_id ";
		$sql2 .= " left join __PREFIX__goods as g on a.goods_id = g.goods_id $where";
		$total = DB::query($sql2);
		$count =  $total[0]['tnum'];
		$Page = new Page($count,20);
		$show = $Page->show();                
                
		$sql = "select a.*,b.order_sn,b.shipping_name,b.pay_name,b.add_time from __PREFIX__order_goods as a left join __PREFIX__order as b on a.order_id=b.order_id ";
		$sql .= " left join __PREFIX__goods as g on a.goods_id = g.goods_id $where ";
		$sql .= "  order by add_time desc limit {$Page->firstRow},{$Page->listRows}";
		$res = DB::query($sql);
		$this->assign('list',$res);		
		$this->assign('page',$show);
		
                $GoodsLogic = new GoodsLogic();        
                $brandList = $GoodsLogic->getSortBrands();
                $categoryList = $GoodsLogic->getSortCategory();
                $this->assign('categoryList',$categoryList);
                $this->assign('brandList',$brandList);
                return $this->fetch();
	}
	
	public function user(){
		$today = strtotime(date('Y-m-d'));
		$month = strtotime(date('Y-m-01'));
		$user['today'] = D('users')->where("reg_time>$today")->count();//今日新增会员
		$user['month'] = D('users')->where("reg_time>$month")->count();//本月新增会员
		$user['total'] = D('users')->count();//会员总数
		$user['user_money'] = D('users')->sum('user_money');//会员余额总额
		$res = M('order')->cache(true)->distinct(true)->field('user_id')->select();
		$user['hasorder'] = count($res);
		$this->assign('user',$user);
		$sql = "SELECT COUNT(*) as num,FROM_UNIXTIME(reg_time,'%Y-%m-%d') as gap from __PREFIX__users where reg_time>$this->begin and reg_time<$this->end group by gap";
		$new = DB::query($sql);//新增会员趋势
		foreach ($new as $val){
			$arr[$val['gap']] = $val['num'];
		}
		
		for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
			$brr[] = empty($arr[date('Y-m-d',$i)]) ? 0 : $arr[date('Y-m-d',$i)];
			$day[] = date('Y-m-d',$i);
		}		
		$result = array('data'=>$brr,'time'=>$day);
		$this->assign('result',json_encode($result));					
		return $this->fetch();
	}
	
	//财务统计
	public function finance(){
		$sql = "SELECT sum(b.goods_num*b.member_goods_price) as goods_amount,sum(a.shipping_price) as shipping_amount,sum(b.goods_num*b.cost_price) as cost_price,";
		$sql .= "sum(a.coupon_price) as coupon_amount,FROM_UNIXTIME(a.add_time,'%Y-%m-%d') as gap from  __PREFIX__order a left join __PREFIX__order_goods b on a.order_id=b.order_id ";
		$sql .= " where a.add_time>$this->begin and a.add_time<$this->end AND a.pay_status=1 and a.shipping_status=1 and b.is_send=1 group by gap order by a.add_time";
		$res = DB::cache(true)->query($sql);//物流费,交易额,成本价
		
		foreach ($res as $val){
			$arr[$val['gap']] = $val['goods_amount'];
			$brr[$val['gap']] = $val['cost_price'];
			$crr[$val['gap']] = $val['shipping_amount'];
			$drr[$val['gap']] = $val['coupon_amount'];
		}
			
		for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
			$date = $day[] = date('Y-m-d',$i);
			$tmp_goods_amount = empty($arr[$date]) ? 0 : $arr[$date];
			$tmp_cost_amount = empty($brr[$date]) ? 0 : $brr[$date];
			$tmp_shipping_amount = empty($crr[$date]) ? 0 : $crr[$date];
			$tmp_coupon_amount = empty($drr[$date]) ? 0 : $drr[$date];
			
			$goods_arr[] = $tmp_goods_amount;
			$cost_arr[] = $tmp_cost_amount;
			$shipping_arr[] = $tmp_shipping_amount;
			$coupon_arr[] = $tmp_coupon_amount;
			$list[] = array('day'=>$date,'goods_amount'=>$tmp_goods_amount,'cost_amount'=>$tmp_cost_amount,
					'shipping_amount'=>$tmp_shipping_amount,'coupon_amount'=>$tmp_coupon_amount,'end'=>date('Y-m-d',$i+24*60*60));
		}
                rsort($list);
		$this->assign('list',$list);
		$result = array('goods_arr'=>$goods_arr,'cost_arr'=>$cost_arr,'shipping_arr'=>$shipping_arr,'coupon_arr'=>$coupon_arr,'time'=>$day);
		$this->assign('result',json_encode($result));
		return $this->fetch();
	}
	
}