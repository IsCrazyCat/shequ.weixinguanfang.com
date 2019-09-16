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
 * Author: 博商网络
 * Date: 2015-09-09
 */

namespace app\home\logic;

use think\Model;
use think\Page;
use think\db;
use app\home\model\UserAddress;
use app\common\logic\CommentLogic;

/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
class UsersLogic extends Model
{
    /*
     * 登陆
     */
    public function login($username,$password){
    	$result = array();
        if(!$username || !$password)
           $result= array('status'=>0,'msg'=>'请填写账号或密码');
        $user = M('users')->where("mobile",$username)->whereOr('email',$username)->find();
        if(!$user){
           $result = array('status'=>-1,'msg'=>'账号不存在!');
        }elseif(encrypt($password) != $user['password']){
           $result = array('status'=>-2,'msg'=>'密码错误!');
        }elseif($user['is_lock'] == 1){
           $result = array('status'=>-3,'msg'=>'账号未付费或被锁定！！！');
        }else{
            //查询用户信息之后, 查询用户的登记昵称
            $levelId = $user['level'];
            $levelName = M("user_level")->where("level_id", $levelId)->getField("level_name");
            $user['level_name'] = $levelName;
          
           $result = array('status'=>1,'msg'=>'登陆成功','result'=>$user);
        }
        return $result;
    }

    /*
     * app端登陆
     */
    public function app_login($username, $password, $capache, $push_id=0)
    {
    	$result = array();
        if(!$username || !$password)
           $result= array('status'=>0,'msg'=>'请填写账号或密码');
        $user = M('users')->where("mobile|email","=",$username)->find();
        if(!$user){
           $result = array('status'=>-1,'msg'=>'账号不存在!');
        }elseif($password != $user['password']){
           $result = array('status'=>-2,'msg'=>'密码错误!');
        }elseif($user['is_lock'] == 1){
           $result = array('status'=>-3,'msg'=>'账号未付费或被锁定！！！');
        }else{
            //查询用户信息之后, 查询用户的登记昵称
            $levelId = $user['level'];
            $levelName = M("user_level")->where("level_id", $levelId)->getField("level_name");
            $user['level_name'] = $levelName;            
            $user['token'] = md5(time().mt_rand(1,999999999));
            M('users')->where("user_id", $user['user_id'])->save(array('token'=>$user['token'],'last_login'=>time(), 'push_id' => $push_id));
            $result = array('status'=>1,'msg'=>'登陆成功','result'=>$user);
        }
        return $result;
    }    

    /*
     * app端登出
     */
    public function app_logout($token = '')
    {
        if (empty($token)){
            ajaxReturn(['status'=>-100, 'msg'=>'已经退出账户']);
        }

        $user = M('users')->where("token", $token)->find();
        if (empty($user)) {
            ajaxReturn(['status'=>-101, 'msg'=>'用户不在登录状态']);
        }

        M('users')->where(["user_id" => $user['user_id']])->save(['last_login' => 0, 'token' => '']);
        session(null);

        return ['status'=>1, 'msg'=>'退出账户成功'];;
    }
    
    //绑定账号
    public function oauth_bind($data = array()){
    	$user = session('user');
    	if(empty($user['openid'])){
    		if(M('users')->where(array('openid'=>$data['openid']))->count()>0){
    			return array('status'=>-1,'msg'=>'您的'.$data['oauth'].'账号已经绑定过账号');
    		}else{
    			 M('users')->where(array('user_id'=>$user['user_id']))->save($data);
    			 return array('status'=>1,'msg'=>'绑定成功','result'=>$data);
    		}
    	}else{
    		return array('status'=>-1,'msg'=>'您的账号已绑定过，请不要重复绑定');
    	}
    }
    /*
     * 第三方登录
     * 微信自动登录，审核等级关系。
     */
    public function thirdLogin($data=array()){
        $openid = $data['openid']; //第三方返回唯一标识
        $oauth = $data['oauth']; //来源
        if(!$openid || !$oauth)
            return array('status'=>-1,'msg'=>'参数有误','result'=>'');
        //获取用户信息
        if(isset($data['unionid'])){
        	$map['unionid'] = $data['unionid'];
        	$user = get_user_info($data['unionid'],4,$oauth);
        }else{
        	$user = get_user_info($openid,3,$oauth);
        }  
        if(!$user){
            //账户不存在 注册一个
            $map['password'] = '';
            $map['openid'] = $openid;
            $map['nickname'] = $data['nickname'];
            $map['reg_time'] = time();
            $map['oauth'] = $oauth;
            $map['head_pic'] = $data['head_pic'];
            $map['sex'] = empty($data['sex']) ? 0 : $data['sex'];
            $map['token'] = md5(time().mt_rand(1,99999));
            $map['first_leader'] = cookie('first_leader'); // 推荐人id
            if($_GET['first_leader'])
                $map['first_leader'] = $_GET['first_leader']; // 微信授权登录返回时 get 带着参数的            

            /************核算等级start!!! by lishibo 20190217*************/
            if($map['first_leader']){

                $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
                if($first_leader){
                    //追加上线
                    $all_leader =$first_leader['all_leader'];
                    if(!empty($all_leader)){
                        $all_leader = $all_leader.','.$map['first_leader'];
                        $map['all_leader'] = $all_leader;
                    }else{
                        $map['all_leader'] = $map['first_leader'];
                    }
                    $map['second_leader'] = $first_leader['first_leader'];
                    $second_leader = M('users')->where("user_id", $map['second_leader'])->find();
                    if($second_leader){
                        $map['third_leader'] = $second_leader['first_leader'];
                    }
                }
            }else{
                $map['first_leader'] = 0;
            }

            // 成为分销商条件  
            $distribut_condition = tpCache('distribut.condition'); 
            if($distribut_condition == 0)  // 直接成为分销商, 每个人都可以做分销        
                $map['is_distribut']  = 1;
            $map['is_lock']  = 0; // 默认 不冻结会员 可登陆
            $map['user_money'] = '0.00';//初始余额
            $map['wei_jf'] = 1000;//初始分红积分(未激活)

            $row_id = M('users')->insertGetId($map);
            if($row_id === false) {  return array('status'=>1,'msg'=>'登陆失败','result'=>$user); }

            $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
            if($pay_points > 0){
                accountLog($row_id, 0,$pay_points, '会员注册赠送积分'); // 记录日志流水
            }

            /************核算等级end!!! by lishibo 20190217******废弃！！微信扫码注册不进行核算等级****/
         /*   if ( $map['first_leader'] > 0 ) {//注册成功
                $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
                if($first_leader){
                    $this->checkLevelNew($map['first_leader'],1);
                }
            }*/
            /************核算等级end!!! by lishibo 20190217*************/

            /***************STEP2 为所有上级更新直推以及累计人数  (废弃)**********************/
           /* if ( $map['first_leader'] > 0 ) {
                $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
                if($first_leader){
                    $this->CheckingRelationship($map['first_leader'],1);
                }
            }*/
            /***************STEP2 为所有上级更新直推以及累计人数**********************/


            $user = M('users')->where("user_id", $row_id)->find();


        }else{
            $user['token'] = md5(time().mt_rand(1,999999999));
            M('users')->where("user_id", $user['user_id'])->save(array('token'=>$user['token'],'last_login'=>time(),'push_id'=>$map['push_id']));
        }
        return array('status'=>1,'msg'=>'登陆成功','result'=>$user);
    }

    /**
     * 三渔工坊&注册&核算等级
     * @param $username  邮箱或手机
     * @param $password  密码
     * @param $password2 确认密码
     * @return array
     */
    public function regsy($username,$mobile,$password,$password2,$chushengriqi,$push_id=0,$invite=''){
        $is_validated = 0 ;

        $map['username'] = $username;
        $map['nickname'] = $username;
        $map['chushengriqi'] = $chushengriqi;

        if(check_email($mobile)){
            $is_validated = 1;
            $map['email_validated'] = 1;
            $map['nickname'] = $map['email'] = $mobile; //邮箱注册
        }

        if(check_mobile($mobile)){
            $is_validated = 1;
            $map['mobile_validated'] = 1;
            //$map['nickname'] = $map['mobile'] = $mobile; //手机注册
            $map['mobile'] = $mobile; //手机注册
        }

        if($is_validated != 1)
            return array('status'=>-1,'msg'=>'请用手机号或邮箱注册');

        if(!$mobile || !$password)
            return array('status'=>-1,'msg'=>'请输入手机号或密码');

        //验证两次密码是否匹配
        if($password2 != $password)
            return array('status'=>-1,'msg'=>'两次输入密码不一致');
        //验证是否存在用户名
        if(get_user_info($mobile,2))
            return array('status'=>-1,'msg'=>'手机已存在');

        $map['password'] = encrypt($password);
        $map['reg_time'] = time();

        $map['first_leader'] = cookie('first_leader'); // 推荐人id

        if ( strlen($invite) > 3 ) {//如果有 推荐人手机号
            $tjr_users = M('users')->where("mobile", $invite)->find();
            if($tjr_users){
                $tjr_user_id = (int)$tjr_users['user_id'];//
                if ( (int)$tjr_user_id == 0 ) {
                    return array('status'=>-1,'msg'=>'没有找到推荐人手机号');
                }
                //如果 查询的推荐人id 大于 0 放到变量中
                if ( $tjr_users['is_distribut'] == 0 ) {
                    return array('status'=>-1,'msg'=>'推荐人未开启分销功能');
                }
                if ( $tjr_users['is_lock'] == 1 ) {
                    return array('status'=>-1,'msg'=>'推荐人账号被锁定冻结');
                }
                if ( (int)$tjr_user_id > 0 ) {
                    $map['first_leader'] = $tjr_user_id; // 推荐人id
                }
            }
        }


        if($map['first_leader']){

            $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
            if($first_leader){
                //追加上线
                $all_leader =$first_leader['all_leader'];
                if(!empty($all_leader)){
                    $all_leader = $all_leader.','.$map['first_leader'];
                    $map['all_leader'] = $all_leader;
                }else{
                    $map['all_leader'] = $map['first_leader'];
                }
                //$this->checkLevel($map['first_leader'],1);
                $map['second_leader'] = $first_leader['first_leader'];
                $second_leader = M('users')->where("user_id", $map['second_leader'])->find();
                if($second_leader){
                    //$this->checkLevel($map['second_leader'],2);
                    $map['third_leader'] = $second_leader['first_leader'];
                    /*$third_leader = M('users')->where("user_id", $map['third_leader'])->find();
                    if($third_leader){
                        $this->checkLevel($map['third_leader'],3);
                    }*/
                }
            }

        }else{
            $map['first_leader'] = 0;
        }

        $map['is_lock']  = 0; // 默认 不冻结会员 可登陆
        $map['is_distribut']  = 1; // 默认 不可做分销
        $map['push_id'] = $push_id; //推送id
        $map['user_money'] = '0.00';//初始余额

        $user_id = M('users')->insertGetId($map);
        if($user_id === false) { return array('status'=>-1,'msg'=>'注册失败'); }

        $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
        if($pay_points > 0){
            accountLog($user_id, 0,$pay_points, '会员注册赠送积分'); // 记录日志流水
        }
        $user = M('users')->where("user_id", $user_id)->find();

        if ( (int)$tjr_user_id > 0 ) { //注册成功

            if($map['first_leader']){//等级核算
                $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
                if($first_leader){
                    $this->checkLevelNew($map['first_leader'],1);
                }
            }

            return array('status'=>1,'msg'=>'您好您已注册成功','result'=>$user);
        }  else {
            return array('status'=>1,'msg'=>'您好您已注册成功！','result'=>$user);
        }

    }

    /**
     * 注册会员不核算等级条件 只有确定收货才核算 20190227
     * @param $username
     * @param $mobile
     * @param $password
     * @param $password2
     * @param $chushengriqi
     * @param int $push_id
     * @param string $invite
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function regsyNew($username,$mobile,$password,$password2,$chushengriqi,$push_id=0,$invite='',$name){
        $is_validated = 0 ;

        $map['username'] = $username;
        $map['nickname'] = $username;
        $map['name'] = $name;
        $map['chushengriqi'] = $chushengriqi;

        if(check_email($mobile)){
            $is_validated = 1;
            $map['email_validated'] = 1;
            $map['nickname'] = $map['email'] = $mobile; //邮箱注册
        }

        if(check_mobile($mobile)){
            $is_validated = 1;
            $map['mobile_validated'] = 1;
            //$map['nickname'] = $map['mobile'] = $mobile; //手机注册
            $map['mobile'] = $mobile; //手机注册
        }

        if($is_validated != 1)
            return array('status'=>-1,'msg'=>'请用手机号或邮箱注册');

        if(!$mobile || !$password)
            return array('status'=>-1,'msg'=>'请输入手机号或密码');

        //验证两次密码是否匹配
        if($password2 != $password)
            return array('status'=>-1,'msg'=>'两次输入密码不一致');
        //验证是否存在用户名
        if(get_user_info($mobile,2))
            return array('status'=>-1,'msg'=>'手机已存在');

        $map['password'] = encrypt($password);
        $map['reg_time'] = time();

        $map['first_leader'] = cookie('first_leader'); // 推荐人id

        if ( !empty($invite)) {//如果有 推荐人id
            $tjr_users = M('users')->where("user_id", $invite)->find();
            if($tjr_users){
                $tjr_user_id = (int)$tjr_users['user_id'];//
                if ( (int)$tjr_user_id == 0 ) {
                    return array('status'=>-1,'msg'=>'没有找到推荐人');
                }
                //如果 查询的推荐人id 大于 0 放到变量中
                if ( $tjr_users['is_distribut'] == 0 ) {
                    return array('status'=>-1,'msg'=>'推荐人未开启分销功能');
                }
                if ( $tjr_users['is_lock'] == 1 ) {
                    return array('status'=>-1,'msg'=>'推荐人账号被锁定冻结');
                }
                if ( (int)$tjr_user_id > 0 ) {
                    $map['first_leader'] = $tjr_user_id; // 推荐人id
                }
            }
        }



        /***************STEP1 核对形成上下关系**********************/
        if($map['first_leader']){
            $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
            if($first_leader){
                //追加上线
                $all_leader =$first_leader['all_leader'];
                if(!empty($all_leader)){
                    $all_leader = $all_leader.','.$map['first_leader'];
                    $map['all_leader'] = $all_leader;
                }else{
                    $map['all_leader'] = $map['first_leader'];
                }
                //$this->checkLevel($map['first_leader'],1);
                $map['second_leader'] = $first_leader['first_leader'];
                $second_leader = M('users')->where("user_id", $map['second_leader'])->find();
                if($second_leader){
                    //$this->checkLevel($map['second_leader'],2);
                    $map['third_leader'] = $second_leader['first_leader'];
                    /*$third_leader = M('users')->where("user_id", $map['third_leader'])->find();
                    if($third_leader){
                        $this->checkLevel($map['third_leader'],3);
                    }*/
                }
            }
            /***************STEP1 核对形成上下关系**********************/
        }else{
            $map['first_leader'] = 0;
        }

        $map['is_lock']  = 0; // 默认 不冻结会员 可登陆
        $map['is_distribut']  = 1; // 默认 不可做分销
        $map['push_id'] = $push_id; //推送id
        $map['user_money'] = '0.00';//初始余额

        $user_id = M('users')->insertGetId($map);
        if($user_id === false) { return array('status'=>-1,'msg'=>'注册失败'); }

        $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
        if($pay_points > 0){
            accountLog($user_id, 0,$pay_points, '会员注册赠送积分'); // 记录日志流水
        }
        $user = M('users')->where("user_id", $user_id)->find();

        if ( (int)$tjr_user_id > 0 ) { //注册成功

            //原注册并核算等级
            if($map['first_leader']){//等级核算

                $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
                while($first_leader){
                    if($first_leader['level']!=4){
                    //判断推荐人等级
                    switch ($first_leader['level']){
                        case 1://如果推荐人是注册会员
                            $cc=M('user_level')->where(array('level_id'=>2))->find();
                            $zhiren=$cc['underling_direct_number'];
                            $tuanren=$cc['underling_number'];
                            $xiaohe=$cc['amount'];
                            break;
                        case 2://如果推荐人是区级代理
                            $cc=M('user_level')->where(array('level_id'=>3))->find();
                            $zhiren=$cc['underling_direct_number'];
                            $tuanren=$cc['underling_number'];
                            $xiaohe=$cc['amount'];
                            break;
                        case 3://如果推荐人是市级代理
                            $cc=M('user_level')->where(array('level_id'=>4))->find();
                            $zhiren=$cc['underling_direct_number'];
                            $tuanren=$cc['underling_number'];
                            $xiaohe=$cc['amount'];
                            break;
                    }
                            $tuiCo=M('users')->where(array('first_leader'=>$first_leader['user_id']))->count();
                    if($tuiCo>=$zhiren){//如果直推人数满足了升级条件
                        $where=array();
                        $u_xia= Db::name('users')->where(" first_leader = {$first_leader['user_id']} ")->select();
                        $arr_id="";//当前层所有的下一层id
                        $tuan_id="";//团id
                        while ($u_xia){
                            foreach ($u_xia as $val){
                                $arr_id=$arr_id.$val['user_id'].",";
                            }
                            $tuan_id=$tuan_id.$arr_id;
                            $arr_id=substr($arr_id,0,-1);
                            $u_xia= Db::name('users')->where('first_leader','in',$arr_id)->select();
                            $arr_id="";
                        }
                        $tuan_id=substr($tuan_id,0,-1);//所有下级的id
                        $tuanCo=M('users')->where('user_id','in',$tuan_id)->count();
                        if($tuanCo>=$tuanren){//如果销售团队人数达到升级条件
                            if($first_leader['chongzhi_yeji']>=$xiaohe){//升级
                                $daqui['level']=$first_leader['level']+1;
                                M('users')->where(array('user_id'=>$first_leader['user_id']))->update($daqui);
                            }
                        }
                    }
                    }

                    $first_leader = M('users')->where("user_id", $first_leader['first_leader'])->find();



                }
                }


            /***************STEP2 为所有上级更新直推以及累计人数 转移到确认收货环节**********************/
           if(true){
                if($map['first_leader']){
                    $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
                    if($first_leader){
                        $this->CheckingRelationship($map['first_leader'],1);
                    }
                }
            }
            /***************STEP2 为所有上级更新直推以及累计人数 转移到确认收货环节**********************/

            return array('status'=>1,'msg'=>'您好您已注册成功','result'=>$user);
        }  else {
            return array('status'=>1,'msg'=>'您好您已注册成功！','result'=>$user);
        }

    }


    /**
     * FOR REG 检查上下关系
     * @author  lishibo 20190301
     * @param $leader_id
     * @param int $level
     */
    function CheckingRelationship($leader_id,$level = 0){
        $leader = M('users')->where("user_id", $leader_id)->find();
        if( $level == 1 ){ //新字段
            //累计人数
            M('users')->where(array('user_id' => $leader_id))->setInc('underling_direct_number');
            M('users')->where(array('user_id' => $leader_id))->setInc('underling_number');
            //直推人 all_leader
            $all_order = $leader['all_leader'];
            $arr = explode(',',$all_order);
            foreach ($arr as $val){
                if((int)$val>0){
                    M('users')->where(array('user_id' => (int)$val))->setInc('underling_number');
                }
            }
        }
    }


    /**
     * FOR 确认收货 有效会员 核算level
     * @author  lishibo 20190301
     * @param $leader_id
     * @param int $level
     */
    function CheckingRelationshipLevelNew($leader_id,$level = 0){

        $leader = M('users')->where("user_id", $leader_id)->find();

        if( $level == 1 ){ //扩展标识
            //直推人 all_leader
            $all_order = $leader['all_leader'];
            $arr = explode(',',$all_order);
            foreach ($arr as $val){
                if((int)$val>0){
                    $tmpleader = M('users')->where("user_id", (int)$val)->find();
                    $where_u =  " (first_leader = ".(int)$val." or second_leader = ".(int)$val." or third_leader = ".(int)$val." ) and level >1";
                    $where_ud = " first_leader = ".(int)$val." and level>1";
                    $underling_number =  M('users')->where($where_u)->count();
                    $underling_direct_number =  M('users')->where($where_ud)->count();

                    $currLevel = $tmpleader['level'];
                    $tmpLevel = 0;

                    $tmpLevel_ud = $this->checkLevelUndelineDirectNumber($underling_direct_number);
                    $tmpLevel_u = $this->checkLevelUndelineNumber($underling_number);

                    if($tmpLevel_ud < $tmpLevel_u){//等级对称
                        $tmpLevel = $tmpLevel_ud;
                    }elseif ($tmpLevel_ud > $tmpLevel_u ){
                        $tmpLevel = $tmpLevel_u;
                    }else{
                        $tmpLevel = $tmpLevel_ud;
                    }

                    if($tmpLevel>0 && $currLevel<$tmpLevel){
                        M('users')->where(array('user_id' => (int)$val))->save(array('level' => $tmpLevel));
                    }
                }

            }

        }

    }

    /**
     * FOR 确认收货 有效会员 核算level
     * @author  lishibo 20190301
     * @param $leader_id
     * @param int $level
     */
    function CheckingRelationshipLevel_Graded_Nursing($leader_id,$level = 0){

        $leader = M('users')->where("user_id", $leader_id)->find();

        if( $level == 1 ){ //扩展标识
            //直推人 all_leader
            $all_order = $leader['all_leader'];
            $arr = explode(',',$all_order);
            foreach ($arr as $val){
                if((int)$val>0){
                    $tmpleader = M('users')->where("user_id", (int)$val)->find();
                    $where_u =  " (first_leader = ".(int)$val." or second_leader = ".(int)$val." or third_leader = ".(int)$val." ) and level >1";
                    $where_ud = " first_leader = ".(int)$val." and level>1";
                    $underling_number =  M('users')->where($where_u)->count();
                    $underling_direct_number =  M('users')->where($where_ud)->count();

                    $currLevel = $tmpleader['level'];
                    $tmpLevel = 0;

                    $tmpLevel_ud = $this->checkLevelUndelineDirectNumber($underling_direct_number);
                    $tmpLevel_u = $this->checkLevelUndelineNumber($underling_number);

                    if($tmpLevel_ud < $tmpLevel_u){//等级对称
                        $tmpLevel = $tmpLevel_ud;
                    }elseif ($tmpLevel_ud > $tmpLevel_u ){
                        $tmpLevel = $tmpLevel_u;
                    }else{
                        $tmpLevel = $tmpLevel_ud;
                    }

                    if($tmpLevel>0 && $currLevel<$tmpLevel){
                        M('users')->where(array('user_id' => (int)$val))->save(array('level' => $tmpLevel));
                        echo "<pre>";print_r('用户编号：'.$tmpleader['user_id'].'[+++]升级为：'.$tmpLevel);echo "<pre>";
                    }

                    if($tmpLevel>0 && $currLevel>$tmpLevel){
                        M('users')->where(array('user_id' => (int)$val))->save(array('level' => $tmpLevel));
                        echo "<pre>";print_r('用户编号：'.$tmpleader['user_id'].'[---]降级为：'.$tmpLevel);echo "<pre>";
                    }
                }

            }

        }

    }


    function CheckingRelationshipLevelNew_bak($leader_id,$level = 0){

        $leader = M('users')->where("user_id", $leader_id)->find();

        if( $level == 1 ){ //扩展标识
            //直推人 all_leader
            $all_order = $leader['all_leader'];
            $arr = explode(',',$all_order);
            foreach ($arr as $val){
                if((int)$val>0){
                    $tmpleader = M('users')->where("user_id", (int)$val)->find();
                    $where_u =  " (first_leader = ".(int)$val." or second_leader = ".(int)$val." or third_leader = ".(int)$val." ) and level >1";
                    $where_ud = " first_leader = ".(int)$val." and level>1";
                    $underling_number =  M('users')->where($where_u)->count();
                    $underling_direct_number =  M('users')->where($where_ud)->count();

                    $currLevel = $tmpleader['level'];
                    $tmpLevel=0;
                    $is_reduced_level = $tmpleader['is_reduced_level'];

                    $tmpLevel_udln = $this->checkLevelUndelineNumber($underling_number);
                    $tmpLevel_udldn = $this->checkLevelUndelineDirectNumber($underling_direct_number);

                    if($tmpLevel_udldn < $tmpLevel_udln){//等级对称
                        $tmpLevel = $tmpLevel_udldn;
                    }elseif ($tmpLevel_udldn > $tmpLevel_udln ){
                        $tmpLevel = $tmpLevel_udln;
                    }else{
                        $tmpLevel = $tmpLevel_udldn;
                    }

                    if($tmpLevel>0 && $currLevel<$tmpLevel && $is_reduced_level == 0){ //重要渠道降级枷锁的用户不参与
                        M('users')->where(array('user_id' => (int)$val))->save(array('level' => $tmpLevel));
                    }
                }

            }

        }

    }
    /**
     * FOR 确认收货 检查上下关系
     * @author  lishibo 20190301
     * @param $leader_id
     * @param int $level
     */
    function CheckingRelationshipLevel($leader_id,$level = 0){

        $leader = M('users')->where("user_id", $leader_id)->find();
        if( $level == 1 ){ //扩展标识
            //直推人 all_leader
            $all_order = $leader['all_leader'];
            $arr = explode(',',$all_order);
            foreach ($arr as $val){
                if((int)$val>0){
                    $tmpleader = M('users')->where("user_id", (int)$val)->find();
                    $underling_direct_number = $tmpleader['underling_direct_number'];
                    $underling_number = $tmpleader['underling_number'];
                    $currLevel = $tmpleader['level'];
                    $tmpLevel=0;
                    $is_reduced_level = $tmpleader['is_reduced_level'];

                    $tmpLevel_udldn = $this->checkLevelUndelineDirectNumber($underling_direct_number);
                    $tmpLevel_udln = $this->checkLevelUndelineNumber($underling_number);

                    if($tmpLevel_udldn < $tmpLevel_udln){//等级对称
                        $tmpLevel = $tmpLevel_udldn;
                    }elseif ($tmpLevel_udldn > $tmpLevel_udln ){
                        $tmpLevel = $tmpLevel_udln;
                    }else{
                        $tmpLevel = $tmpLevel_udldn;
                    }

                   // if($tmpLevel>0 && $currLevel<$tmpLevel){//达到晋升条件
                    if($tmpLevel>0 && $currLevel<$tmpLevel && $is_reduced_level == 0){ //重要渠道降级枷锁的用户不参与
                        M('users')->where(array('user_id' => (int)$val))->save(array('level' => $tmpLevel));
                    }
                }

            }

        }

    }


    /**
     * 原需求用于注册入口，现在保留不用
     * 核算等级&&统计直推&&各级累计总和
     * @author lishibo 20190215
     *
     * @param $leader_id
     * @param int $level
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    function checkLevelNew($leader_id,$level = 0){

        $leader = M('users')->where("user_id", $leader_id)->find();
        if( $level == 1 ){ //新字段
            //累计人数
            M('users')->where(array('user_id' => $leader_id))->setInc('underling_direct_number');
            M('users')->where(array('user_id' => $leader_id))->setInc('underling_number');
            //直推人 all_leader
            $all_order = $leader['all_leader'];
            $arr = explode(',',$all_order);
            foreach ($arr as $val){
                if((int)$val>0){
                    //累计所有上线
                    M('users')->where(array('user_id' => (int)$val))->setInc('underling_number');

                    $tmpleader = M('users')->where("user_id", (int)$val)->find();
                    $underling_direct_number = $tmpleader['underling_direct_number'];
                    $underling_number = $tmpleader['underling_number'];
                    $currLevel = $tmpleader['level'];
                    $tmpLevel=0;

                    $tmpLevel_udldn = $this->checkLevelUndelineDirectNumber($underling_direct_number);
                    $tmpLevel_udln = $this->checkLevelUndelineNumber($underling_number);
                    if($tmpLevel_udldn < $tmpLevel_udln){//等级对称
                        $tmpLevel = $tmpLevel_udldn;
                    }elseif ($tmpLevel_udldn > $tmpLevel_udln ){
                        $tmpLevel = $tmpLevel_udln;
                    }else{
                        $tmpLevel = $tmpLevel_udldn;
                    }

                    if($tmpLevel>0 && $currLevel<$tmpLevel){//达到晋升条件
                        M('users')->where(array('user_id' => (int)$val))->save(array('level' => $tmpLevel));
                    }
                }

            }

        }

    }
    /**
     * 核算等级
     * @author lishibo 20190215
     *
     * @param $leader_id
     * @param int $level
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    function checkLevel($leader_id,$level = 0){

        //上线分销的代理人数加1
        M('users')->where(array('user_id' => $leader_id))->setInc('underling_number');

        if( $level == 1 ){//新字段
            M('users')->where(array('user_id' => $leader_id))->setInc('underling_direct_number');
            //直推人 all_leader
        }

        $leader = M('users')->where("user_id", $leader_id)->find();
        $underling_direct_number = $leader['underling_direct_number'];
        $underling_number = $leader['underling_number'];
        $currLevel = $leader['level'];
        $tmpLevel=0;

        $tmpLevel_udldn = $this->checkLevelUndelineDirectNumber($underling_direct_number);
        $tmpLevel_udln = $this->checkLevelUndelineNumber($underling_number);
        if($tmpLevel_udldn < $tmpLevel_udln){//等级对称
            $tmpLevel = $tmpLevel_udldn;
        }elseif ($tmpLevel_udldn > $tmpLevel_udln ){
            $tmpLevel = $tmpLevel_udln;
        }else{
            $tmpLevel = $tmpLevel_udldn;
        }

        if($tmpLevel>0 && $currLevel<$tmpLevel){//达到晋升条件
            M('users')->where(array('user_id' => $leader_id))->save(array('LEVEL' => $tmpLevel));
        }
    }


    /**
     * 检查累计人数对应的等级
     * @author  lishibo 20190215
     * @param $underling_number
     * @return int
     */
    function checkLevelUndelineNumber($underling_number){
        $tmpLevel = 0;
        if($underling_number < 100){//经理
            $tmpLevel=4;
        }elseif(100 <= $underling_number){//总监
            $tmpLevel=5;
        }
        return $tmpLevel;
    }

 /*   function checkLevelUndelineNumber_bak($underling_number){
        $tmpLevel = 0;
        if($underling_number < 200){//会员
            $tmpLevel=3;
        }elseif(200 <= $underling_number && $underling_number < 1000){//经理
            $tmpLevel=4;
        }elseif(1000 <= $underling_number){//总监
            $tmpLevel=5;
        }
        return $tmpLevel;
    }*/

    /**
     * 检查直推人数对应的等级
     * @author  lishibo 20190215
     * @param $underling_direct_number
     * @return int
     */
    function checkLevelUndelineDirectNumber ($underling_direct_number){
        $tmpLevel = 0;
        if($underling_direct_number < 5){ //会员
            $tmpLevel=3;
        }elseif(5 <= $underling_direct_number && $underling_direct_number < 50){ //经理
            $tmpLevel=4;
        }elseif(50 <= $underling_direct_number){ //总监
            $tmpLevel=5;
        }
        return $tmpLevel;
    }

/*    function checkLevelUndelineDirectNumber ($underling_direct_number){
        $tmpLevel = 0;
        if($underling_direct_number < 50){//主管
            $tmpLevel=3;
        }elseif(50 <= $underling_direct_number && $underling_direct_number < 200){//经理
            $tmpLevel=4;
        }elseif(200 <= $underling_direct_number){//总监
            $tmpLevel=5;
        }
        return $tmpLevel;
    }*/

    function checkLevelUndelineNumber_bak($underling_number){
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

    function checkLevelUndelineDirectNumber_bak ($underling_direct_number){
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

/****************************************************************************/

    /**
     * 注册
     * @param $username  邮箱或手机
     * @param $password  密码
     * @param $password2 确认密码
     * @return array
     */
    public function reg($username,$password,$password2, $push_id=0){
        $is_validated = 0 ;
        if(check_email($username)){
            $is_validated = 1;
            $map['email_validated'] = 1;
            $map['nickname'] = $map['email'] = $username; //邮箱注册
        }

        if(check_mobile($username)){
            $is_validated = 1;
            $map['mobile_validated'] = 1;
            $map['nickname'] = $map['mobile'] = $username; //手机注册
        }

        if($is_validated != 1)
            return array('status'=>-1,'msg'=>'请用手机号或邮箱注册');

        if(!$username || !$password)
            return array('status'=>-1,'msg'=>'请输入用户名或密码');

        //验证两次密码是否匹配
        if($password2 != $password)
            return array('status'=>-1,'msg'=>'两次输入密码不一致');
        //验证是否存在用户名
        if(get_user_info($username,1)||get_user_info($username,2))
            return array('status'=>-1,'msg'=>'账号已存在');

        $map['password'] = encrypt($password);
        $map['reg_time'] = time();
        $map['first_leader'] = cookie('first_leader'); // 推荐人id
        // 如果找到他老爸还要找他爷爷他祖父等
        if($map['first_leader'])
        {
            $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
            $map['second_leader'] = $first_leader['first_leader'];
            $map['third_leader'] = $first_leader['second_leader'];
            //他上线分销的代理人数要加1
            M('users')->where(array('user_id' => $map['first_leader']))->setInc('underling_number');
            M('users')->where(array('user_id' => $map['second_leader']))->setInc('underling_number');
            M('users')->where(array('user_id' => $map['third_leader']))->setInc('underling_number');
        }else
        {
            $map['first_leader'] = 0;
        }

        // 成为分销商条件
        $distribut_condition = tpCache('distribut.condition');
        if($distribut_condition == 0)  // 直接成为分销商, 每个人都可以做分销
            $map['is_distribut']  = 1;

        $map['push_id'] = $push_id; //推送id
        //$map['token'] = md5(time().mt_rand(1,99999));

        $user_id = M('users')->insertGetId($map);
        if($user_id === false)
            return array('status'=>-1,'msg'=>'注册失败');

        $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
        if($pay_points > 0){
            accountLog($user_id, 0,$pay_points, '会员注册赠送积分'); // 记录日志流水
        }
        $user = M('users')->where("user_id", $user_id)->find();
        return array('status'=>1,'msg'=>'注册成功','result'=>$user);
    }

    public function thirdLogin_copy($data=array()){
        $openid = $data['openid']; //第三方返回唯一标识
        $oauth = $data['oauth']; //来源
        if(!$openid || !$oauth)
            return array('status'=>-1,'msg'=>'参数有误','result'=>'');
        //获取用户信息
        if(isset($data['unionid'])){
            $map['unionid'] = $data['unionid'];
            $user = get_user_info($data['unionid'],4,$oauth);
        }else{
            $user = get_user_info($openid,3,$oauth);
        }
        if(!$user){
            //账户不存在 注册一个
            $map['password'] = '';
            $map['openid'] = $openid;
            $map['nickname'] = $data['nickname'];
            $map['reg_time'] = time();
            $map['oauth'] = $oauth;
            $map['head_pic'] = $data['head_pic'];
            $map['sex'] = empty($data['sex']) ? 0 : $data['sex'];
            $map['token'] = md5(time().mt_rand(1,99999));
            $map['first_leader'] = cookie('first_leader'); // 推荐人id
            if($_GET['first_leader'])
                $map['first_leader'] = $_GET['first_leader']; // 微信授权登录返回时 get 带着参数的
            // 如果找到他老爸还要找他爷爷他祖父等
            if($map['first_leader'])
            {
                $first_leader = M('users')->where("user_id", $map['first_leader'])->find();
                $map['second_leader'] = $first_leader['first_leader']; //  第一级推荐人
                $map['third_leader'] = $first_leader['second_leader']; // 第二级推荐人
                //他上线分销的代理人数要加1
                M('users')->where(array('user_id' => $map['first_leader']))->setInc('underling_number');
                M('users')->where(array('user_id' => $map['second_leader']))->setInc('underling_number');
                M('users')->where(array('user_id' => $map['third_leader']))->setInc('underling_number');
            }else
            {
                $map['first_leader'] = 0;
            }

            // 成为分销商条件
            $distribut_condition = tpCache('distribut.condition');
            if($distribut_condition == 0)  // 直接成为分销商, 每个人都可以做分销
                $map['is_distribut']  = 1;

            $row_id = M('users')->insertGetId($map);
//			// 会员注册送优惠券
//			$coupon = M('coupon')->where("send_end_time > ".time()." and ((createnum - send_num) > 0 or createnum = 0) and type = 2")->select();
//			foreach ($coupon as $key => $val)
//			{
//				// 送券
//				M('coupon_list')->add(array('cid'=>$val['id'],'type'=>$val['type'],'uid'=>$row_id,'send_time'=>time()));
//				M('Coupon')->where("id", $val['id'])->setInc('send_num'); // 优惠券领取数量加一
//			}
            $user = M('users')->where("user_id", $row_id)->find();

        }else{
            $user['token'] = md5(time().mt_rand(1,999999999));
            M('users')->where("user_id", $user['user_id'])->save(array('token'=>$user['token'],'last_login'=>time(),'push_id'=>$map['push_id']));
        }
        return array('status'=>1,'msg'=>'登陆成功','result'=>$user);
    }

    /**
     * 我修改后的注册
     * @param $username  邮箱或手机
     * @param $password  密码
     * @param $password2 确认密码
     * @return array
     */
    public function my_reg($username,$mobile,$password,$password2,$chushengriqi,$push_id=0,$invite=''){
    	$is_validated = 0 ;
		
		$map['username'] = $username;
		$map['nickname'] = $username;
		$map['chushengriqi'] = $chushengriqi;
		
        if(check_email($mobile)){
            $is_validated = 1;
            $map['email_validated'] = 1;
            $map['nickname'] = $map['email'] = $mobile; //邮箱注册
        }

        if(check_mobile($mobile)){
            $is_validated = 1;
            $map['mobile_validated'] = 1;
            //$map['nickname'] = $map['mobile'] = $mobile; //手机注册
			$map['mobile'] = $mobile; //手机注册
        }

        if($is_validated != 1)
            return array('status'=>-1,'msg'=>'请用手机号或邮箱注册');

        if(!$mobile || !$password)
            return array('status'=>-1,'msg'=>'请输入手机号或密码');

        //验证两次密码是否匹配
        if($password2 != $password)
            return array('status'=>-1,'msg'=>'两次输入密码不一致');
        //验证是否存在用户名
        if(get_user_info($mobile,2))
            return array('status'=>-1,'msg'=>'手机已存在');

        $map['password'] = encrypt($password);
        $map['reg_time'] = time();
		
        $map['first_leader'] = cookie('first_leader'); // 推荐人id
		
		if ( strlen($invite) > 3 ) {//如果有 推荐人手机号
			$tjr_users = M('users')->where("mobile", $invite)->find();
			if($tjr_users){
				$tjr_user_id = (int)$tjr_users['user_id'];//
				if ( (int)$tjr_user_id == 0 ) {
					return array('status'=>-1,'msg'=>'没有找到推荐人手机号');	
				}
				//如果 查询的推荐人id 大于 0 放到变量中
				if ( $tjr_users['is_distribut'] == 0 ) {
					return array('status'=>-1,'msg'=>'推荐人未开启分销功能');	
				}
				if ( $tjr_users['is_lock'] == 1 ) {
					return array('status'=>-1,'msg'=>'推荐人账号被锁定冻结');	
				}
				if ( (int)$tjr_user_id > 0 ) {
					$map['first_leader'] = $tjr_user_id; // 推荐人id
				} 
    		}
		}
		
//		 else {
//			$map['first_leader'] = 1; // 官网推荐人id	 
//		}
		
        // 如果找到他老爸还要找他爷爷他祖父等
        if($map['first_leader'])
        {
			
				
				/*M('users')->where(array('user_id' => $map['first_leader']))->setInc('frozen_money','0.00'); //给推荐者加销售佣金
				$account_log = array(
					'user_id'       => $map['first_leader'],
					'user_money'    => '0.00',
					'pay_points'    => '0',
					'change_time'   => time(),
					'desc'   => '销售佣金（来自于）',
					'distribut_money' => '0.00',
					'order_id' => '0',
					'order_sn' => '',
					'address_id' => 0,
					'total_amount' => '0',
				);
				M('account_log')->add($account_log);*/
			
			
			$first_leader = M('users')->where("user_id", $map['first_leader'])->find();
			if($first_leader){
            	$map['second_leader'] = $first_leader['first_leader'];
           		$second_leader = M('users')->where("user_id", $first_leader['first_leader'])->find();
				if($second_leader){
					$map['third_leader'] = $second_leader['first_leader'];
				}
			}
			
            //他上线分销的代理人数要加1
            M('users')->where(array('user_id' => $map['first_leader']))->setInc('underling_number');
            M('users')->where(array('user_id' => $map['second_leader']))->setInc('underling_number');
            M('users')->where(array('user_id' => $map['third_leader']))->setInc('underling_number');
			
        }else
		{
			$map['first_leader'] = 0;
		}

        // 成为分销商条件  
        $distribut_condition = tpCache('distribut.condition'); 
        /* if($distribut_condition == 0)  // 直接成为分销商, 每个人都可以做分销        
            $map['is_distribut']  = 1; */
			
		
		$map['is_lock']  = 0; // 默认 不冻结会员 可登陆	
		$map['is_distribut']  = 1; // 默认 不可做分销
			
			        
        
        $map['push_id'] = $push_id; //推送id
        //$map['token'] = md5(time().mt_rand(1,99999));
        
		
		$map['user_money'] = '0.00';//给本人加5元可用余额
		
        $user_id = M('users')->insertGetId($map);
        if($user_id === false) { return array('status'=>-1,'msg'=>'注册失败'); }
           
        $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
        if($pay_points > 0){
            accountLog($user_id, 0,$pay_points, '会员注册赠送积分'); // 记录日志流水
        }
        $user = M('users')->where("user_id", $user_id)->find();
		
		if ( (int)$tjr_user_id > 0 ) {
			//return array('status'=>1,'msg'=>'注册成功,请等待审核','result'=>$user);
			return array('status'=>1,'msg'=>'您好您已注册成功','result'=>$user);
		}  else {
			return array('status'=>1,'msg'=>'您好您已注册成功，推荐人为官网','result'=>$user);
		}

    }



     /*
      * 获取当前登录用户信息
      */
    public function get_info($user_id)
    {
        if (!$user_id) {
            return array('status'=>-1, 'msg'=>'缺少参数');
        }

        $user = M('users')->where('user_id', $user_id)->find();
        if (!$user) {
            return false;
        }

        $activityLogic = new \app\common\logic\ActivityLogic;             //获取能使用优惠券个数
        $user['coupon_count'] = $activityLogic->getUserCouponNum($user_id, 0);
        
        $user['collect_count'] = $this->getGoodsCollectNum($user_id);; //获取收藏数量
        $user['return_count'] = M('return_goods')->where("user_id=$user_id and status<2")->count();   //退换货数量
        
        $user['waitPay']     = M('order')->where("user_id = :user_id ".C('WAITPAY'))->bind(['user_id'=>$user_id])->count(); //待付款数量
        $user['waitSend']    = M('order')->where("user_id = :user_id ".C('WAITSEND'))->bind(['user_id'=>$user_id])->count(); //待发货数量
        $user['waitReceive'] = M('order')->where("user_id = :user_id ".C('WAITRECEIVE'))->bind(['user_id'=>$user_id])->count(); //待收货数量
        $user['order_count'] = $user['waitPay'] + $user['waitSend'] + $user['waitReceive'];
        
        $commentLogic = new CommentLogic;
        $user['comment_count'] = $commentLogic->getHadCommentNum($user_id); //已评论数
        $user['uncomment_count'] = $commentLogic->getWaitCommentNum($user_id); //待评论数
        
         return ['status' => 1, 'msg' => '获取成功', 'result' => $user];
     }
     
    /*
      * 获取当前登录用户信息
      */
    public function getApiUserInfo($user_id)
    {
        if (!$user_id) {
            return array('status'=>-1, 'msg'=>'缺少参数');
        }

        $user = M('users')->where('user_id', $user_id)->find();
        if (!$user) {
            return false;
        }

        $activityLogic = new \app\common\logic\ActivityLogic;             //获取能使用优惠券个数
        $user['coupon_count'] = $activityLogic->getUserCouponNum($user_id, 0);
        
        $user['collect_count'] = $this->getGoodsCollectNum($user_id);; //获取收藏数量
        $user['visit_count']   = M('goods_visit')->where('user_id', $user_id)->count();   //商品访问记录数
        $user['return_count'] = M('return_goods')->where("user_id=$user_id and status<2")->count();   //退换货数量
        
        $user['waitPay']     = M('order')->where("user_id = :user_id ".C('WAITPAY'))->bind(['user_id'=>$user_id])->count(); //待付款数量
        $user['waitSend']    = M('order')->where("user_id = :user_id ".C('WAITSEND'))->bind(['user_id'=>$user_id])->count(); //待发货数量
        $user['waitReceive'] = M('order')->where("user_id = :user_id ".C('WAITRECEIVE'))->bind(['user_id'=>$user_id])->count(); //待收货数量
        $user['order_count'] = $user['waitPay'] + $user['waitSend'] + $user['waitReceive'];
        
        $commentLogic = new CommentLogic;
        $user['comment_count'] = $commentLogic->getHadCommentNum($user_id); //已评论数
        $user['uncomment_count'] = $commentLogic->getWaitCommentNum($user_id); //待评论数

        $cartLogic = new CartLogic();
        $cartLogic->setUserId($user_id);
        $cartList = $cartLogic->getUserCartList(1);// 选中的商品
        $user['cart_goods_num'] = $cartList['total_price']['num']; //购物车商品数量
            
         return ['status' => 1, 'msg' => '获取成功', 'result' => $user];
     }
     
    /*
     * 获取最近一笔订单
     */
    public function get_last_order($user_id){
        $last_order = M('order')->where("user_id", $user_id)->order('order_id DESC')->find();
        return $last_order;
    }


    /*
     * 获取订单商品
     */
    public function get_order_goods($order_id){
        $sql = "SELECT og.*,g.commission,g.is_level FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__goods g ON g.goods_id = og.goods_id WHERE order_id = :order_id";
        $bind['order_id'] = $order_id;
        $goods_list = DB::query($sql,$bind);

        $return['status'] = 1;
        $return['msg'] = '';
        $return['result'] = $goods_list;
        return $return;
    }

    /**
     * 获取账户资金记录
     * @param $user_id|用户id
     * @param int $account_type|收入：1,支出:2 所有：0
     * @return mixed
     */
    public function get_account_log($user_id,$account_type = 0){
        $account_log_where = ['user_id'=>$user_id];
        if($account_type == 1){
            $account_log_where['user_money|pay_points'] = ['gt',0];
        }
        if($account_type == 2){
            $account_log_where['user_money|pay_points'] = ['lt',0];
        }
        $count = M('account_log')->where($account_log_where)->count();
        $Page = new Page($count,16);
        $account_log = M('account_log')->where($account_log_where)
            ->order('change_time desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $return = [
            'status'    =>1,
            'msg'       =>'',
            'result'    =>$account_log,
            'show'      =>$Page->show()
        ];
        return $return;
    }

    /**
     * 提现记录
     * @author lxl 2017-4-26
     * @param $user_id
     * @param int $withdrawals_status 提现状态 0:申请中 1:申请成功 2:申请失败
     * @return mixed
     */
    public function get_withdrawals_log($user_id,$withdrawals_status=''){
        $withdrawals_log_where = ['user_id'=>$user_id];
        if($withdrawals_status){
            $withdrawals_log_where['status']=$withdrawals_status;
        }
        $count = M('withdrawals')->where($withdrawals_log_where)->count();
        $Page = new Page($count, 10);
        $withdrawals_log = M('withdrawals')->where($withdrawals_log_where)
            ->order('id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $return = [
            'status'    =>1,
            'msg'       =>'',
            'result'    =>$withdrawals_log,
            'show'      =>$Page->show()
        ];
        return $return;
    }

    /**
     * 用户充值记录
     * $author lxl 2017-4-26
     * @param $user_id 用户ID
     * @param int $pay_status 充值状态0:待支付 1:充值成功 2:交易关闭
     * @return mixed
     */
    public function get_recharge_log($user_id,$pay_status=0){
        $recharge_log_where = ['user_id'=>$user_id];
        if($pay_status){
            $pay_status['status']=$pay_status;
        }
        $count = M('recharge')->where($recharge_log_where)->count();
        $Page = new Page($count, 10);
        $recharge_log = M('recharge')->where($recharge_log_where)
            ->order('order_id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $return = [
            'status'    =>1,
            'msg'       =>'',
            'result'    =>$recharge_log,
            'show'      =>$Page->show()
        ];
        return $return;
    }
    /*
     * 获取优惠券
     */
    public function get_coupon($user_id, $type =0, $orderBy = null)
    {
        $activityLogic = new \app\common\logic\ActivityLogic;
        $count = $activityLogic->getUserCouponNum($user_id, $type, $orderBy);
        
        $page = new Page($count, 10);
        $list = $activityLogic->getUserCouponList($page->firstRow, $page->listRows, $user_id, $type, $orderBy);

        $return['status'] = 1;
        $return['msg'] = '获取成功';
        $return['result'] = $list;
        $return['show'] = $page->show();
        return $return;
    }

    public function getGoodsCollectNum($user_id)
    {
        $count = M('goods_collect')->alias('c')
                ->join('goods g','g.goods_id = c.goods_id','INNER')
                ->where('user_id', $user_id)
                ->count();
        return $count;
    }
    
    /**
     * 获取商品收藏列表
     * @param $user_id  用户id
     */
    public function get_goods_collect($user_id){
        $count = $this->getGoodsCollectNum($user_id);
        $page = new Page($count,10);
        $show = $page->show();
        //获取我的收藏列表
            $result = M('goods_collect')->alias('c')
            ->field('c.collect_id,c.add_time,g.goods_id,g.goods_name,g.shop_price,g.is_on_sale,g.store_count,g.cat_id ')
            ->join('goods g','g.goods_id = c.goods_id','INNER')
            ->where("c.user_id = $user_id")
            ->limit($page->firstRow,$page->listRows)
            ->select();
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        $return['show'] = $show;        
        return $return;
    }

    /**
     * 获取评论列表
     * @param $user_id 用户id
     * @param $status  状态 0 未评论 1 已评论 2全部
     * @return mixed
     */
    public function get_comment($user_id,$status=2){
        if($status == 1){
            //已评论
            $commented_count = Db::name('comment')
                ->alias('c')
                ->join('__ORDER_GOODS__ g','c.goods_id = g.goods_id and c.order_id = g.order_id', 'inner')
                ->where('c.user_id',$user_id)
                ->count();
            $page = new Page($commented_count,10);
            $comment_list = Db::name('comment')
                ->alias('c')
                ->field('c.*,g.*,(select order_sn from  __PREFIX__order where order_id = c.order_id ) as order_sn')
                ->join('__ORDER_GOODS__ g','c.goods_id = g.goods_id and c.order_id = g.order_id', 'inner')
                ->where('c.user_id',$user_id)
                ->order('c.add_time desc')
                ->limit($page->firstRow,$page->listRows)
                ->select();
        }else{
            $comment_where = ['o.user_id'=>$user_id,'og.is_send'=>1,'o.order_status'=>['in',[2,4]]];
            if($status == 0){
                $comment_where['og.is_comment'] = 0;
                $comment_where['o.order_status'] = 2;
            }
            $comment_count = Db::name('order_goods')->alias('og')->join('__ORDER__ o','o.order_id = og.order_id','left')->where($comment_where)->count();
            $page = new Page($comment_count,10);
            $comment_list = Db::name('order_goods')
                ->alias('og')
                ->join('__ORDER__ o','o.order_id = og.order_id','left')
                ->where($comment_where)
                ->order('o.order_id desc')
                ->limit($page->firstRow,$page->listRows)
                ->select();
        }
        $show = $page->show();
        if($comment_list){
        	$return['result'] = $comment_list;
        	$return['show'] = $show; //分页
        	return $return;
        }else{
        	return array();
        }
    }

    /**
     * 添加评论
     * @param $add
     * @return array
     */
    public function add_comment($add){
        if(!$add['order_id'] || !$add['goods_id'])
            return array('status'=>-1,'msg'=>'非法操作','result'=>'');
        
        //检查订单是否已完成
        $order = M('order')->field('order_status')->where("order_id", $add['order_id'])->where('user_id', $add['user_id'])->find();
        if($order['order_status'] != 2)
            return array('status'=>-1,'msg'=>'该笔订单还未确认收货','result'=>'');

        //检查是否已评论过
        $goods = M('comment')->where(['order_id'=>$add['order_id'],'goods_id'=>$add['goods_id']])->find();
        if($goods) {
            return array('status'=>-1,'msg'=>'您已经评论过该商品','result'=>'');
        }

        $row = M('comment')->add($add);
        if($row)
        {
            //更新订单商品表状态
            M('order_goods')->where(array('goods_id'=>$add['goods_id'],'order_id'=>$add['order_id']))->save(array('is_comment'=>1));
            M('goods')->where(array('goods_id'=>$add['goods_id']))->setInc('comment_count',1); // 评论数加一
            // 查看这个订单是否全部已经评论,如果全部评论了 修改整个订单评论状态            
            $comment_count   = M('order_goods')->where("order_id", $add['order_id'])->where('is_comment', 0)->count();
            if($comment_count == 0) // 如果所有的商品都已经评价了 订单状态改成已评价
            {
                M('order')->where("order_id",$add['order_id'])->save(array('order_status'=>4));
            }
            return array('status'=>1,'msg'=>'评论成功','result'=>'');
        }
        return array('status'=>-1,'msg'=>'评论失败','result'=>'');
    }

    /**
     * 虚拟评论 lishibo
     * @param $add
     * @return array
     */
    public function add_comment_virtualComments($add){
        $row = M('comment')->add($add);
        if($row)
        {
            return array('status'=>1,'msg'=>'评论成功','result'=>'');
        }
        return array('status'=>-1,'msg'=>'评论失败','result'=>'');
    }

    /**
     * 邮箱或手机绑定
     * @param $email_mobile  邮箱或者手机
     * @param int $type  1 为更新邮箱模式  2 手机
     * @param int $user_id  用户id
     * @return bool
     */
    public function update_email_mobile($email_mobile,$user_id,$type=1){
        //检查是否存在邮件
        if($type == 1)
            $field = 'email';
        if($type == 2)
            $field = 'mobile';
        $condition['user_id'] = array('neq',$user_id);
        $condition[$field] = $email_mobile;

        $is_exist = M('users')->where($condition)->find();
        if($is_exist)
            return false;
        unset($condition[$field]);
        $condition['user_id'] = $user_id;
        $validate = $field.'_validated';
        M('users')->where($condition)->save(array($field=>$email_mobile,$validate=>1));
        return true;
    }

    /**
     * 更新用户信息
     * @param $user_id
     * @param $post  要更新的信息
     * @return bool
     */
    public function update_info($user_id,$post=array()){
        $model = M('users')->where("user_id", $user_id);
        $row = $model->setField($post);
        if($row === false)
           return false;
        return true;
    }

    /**
     * 地址添加/编辑
     * @param $user_id 用户id
     * @param $user_id 地址id(编辑时需传入)
     * @return array
     */
    public function add_address($user_id,$address_id=0,$data){
        $post = $data;
        if($address_id == 0)
        {
            $c = M('UserAddress')->where("user_id", $user_id)->count();
            if($c >= 20)
                return array('status'=>-1,'msg'=>'最多只能添加20个收货地址','result'=>'');
        }

        //检查手机格式
        if($post['consignee'] == '')
            return array('status'=>-1,'msg'=>'收货人不能为空','result'=>'');
        if(!$post['province'] || !$post['city'] || !$post['district'])
            return array('status'=>-1,'msg'=>'所在地区不能为空','result'=>'');
        if(!$post['address'])
            return array('status'=>-1,'msg'=>'地址不能为空','result'=>'');
        if(!check_mobile($post['mobile']))
            return array('status'=>-1,'msg'=>'手机号码格式有误','result'=>'');

        //编辑模式
        if($address_id > 0){

            $address = M('user_address')->where(array('address_id'=>$address_id,'user_id'=> $user_id))->find();
            if($post['is_default'] == 1 && $address['is_default'] != 1)
                M('user_address')->where(array('user_id'=>$user_id))->save(array('is_default'=>0));
            $row = M('user_address')->where(array('address_id'=>$address_id,'user_id'=> $user_id))->save($post);
            if(!$row)
                return array('status'=>-1,'msg'=>'操作完成','result'=>'');
            return array('status'=>1,'msg'=>'编辑成功','result'=>'');
        }
        //添加模式
        $post['user_id'] = $user_id;
        
        // 如果目前只有一个收货地址则改为默认收货地址
        $c = M('user_address')->where("user_id", $post['user_id'])->count();
        if($c == 0)  $post['is_default'] = 1;
        
        $address_id = M('user_address')->add($post);
        //如果设为默认地址
        $insert_id = DB::name('user_address')->getLastInsID();
        $map['user_id'] = $user_id;
        $map['address_id'] = array('neq',$insert_id);
               
        if($post['is_default'] == 1)
            M('user_address')->where($map)->save(array('is_default'=>0));
        if(!$address_id)
            return array('status'=>-1,'msg'=>'添加失败','result'=>'');
        
        
        return array('status'=>1,'msg'=>'添加成功','result'=>$address_id);
    }

    /**
     * 添加自提点
     * @author dyr
     * @param $user_id
     * @param $post
     * @return array
     */
    public function add_pick_up($user_id, $post)
    {
        //检查用户是否已经有自提点
        $user_pickup_address_id = M('user_address')->where(['user_id'=>$user_id,'is_pickup'=>1])->getField('address_id');
        $pick_up = M('pick_up')->where(array('pickup_id' => $post['pickup_id']))->find();
        $post['address'] = $pick_up['pickup_address'];
        $post['is_pickup'] = 1;
        $post['user_id'] = $user_id;
        $user_address = new UserAddress();
        if (!empty($user_pickup_address_id)) {
            //更新自提点
            $user_address_save_result = $user_address->allowField(true)->validate(true)->save($post,['address_id'=>$user_pickup_address_id]);
        } else {
            //添加自提点
            $user_address_save_result = $user_address->allowField(true)->validate(true)->save($post);
        }
        if (false === $user_address_save_result) {
            return array('status' => -1, 'msg' => '保存失败', 'result' => $user_address->getError());
        } else {
            return array('status' => 1, 'msg' => '保存成功', 'result' => '');
        }
    }

    /**
     * 设置默认收货地址
     * @param $user_id
     * @param $address_id
     */
    public function set_default($user_id,$address_id){
        M('user_address')->where(array('user_id'=>$user_id))->save(array('is_default'=>0)); //改变以前的默认地址地址状态
        $row = M('user_address')->where(array('user_id'=>$user_id,'address_id'=>$address_id))->save(array('is_default'=>1));
        if(!$row)
            return false;
        return true;
    }

    /**
     * 修改密码
     * @param $user_id  用户id
     * @param $old_password  旧密码
     * @param $new_password  新密码
     * @param $confirm_password 确认新 密码
     */
    public function password($user_id,$old_password,$new_password,$confirm_password,$is_update=true){
        $user = M('users')->where('user_id', $user_id)->find();
        if(strlen($new_password) < 6)
            return array('status'=>-1,'msg'=>'密码不能低于6位字符','result'=>'');
        if($new_password != $confirm_password)
            return array('status'=>-1,'msg'=>'两次密码输入不一致','result'=>'');
        //验证原密码
        if($is_update && ($user['password'] != '' && encrypt($old_password) != $user['password']))
            return array('status'=>-1,'msg'=>'密码验证失败','result'=>'');
        $row = M('users')->where("user_id", $user_id)->save(array('password'=>encrypt($new_password)));
        if(!$row)
            return array('status'=>-1,'msg'=>'修改失败','result'=>'');
        return array('status'=>1,'msg'=>'修改成功','result'=>'');
    }

    /**
     *  针对 APP 修改密码的方法
     * @param $user_id  用户id
     * @param $old_password  旧密码
     * @param $new_password  新密码
     * @param $confirm_password 确认新 密码
     */
    public function passwordForApp($user_id,$old_password,$new_password,$is_update=true){
        $user = M('users')->where('user_id', $user_id)->find();
        if(strlen($new_password) < 6){
            return array('status'=>-1,'msg'=>'密码不能低于6位字符','result'=>'');
        }
        //验证原密码
        if($is_update && ($user['password'] != '' && $old_password != $user['password'])){
            return array('status'=>-1,'msg'=>'旧密码错误','result'=>'');
        }

        $row = M('users')->where("user_id='{$user_id}'")->update(array('password'=>$new_password));
        if(!$row){
            return array('status'=>-1,'msg'=>'密码修改失败','result'=>'');
        }
        return array('status'=>1,'msg'=>'密码修改成功','result'=>'');
    }
    
    /**
     * 设置支付密码
     * @param $user_id  用户id
     * @param $new_password  新密码
     * @param $confirm_password 确认新 密码
     */
    public function paypwd($user_id,$new_password,$confirm_password){
        if(strlen($new_password) < 6)
            return array('status'=>-1,'msg'=>'密码不能低于6位字符','result'=>'');
        if($new_password != $confirm_password)
            return array('status'=>-1,'msg'=>'两次密码输入不一致','result'=>'');
        $row = M('users')->where("user_id",$user_id)->update(array('paypwd'=>encrypt($new_password)));
        if(!$row){
            return array('status'=>-1,'msg'=>'修改失败','result'=>'');
        }
        return array('status'=>1,'msg'=>'修改成功','result'=>'');
    }

    /**
     * 取消订单 lxl 2017-4-29
     * @param $user_id  用户ID
     * @param $order_id 订单ID
     * @param string $action_note 操作备注
     * @return array
     */
    public function cancel_order($user_id,$order_id,$action_note='您取消了订单'){
        $order = M('order')->where(array('order_id'=>$order_id,'user_id'=>$user_id))->find();
        //检查是否未支付订单 已支付联系客服处理退款
        if(empty($order))
            return array('status'=>-1,'msg'=>'订单不存在','result'=>'');
        //检查是否未支付的订单
        if($order['pay_status'] > 0 || $order['order_status'] > 0)
            return array('status'=>-1,'msg'=>'支付状态或订单状态不允许','result'=>'');
        //获取记录表信息
        //$log = M('account_log')->where(array('order_id'=>$order_id))->find();
        //有余额支付的情况
        if($order['user_money'] > 0 || $order['integral'] > 0){
            accountLog($user_id,$order['user_money'],$order['integral'],"订单取消，退回{$order['user_money']}分,{$order['integral']}积分");
        }
        
		if($order['coupon_price'] >0){
			$res = array('use_time'=>0,'status'=>0,'order_id'=>0);
			M('coupon_list')->where(array('order_id'=>$order_id,'uid'=>$user_id))->save($res);
		}
		
        $row = M('order')->where(array('order_id'=>$order_id,'user_id'=>$user_id))->save(array('order_status'=>3));
				
        $data['order_id'] = $order_id;
        $data['action_user'] = 0;
        $data['action_note'] = $action_note;
        $data['order_status'] = 3;
        $data['pay_status'] = $order['pay_status'];
        $data['shipping_status'] = $order['shipping_status'];
        $data['log_time'] = time();
        $data['status_desc'] = '用户取消订单';        
        M('order_action')->add($data);//订单操作记录

        if(!$row)
            return array('status'=>-1,'msg'=>'操作失败','result'=>'');
        return array('status'=>1,'msg'=>'操作成功','result'=>'');

    }

    /**
     * 自动取消订单
     * @author lxl 2014-4-29
     * @param $order_id         订单id
     * @param $user_id  用户ID
     * @param $orderAddTime 订单添加时间
     * @param $setTime  自动取消时间/天 默认1天
     */
    public function  abolishOrder($user_id,$order_id,$orderAddTime='',$setTime=1){
        $abolishtime = strtotime("-$setTime day");
       if($orderAddTime<$abolishtime) {
           $action_note = '超过' . $setTime . '天未支付自动取消';
           $result = $this->cancel_order($user_id,$order_id,$action_note);
//           if($result['status']==1)
               return $result;
       }
    }
  
    /**
     * 发送验证码: 该方法只用来发送邮件验证码, 短信验证码不再走该方法
     * @param $sender 接收人
     * @param $type 发送类型
     * @return json
     */
    public function send_email_code($sender){
    	$sms_time_out = tpCache('sms.sms_time_out');
    	$sms_time_out = $sms_time_out ? $sms_time_out : 180;
    	//获取上一次的发送时间
    	$send = session('validate_code');
    	if(!empty($send) && $send['time'] > time() && $send['sender'] == $sender){
    		//在有效期范围内 相同号码不再发送
    		$res = array('status'=>-1,'msg'=>'规定时间内,不要重复发送验证码');
            return $res;
    	}
    	$code =  mt_rand(1000,9999);
		//检查是否邮箱格式
		if(!check_email($sender)){
			$res = array('status'=>-1,'msg'=>'邮箱码格式有误');
            return $res;
		}
		$send = send_email($sender,'验证码','您好，你的验证码是：'.$code);
    	if($send['status'] == 1){
    		$info['code'] = $code;
    		$info['sender'] = $sender;
    		$info['is_check'] = 0;
    		$info['time'] = time() + $sms_time_out; //有效验证时间
    		session('validate_code',$info);
    		$res = array('status'=>1,'msg'=>'验证码已发送，请注意查收');
    	}else{
    		$res = $send;
    	}
    	return $res;
    }    
     
    /**
     * 检查短信/邮件验证码验证码
     * @param unknown $code
     * @param unknown $sender
     * @param unknown $session_id
     * @return multitype:number string
     */
    public function check_validate_code($code, $sender, $type ='email', $session_id=0 ,$scene = -1){
    	
        $timeOut = time();
        $inValid = true;  //验证码失效

        //短信发送否开启
        //-1:用户没有发送短信
        //空:发送验证码关闭
        $sms_status = checkEnableSendSms($scene);

        //邮件证码是否开启
        $reg_smtp_enable = tpCache('smtp.regis_smtp_enable');
        
        if($type == 'email'){            
            if(!$reg_smtp_enable){//发生邮件功能关闭
                $validate_code = session('validate_code');
                $validate_code['sender'] = $sender;
                $validate_code['is_check'] = 1;//标示验证通过
                session('validate_code',$validate_code);
                return array('status'=>1,'msg'=>'邮件验证码功能关闭, 无需校验验证码');
            }            
            if(!$code)return array('status'=>-1,'msg'=>'请输入邮件验证码');                
            //邮件
            $data = session('validate_code');
            $timeOut = $data['time'];
            if($data['code'] != $code || $data['sender']!=$sender){
            	$inValid = false;
            }  
        }else{
            if($scene == -1){
                return array('status'=>-1,'msg'=>'参数错误, 请传递合理的scene参数');
            }elseif($sms_status['status'] == 0){
                $data['sender'] = $sender;
                $data['is_check'] = 1; //标示验证通过
                session('validate_code',$data);
                return array('status'=>1,'msg'=>'短信验证码功能关闭, 无需校验验证码');
            } 
            
            if(!$code)return array('status'=>-1,'msg'=>'请输入短信验证码A');
            //短信
            $sms_time_out = tpCache('sms.sms_time_out');
            $sms_time_out = $sms_time_out ? $sms_time_out : 180;
            $data = M('sms_log')->where(array('mobile'=>$sender,'session_id'=>$session_id , 'status'=>1))->order('id DESC')->find();
            file_put_contents('./test.log', json_encode(['mobile'=>$sender,'session_id'=>$session_id, 'data' => $data]));
            if(is_array($data) && $data['code'] == $code){
            	$data['sender'] = $sender;
            	$timeOut = $data['add_time']+ $sms_time_out;
            }else{
            	$inValid = false;
            }           
        }
        
       if(empty($data)){
           $res = array('status'=>-1,'msg'=>'请先获取验证码');
       }elseif($timeOut < time()){
           $res = array('status'=>-1,'msg'=>'验证码已超时失效');
       }elseif(!$inValid)
       {
           $res = array('status'=>-1,'msg'=>'验证失败,验证码有误');
       }else{
            $data['is_check'] = 1; //标示验证通过
            session('validate_code',$data);
            $res = array('status'=>1,'msg'=>'验证成功');
        }
        return $res;
    }
     
    
    /**
     * @time 2016/09/01
     * @author dyr
     * 设置用户系统消息已读
     */
    public function setSysMessageForRead()
    {
        $user_info = session('user');
        if (!empty($user_info['user_id'])) {
            $data['status'] = 1;
            M('user_message')->where(array('user_id' => $user_info['user_id'], 'category' => 0))->save($data);
        }
    }
    
    /**
     * 获取访问记录
     * @param type $user_id
     * @param type $p
     * @return type
     */
    public function getVisitLog($user_id, $p = 1)
    {
        $visit = M('goods_visit')->alias('v')
            ->field('v.visit_id, v.goods_id, v.visittime, g.goods_name, g.shop_price, g.cat_id')
            ->join('__GOODS__ g', 'v.goods_id=g.goods_id')
            ->where('v.user_id', $user_id)
            ->order('v.visittime desc')
            ->page($p, 20)
            ->select();

        /* 浏览记录按日期分组 */
        $curyear = date('Y');
        $visit_list = [];
        foreach ($visit as $v) {
            if ($curyear == date('Y', $v['visittime'])) {
                $date = date('m月d日', $v['visittime']);
            } else {
                $date = date('Y年m月d日', $v['visittime']);
            }
            $visit_list[$date][] = $v;
        }

        return $visit_list;
    }
    
    /**
     * 上传头像
     */
    public function upload_headpic($must_upload = true)
    {
        if ($_FILES['head_pic']['tmp_name']) {
            $file = request()->file('head_pic');
            $validate = ['size'=>1024 * 1024 * 3,'ext'=>'jpg,png,gif,jpeg'];
            $dir = 'public/upload/head_pic/';
            if (!($_exists = file_exists($dir))) {
                mkdir($dir);
            }
            $parentDir = date('Ymd');
            $info = $file->validate($validate)->move($dir, true);
            if ($info) {
                $pic_path = '/'.$dir.$parentDir.'/'.$info->getFilename();
            } else {
                return ['status' => -1, 'msg' => $info->getError()];
            }
        } elseif ($must_upload) {
            return ['status' => -1, 'msg' => "图片不存在！"];
        }
        return ['status' => 1, 'msg' => '上传成功', 'result' => $pic_path];
    }
    
    /**
     * 账户明细
     */
    public function account($user_id, $type='all'){
    	if($type == 'all'){
    		$count = M('account_log')->where("user_money!=0 and user_id=" . $user_id)->count();
    		$page = new Page($count, 16);
    		$account_log = M('account_log')->where("user_money!=0 and user_id=" . $user_id)->order('log_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
    	}else{
    		$where = $type=='plus' ? " and user_money>0 " : " and user_money<0 ";
    		$count = M('account_log')->where("user_id=" . $user_id.$where)->count();
    		$page = new Page($count, 16);
    		$account_log = M('account_log')->where("user_id=" . $user_id.$where)->order('log_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
    	}
    	$result['account_log'] = $account_log;
    	$result['page'] = $page;
    	return $result;
    }
    
    /**
     * 积分明细
     */
    public function points($user_id, $type='all')
    {
 		 if($type == 'all'){
    		$count = M('account_log')->where("user_id=" . $user_id ." and pay_points!=0 ")->count();
    		$page = new Page($count, 16);
    		$account_log = M('account_log')->where("user_id=" . $user_id." and pay_points!=0 ")->order('log_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
    	}else{
    		$where = $type=='plus' ? " and pay_points>0 " : " and pay_points<0 ";
    		$count = M('account_log')->where("user_id=" . $user_id.$where)->count();
    		$page = new Page($count, 16);
    		$account_log = M('account_log')->where("user_id=" . $user_id.$where)->order('log_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
    	}

        $result['account_log'] = $account_log;
        $result['page'] = $page;
        return $result;
    }

    /**
     * 添加用户
     * @param $user
     * @return array
     */
    public function addUser($user)
    {
        $user_count = Db::name('users')
            ->where(function($query) use ($user){
                if ($user['email']) {
                    $query->where('email',$user['email']);
                }
                if ($user['mobile']) {
                    $query->whereOr('mobile',$user['mobile']);
                }
            })
            ->count();
        if ($user_count > 0) {
            return array('status' => -1, 'msg' => '账号已存在');
        }
        $user['password'] = encrypt($user['password']);
        $user['reg_time'] = time();
        $user_id = M('users')->add($user);
        if(!$user_id){
            return array('status'=>-1,'msg'=>'添加失败');
        }else{
            $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
            if($pay_points > 0)
                accountLog($user_id, 0 , $pay_points , '会员注册赠送积分'); // 记录日志流水
            return array('status'=>1,'msg'=>'添加成功','user_id'=>$user_id);
        }
    }
}