<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 聊城市博商网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.boshang3710.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: JY
 * Date: 2015-09-23
 */

namespace app\home\controller;
use app\home\logic\UsersLogic;
use think\Db;
use think\Session;
use think\Controller;
use think\Verify;
use think\Cookie;

class Api extends Base {
    public  $send_scene;
    
    public function _initialize() {
        parent::_initialize();
    }
    /*
     * 获取地区
     */
    public function getRegion(){
        $parent_id = I('get.parent_id/d');
        $selected = I('get.selected',0);        
        $data = M('region')->where("parent_id",$parent_id)->select();
        $html = '';
        if($data){
            foreach($data as $h){
            	if($h['id'] == $selected){
            		$html .= "<option value='{$h['id']}' selected>{$h['name']}</option>";
            	}
                $html .= "<option value='{$h['id']}'>{$h['name']}</option>";
            }
        }
        echo $html;
    }
    

    public function getTwon(){
    	$parent_id = I('get.parent_id/d');
    	$data = M('region')->where("parent_id",$parent_id)->select();
    	$html = '';
    	if($data){
    		foreach($data as $h){
    			$html .= "<option value='{$h['id']}'>{$h['name']}</option>";
    		}
    	}
    	if(empty($html)){
    		echo '0';
    	}else{
    		echo $html;
    	}
    }

    /**
     * 获取省
     */
    public function getProvince()
    {
        $province = Db::name('region')->field('id,name')->where(array('level' => 1))->cache(true)->select();
        $res = array('status' => 1, 'msg' => '获取成功', 'result' => $province);
        exit(json_encode($res));
    }

    /**
     * 获取市或者区
     */
    public function getRegionByParentId()
    {
        $parent_id = input('parent_id');
        $res = array('status' => 0, 'msg' => '获取失败，参数错误', 'result' => '');
        if($parent_id){
            $region_list = Db::name('region')->field('id,name')->where(['parent_id'=>$parent_id])->select();
            $res = array('status' => 1, 'msg' => '获取成功', 'result' => $region_list);
        }
        exit(json_encode($res));
    }
    
    /*
     * 获取地区
     */
    public function get_category(){
        $parent_id = I('get.parent_id/d'); // 商品分类 父id
            $list = M('goods_category')->where("parent_id", $parent_id)->select();
        
        foreach($list as $k => $v)
            $html .= "<option value='{$v['id']}'>{$v['name']}</option>";        
        exit($html);
    }  
    
    /*短信宝*/
    public function duanxinbaby(){
        $mobile=I('send');
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://www.smsbao.com/"; //短信网关
        $user = "nlgliuyang"; //短信平台帐号
        $pass = md5("3832050"); //短信平台密码
        $suiji=rand(1000,9999);
        session($mobile,$suiji);
        $content="【社群新零售】注册验证码".$suiji;//要发送的短信内容
        $phone = $mobile;
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
        $result =file_get_contents($sendurl) ;
        $res['status'] = $result;
        $res['msg'] = $statusStr[$result];
        ajaxReturn($res);
    }
    /**
     * 前端发送短信方法: APP/WAP/PC 共用发送方法
     */
    public function send_validate_code(){
         
        $this->send_scene = C('SEND_SCENE');
        
        $type = I('type');
        $scene = I('scene');    //发送短信验证码使用场景
        $mobile = I('mobile');
        $sender = I('send');
        $verify_code = I('verify_code');
        $mobile = !empty($mobile) ?  $mobile : $sender ;
        $session_id = I('unique_id' , session_id());
        session("scene" , $scene);
		
        
        //注册
        if($scene == 1 && !empty($verify_code)){
            $verify = new Verify();
            if (!$verify->check($verify_code, 'user_reg')) {
                ajaxReturn(array('status'=>-1,'msg'=>'图像验证码错误'));
            }
        }
        if($type == 'email'){
            //发送邮件验证码
            $logic = new UsersLogic();
            $res = $logic->send_email_code($sender);
            ajaxReturn($res);
        }else{
            //发送短信验证码
             $res = checkEnableSendSms($scene);
            if($res['status'] != 1){
                ajaxReturn($res);
            }
            //判断是否存在验证码
            $data = M('sms_log')->where(array('mobile'=>$mobile,'session_id'=>$session_id, 'status'=>1))->order('id DESC')->find();
            //获取时间配置
            $sms_time_out = tpCache('sms.sms_time_out');
            $sms_time_out = $sms_time_out ? $sms_time_out : 120;
            //120秒以内不可重复发送
            if($data && (time() - $data['add_time']) < $sms_time_out){
                $return_arr = array('status'=>-1,'msg'=>$sms_time_out.'秒内不允许重复发送');
                ajaxReturn($return_arr);
            }
            //随机一个验证码
            $code = rand(1000, 9999);

            $user = session('user');
            if ($scene == 6){
                 
                if(!$user['user_id']){
                    //登录超时
                    ajaxReturn(array('status'=>-1,'msg'=>'登录超时'));
                }
                $params = array('code'=>$code);
                 
                if($user['nickname']){
                    $params['user_name'] = $user['nickname'];
                }
            }
            $params['code'] =$code;
            
			//博商网络 短信发送
			
			$duoshaoyuan = '0';
			$duoshaoyongjin = '0';

            $params['duoshaoyuan'] = $duoshaoyuan;
            $params['duoshaoyongjin'] = $duoshaoyongjin;
            //发送短信
            $resp = sendSms($scene , $mobile , $params, $session_id);

            if($resp['status'] == 1){
                //发送成功, 修改发送状态位成功
                M('sms_log')->where(array('mobile'=>$mobile,'code'=>$code,'session_id'=>$session_id , 'status' => 0))->save(array('status' => 1));
                $return_arr = array('status'=>1,'msg'=>'发送成功,请注意查收');
            }else{
                $return_arr = array('status'=>-1,'msg'=>'发送失败'.$resp['msg']);
            }
            ajaxReturn($return_arr);

        }
    }
    
    /**
     * 验证短信验证码: APP/WAP/PC 共用发送方法
     */
    public function check_validate_code(){
          
        $code = I('post.code');
        $mobile = I('mobile');
        $send = I('send');
        $sender = empty($mobile) ? $send : $mobile; 
        $type = I('type');
        $session_id = I('unique_id', session_id());
        $scene = I('scene', -1);

        $logic = new UsersLogic();
        $res = $logic->check_validate_code($code, $sender, $type ,$session_id, $scene);
        ajaxReturn($res);
    }
    
    /**
     * 检测手机号是否已经存在
     */
    public function issetMobile()
    {
      $mobile = I("mobile",'0');  
      $users = M('users')->where('mobile',$mobile)->find();
      if($users)
          exit ('1');
      else 
          exit ('0');      
    }


    /**
     * 检测手机号是否已经存在
     */
    public function myissetMobile()
    {
      $mobile = I("mobile",'0');  
      $users = M('users')->where('mobile',$mobile)->find();
      if($users)
          exit ($users['nickname']);
      else 
          exit ('0');      
    }
	
	
    /**
     * 返回当前用户的可用余额 
     */
	public function fanhuizhanghuyve()
    {
      $mobile = I("mobile",'0');  
      $users = M('users')->where('mobile',$mobile)->find();
	  $tjrid = 0;
	  $tjrname = '无';
	  $tjrdjid = 0;
	  $tjrdjname = '无';
	  $usermoney = 0;
	  $userid = 0;
	  $username = '无';
	  
	  $address = Array();
	  $province = Array();
	  $city = Array();
	  $district = Array();
	  $twon = Array();
	   
	  
      if($users) {
		  $userid = $users['user_id'];
		  $username = $users['username'];
		  $usermoney = $users['user_money'];
		  $userlevel = $users['level'];
		  
		  
	  	  if ( (int)$userid > 0 ) {
//        	$address = M('user_address')->where(array('is_default'=>1,'user_id'=> $userid))->field('address_id,consignee,province,city,district,twon,address')->find();
//		    if($address) {
//			} else {
//				$address = M('user_address')->where(array('is_default'=>0,'user_id'=> $userid))->field('address_id,consignee,province,city,district,twon,address')->find();
//			}
			$address = M('user_address')->where(array('user_id'=> $userid))->field('address_id,consignee,province,city,district,twon,address')->find();
			if($address) {
				
				$city = M('region')->where(array('parent_id'=>$address['province'],'level'=> 2))->field('id,name')->select();
				$district = M('region')->where(array('parent_id'=>$address['city'],'level'=> 3))->field('id,name')->select();
				if($address['twon']){
					$twon = M('region')->where(array('parent_id'=>$address['district'],'level'=>4))->field('id,name')->select();
				}
				
				$mycity='<option  value="0">请选择</option>';
				foreach($city as $k=>$val){ 
					$mycity.= '<option  value="'. $val["id"] .'">' . $val["name"] . '</option>';
				} 
				
				$mydistrict='<option  value="0">请选择</option>';
				foreach($district as $k=>$val){ 
					$mydistrict.= '<option  value="'. $val["id"] .'">' . $val["name"] . '</option>';
				} 
				
				$mytwon='';
				foreach($twon as $k=>$val){ 
					$mytwon.= '<option  value="'. $val["id"] .'">' . $val["name"] . '</option>';
				} 
				//$myaddress = $address['addressid'].'|'.$address['consignee'].'|'.$address['province'].'|'.$address['city'].'|'.$address['district'].'|'.$address['twon'].'|'.$address['address'];      
				$myaddress = implode("|",$address);
			} else {
				$myaddress = "";
			}
			
			//$province = implode("|",$province);
			//$city = implode("|",$city);
			//$district = implode("|",$district);
			//$twon = implode("|",$twon);

	  	  	$tjrid = $users['first_leader'];
			$tjr = M('users')->where('user_id', $tjrid)->find();
			if ($tjr) {
			  $tjrname = $tjr['username'];
			  $tjrdjid = $tjr['user_id'];
			  $tjrdj = M('user_level')->where('level_id',$tjr['level'])->find();
			  if ($tjrdj) {
				  $tjrdjid = $tjrdj['level_id'];
				  $tjrdjname = $tjrdj['level_name'];
			  }
			}
			
			
			  $brrdj = M('user_level')->where('level_id',$userlevel)->find();
			  if ($brrdj) {
				  $user_level_name = $brrdj['level_name'];
				  $user_discount = $brrdj['discount'];
				  $user_tjrfy = $brrdj['tjrfy'];
				  $user_ssjfy = $brrdj['ssjfy'];
			  }
			
			
		  }
          exit ($usermoney.'^'.$tjrid.'^'.$tjrname.'^'.$tjrdjid.'^'.$tjrdjname.'^'.$userid.'^'.$username.'^'.$user_level_name.'^'.$user_discount.'^'.$user_tjrfy.'^'.$user_ssjfy.'^'.$myaddress.'^'.$mycity.'^'.$mydistrict.'^'.$mytwon);
		  
          //exit ($usermoney.'^'.$tjrid.'^'.$tjrname.'^'.$tjrdjid.'^'.$tjrdjname.'^'.$userid.'^'.$username.'^'.$address.'^'.$mycity.'^'.$mydistrict.'^'.$mytwon);
      } else { 
          exit ('0');
	  }      
    }
	
	
	 /**
     * 返回当前用户的可用余额 
     */
	public function fanhuizhanghuyve2()
    {
      $xingming = I("xingming",'0');  
      $users = M('users')->where('username',$xingming)->find();
	  $tjrid = 0;
	  $tjrname = '无';
	  $tjrdjid = 0;
	  $tjrdjname = '无';
	  $usermoney = 0;
	  $userid = 0;
	  $username = '无';
	  $usermobile = '';
	  
	  $address = Array();
	  $province = Array();
	  $city = Array();
	  $district = Array();
	  $twon = Array();
	   
	  
      if($users) {
		  $userid = $users['user_id'];
		  $username = $users['username'];
		  $usermoney = $users['user_money'];
		  $userlevel = $users['level'];
		  $usermobile = $users['mobile'];
		  
	  	  if ( (int)$userid > 0 ) {
//        	$address = M('user_address')->where(array('is_default'=>1,'user_id'=> $userid))->field('address_id,consignee,province,city,district,twon,address')->find();
//		    if($address) {
//			} else {
//				$address = M('user_address')->where(array('is_default'=>0,'user_id'=> $userid))->field('address_id,consignee,province,city,district,twon,address')->find();
//			}
			$address = M('user_address')->where(array('user_id'=> $userid))->field('address_id,consignee,province,city,district,twon,address')->find();
			if($address) {
				
				$city = M('region')->where(array('parent_id'=>$address['province'],'level'=> 2))->field('id,name')->select();
				$district = M('region')->where(array('parent_id'=>$address['city'],'level'=> 3))->field('id,name')->select();
				if($address['twon']){
					$twon = M('region')->where(array('parent_id'=>$address['district'],'level'=>4))->field('id,name')->select();
				}
				
				$mycity='<option  value="0">请选择</option>';
				foreach($city as $k=>$val){ 
					$mycity.= '<option  value="'. $val["id"] .'">' . $val["name"] . '</option>';
				} 
				
				$mydistrict='<option  value="0">请选择</option>';
				foreach($district as $k=>$val){ 
					$mydistrict.= '<option  value="'. $val["id"] .'">' . $val["name"] . '</option>';
				} 
				
				$mytwon='';
				foreach($twon as $k=>$val){ 
					$mytwon.= '<option  value="'. $val["id"] .'">' . $val["name"] . '</option>';
				} 
				//$myaddress = $address['addressid'].'|'.$address['consignee'].'|'.$address['province'].'|'.$address['city'].'|'.$address['district'].'|'.$address['twon'].'|'.$address['address'];      
				$myaddress = implode("|",$address);
			} else {
				$myaddress = "";
			}
			
			//$province = implode("|",$province);
			//$city = implode("|",$city);
			//$district = implode("|",$district);
			//$twon = implode("|",$twon);

	  	  	$tjrid = $users['first_leader'];
			$tjr = M('users')->where('user_id', $tjrid)->find();
			if ($tjr) {
			  
			  $tjrname = $tjr['username'];
			  $tjrdjid = $tjr['user_id'];
			  $tjrdj = M('user_level')->where('level_id',$tjr['level'])->find();
			  if ($tjrdj) {
				  $tjrdjid = $tjrdj['level_id'];
				  $tjrdjname = $tjrdj['level_name'];
			  }
			}
			
			  $brrdj = M('user_level')->where('level_id',$userlevel)->find();
			  if ($brrdj) {
				  $user_level_name = $brrdj['level_name'];
				  $user_discount = $brrdj['discount'];
				  $user_tjrfy = $brrdj['tjrfy'];
				  $user_ssjfy = $brrdj['ssjfy'];
			  }
			
			
		  }
          exit ($usermoney.'^'.$tjrid.'^'.$tjrname.'^'.$tjrdjid.'^'.$tjrdjname.'^'.$userid.'^'.$username.'^'.$user_level_name.'^'.$user_discount.'^'.$user_tjrfy.'^'.$user_ssjfy.'^'.$myaddress.'^'.$mycity.'^'.$mydistrict.'^'.$mytwon.'^'.$usermobile);
		  
          //exit ($usermoney.'^'.$tjrid.'^'.$tjrname.'^'.$tjrdjid.'^'.$tjrdjname.'^'.$userid.'^'.$username.'^'.$address.'^'.$mycity.'^'.$mydistrict.'^'.$mytwon);
      } else { 
          exit ('0');
	  }      
    }

	

    public function issetMobileOrEmail()
    {
        $mobile = I("mobile",'0');        
        $users = M('users')->where("email",$mobile)->whereOr('mobile',$mobile)->find();
        if($users)
            exit ('1');
        else
            exit ('0');
    }
    /**
     * 查询物流
     */
    public function queryExpress()
    {
        $shipping_code = input('shipping_code');
        $invoice_no = input('invoice_no');
        if(empty($shipping_code) || empty($invoice_no)){
            return json(['status'=>0,'message'=>'参数有误','result'=>'']);
        }
        return json(queryExpress($shipping_code,$invoice_no));
    }
    
    /**
     * 检查订单状态
     */
    public function check_order_pay_status()
    {
        $order_id = I('order_id/d');
        if(empty($order_id)){
            $res = ['message'=>'参数错误','status'=>-1,'result'=>''];
            $this->AjaxReturn($res);
        }
        $order = M('order')->field('pay_status')->where(['order_id'=>$order_id])->find();
        if($order['pay_status'] != 0){
            $res = ['message'=>'已支付','status'=>1,'result'=>$order];
        }else{
            $res = ['message'=>'未支付','status'=>0,'result'=>$order];
        }
        $this->AjaxReturn($res);
    }

    /**
     * 广告位js
     */
    public function ad_show()
    {
        $pid = I('pid/d',1);
        $where = array(
            'pid'=>$pid,
            'enable'=>1,
            'start_time'=>array('lt',strtotime(date('Y-m-d H:00:00'))),
            'end_time'=>array('gt',strtotime(date('Y-m-d H:00:00'))),
        );
        $ad = D("ad")->where($where)->order("orderby desc")->cache(true,TPSHOP_CACHE_TIME)->find();
        $this->assign('ad',$ad);
        return $this->fetch();
    }
    /**
     *  搜索关键字
     * @return array
     */
    public function searchKey(){
        $searchKey = input('key');
        $searchKeyList = Db::name('search_word')
            ->where('keywords','like',$searchKey.'%')
            ->whereOr('pinyin_full','like',$searchKey.'%')
            ->whereOr('pinyin_simple','like',$searchKey.'%')
            ->limit(10)
            ->select();
        if($searchKeyList){
            return ['status'=>1,'msg'=>'搜索成功','result'=>$searchKeyList];
        }else{
            return ['status'=>0,'msg'=>'没记录','result'=>$searchKeyList];
        }
    }

    /**
     * 根据ip设置获取的地区来设置地区缓存
     */
    public function doCookieArea()
    {
//        $ip = '183.147.30.238';//测试ip
        $address = input('address/a',[]);
        if(empty($address) || empty($address['province'])){
            $this->setCookieArea();
            return;
        }
        $province_id = Db::name('region')->where(['level' => 1, 'name' => ['like', '%' . $address['province'] . '%']])->limit('1')->value('id');
        if(empty($province_id)){
            $this->setCookieArea();
            return;
        }
        if (empty($address['city'])) {
            $city_id = Db::name('region')->where(['level' => 2, 'parent_id' => $province_id])->limit('1')->order('id')->value('id');
        } else {
            $city_id = Db::name('region')->where(['level' => 2, 'parent_id' => $province_id, 'name' => ['like', '%' . $address['city'] . '%']])->limit('1')->value('id');
        }
        if (empty($address['district'])) {
            $district_id = Db::name('region')->where(['level' => 3, 'parent_id' => $city_id])->limit('1')->order('id')->value('id');
        } else {
            $district_id = Db::name('region')->where(['level' => 3, 'parent_id' => $city_id, 'name' => ['like', '%' . $address['district'] . '%']])->limit('1')->value('id');
        }
        $this->setCookieArea($province_id, $city_id, $district_id);
    }

    /**
     * 设置地区缓存
     * @param $province_id
     * @param $city_id
     * @param $district_id
     */
    private function setCookieArea($province_id = 1, $city_id = 2, $district_id = 3)
    {
        Cookie::set('province_id', $province_id);
        Cookie::set('city_id', $city_id);
        Cookie::set('district_id', $district_id);
    }
    
}