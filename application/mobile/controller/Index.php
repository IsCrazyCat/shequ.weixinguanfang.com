<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 聊城市博商网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.boshang3710.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * $Author: lihongshun 2016-01-09
 */
namespace app\mobile\controller;
use app\home\logic\UsersLogic;
use Think\Db;
class Index extends MobileBase {

	 /*
    * 初始化操作
    */
    public function _initialize()
    {
        parent::_initialize();
        if (session('?user')) {
            $user = session('user');
            $user = M('users')->where("user_id", $user['user_id'])->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user', $user); //存储用户信息
        }
        $nologin = array(
            'login', 'pop_login', 'do_login', 'logout', 'verify', 'set_pwd', 'finished',
            'verifyHandle', 'reg', 'send_sms_reg_code', 'find_pwd', 'check_validate_code',
            'forget_pwd', 'check_captcha', 'check_username', 'send_validate_code', 'express',
        );
//        if (!$this->user_id && !in_array(ACTION_NAME, $nologin)) {
//            header("location:" . U('Mobile/User/login'));
//            exit;
//        }

        $order_status_coment = array(
            'WAITPAY' => '待付款 ', //订单查询状态 待支付
            'WAITSEND' => '待发货', //订单查询状态 待发货
            'WAITRECEIVE' => '待收货', //订单查询状态 待收货
            'WAITCCOMMENT' => '待评价', //订单查询状态 待评价
        );
        $this->assign('order_status_coment', $order_status_coment);
    }
    /*
20190816
    public function index(){
        $indexAds = M('ad')->where("pid=2 and enabled=1")->order('orderby DESC')->limit(3)->cache(true,TPSHOP_CACHE_TIME)->select();//首页顶部轮播
        $this->assign('indexAds',$indexAds);

        $cat_list = M('goods_category')->where("parent_id = 2 and level = 3 ")->order('`sort_order` asc')->cache(true,TPSHOP_CACHE_TIME)->select(); //获取指定二级下三级分类
        foreach($cat_list as $k => $v){
            $cat_list[$k]['goodslist'] = M('Goods')->where("cat_id  = ".$v['id']." and is_on_sale = 1")->order('`sort` asc')->select();
        }
        $this->assign('cat_list',$cat_list);
      //    echo "<pre>";print_r($cat_list);echo "<pre>";
        $this->assign('biaoji',3);
       return $this->fetch();
    }
*/
    public function index(){

        /*
            //获取微信配置
            $wechat_list = M('wx_user')->select();
            $wechat_config = $wechat_list[0];
            $this->weixin_config = $wechat_config;
            // 微信Jssdk 操作类 用分享朋友圈 JS
            $jssdk = new \Mobile\Logic\Jssdk($this->weixin_config['appid'], $this->weixin_config['appsecret']);
            $signPackage = $jssdk->GetSignPackage();
            print_r($signPackage);
        */
        $hot_goods = M('goods')->where("is_hot=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,TPSHOP_CACHE_TIME)->select();//首页热卖商品
        $thems = M('goods_category')->where('level=1')->order('sort_order')->limit(9)->cache(true,TPSHOP_CACHE_TIME)->select();
        $this->assign('thems',$thems);
        $this->assign('hot_goods',$hot_goods);
        $favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,TPSHOP_CACHE_TIME)->select();//首页推荐商品
        $this->assign('favourite_goods',$favourite_goods);

        /**超级大咖名单（暂时包含未支付成功的，为了增加数量显示)**/
        $dklist = M('users')->limit(20)->cache(true,TPSHOP_CACHE_TIME)->select();
        foreach ($dklist as& $val){
            $val['nickname']=substr($val['nickname'],0,6)."*****";
            $val['reg_time']=time()-9999;
        }

        $this->assign('dklist',$dklist);
        /**超级大咖名单**/


        /**dki影视**/
        $Video =  M('Video');
        $catslist = array();
        $catslstmp =  M('video_cat')->where('parent_id = 2')->order("cat_id asc")->select();

        if($catslstmp){
            foreach ($catslstmp as $val){
                $cat_id = (int)$val['cat_id'];
                $res = M('Video')->where("cat_id = $cat_id ")->order('video_id asc')->select();
                if($res){ //创建二维数组
                    $val['videolist'] =$res;
                    $catslist[] = $val;
                    unset($res);
                }
            }
        }
        $this->assign('catslist',$catslist);
        /**dki影视**/
        return $this->fetch();
    }
    public function pifaqu(){


        $indexAds = M('ad')->where("pid=2 and enabled=1")->order('orderby DESC')->limit(3)->cache(true,TPSHOP_CACHE_TIME)->select();//首页顶部轮播
        $this->assign('indexAds',$indexAds);

        $cat_list = M('goods_category')->where("parent_id = 2 and level = 3 ")->order('`sort_order` asc')->cache(true,TPSHOP_CACHE_TIME)->select(); //获取指定二级下三级分类
        foreach($cat_list as $k => $v){
            $cat_list[$k]['goodslist'] = M('Goods')->where("cat_id  = ".$v['id']." and is_on_sale = 1")->order('`sort` asc')->select();
        }
        $this->assign('cat_list',$cat_list);
        //    echo "<pre>";print_r($cat_list);echo "<pre>";
        $this->assign('biaoji',4);
        return $this->fetch();
    }
    /**
     * 分类列表显示
     */
    public function categoryList(){
        return $this->fetch();
    }

    /**
     * 模板列表
     */
    public function mobanlist(){
        $arr = glob("D:/wamp/www/svn_tpshop/mobile--html/*.html");
        foreach($arr as $key => $val)
        {
            $html = end(explode('/', $val));
            echo "<a href='http://www.php.com/svn_tpshop/mobile--html/{$html}' target='_blank'>{$html}</a> <br/>";            
        }        
    }
	
   
    /**
     * 商品列表页
     */
    public function goodsList(){
        $id = I('get.id/d',0); // 当前分类id
        $lists = getCatGrandson($id);
        $this->assign('lists',$lists);
        return $this->fetch();
    }
	
    
    /**
     * 会员生日祝福短信
     */
    public function huiyuanshengrizhufu(){
		
		
		$user = M('users')->where("user_id", '3')->find();
		if($user){
			$user_name = $user['username'];
			$sender = $user['mobile'];
			$params = array('user_name'=>$user_name  );
			$resp = sendSms("11", $sender, $params, $user['user_id'] );
		}					
		
        return $this->fetch();
    }
	
    
    public function ajaxGetMore(){
    	$p = I('p/d',1);

    	$favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1 and cat_id=3")->order('goods_id DESC')->page($p,C('PAGESIZE'))->cache(true,TPSHOP_CACHE_TIME)->select();//首页推荐商品
    	$this->assign('favourite_goods',$favourite_goods);
    	return $this->fetch();
    }
    public function ajaxGetpifa(){
        $p = I('p/d',1);

        $favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1 and cat_id=4")->order('goods_id DESC')->page($p,C('PAGESIZE'))->cache(true,TPSHOP_CACHE_TIME)->select();//首页推荐商品
        $this->assign('favourite_goods',$favourite_goods);
        return $this->fetch();
    }
    public function ajaxtejiaqu(){
        $p = I('p/d',1);
        $favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1 and cat_id=26")->order('goods_id DESC')->page($p,C('PAGESIZE'))->cache(true,TPSHOP_CACHE_TIME)->select();//首页推荐商品

        $this->assign('favourite_goods',$favourite_goods);
        return $this->fetch();
    }
    //微信Jssdk 操作类 用分享朋友圈 JS
    public function ajaxGetWxConfig(){
    	$askUrl = I('askUrl');//分享URL
    	$weixin_config = M('wx_user')->find(); //获取微信配置
    	$jssdk = new \app\mobile\logic\Jssdk($weixin_config['appid'], $weixin_config['appsecret']);
    	$signPackage = $jssdk->GetSignPackage(urldecode($askUrl));
    	if($signPackage){
    		$this->ajaxReturn($signPackage,'JSON');
    	}else{
    		return false;
    	}
    }
       
}