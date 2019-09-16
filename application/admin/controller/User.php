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
 * Author: lihongshun      
 * Date: 2015-09-09
 */

namespace app\admin\controller;
use app\admin\logic\OrderLogic;
use think\AjaxPage;
use think\Page;
use think\Verify;
use think\Db;
use app\home\logic\UsersLogic;
use think\Loader;

class User extends Base {

    public function index(){
        return $this->fetch();
    }


    /**
     * 会员列表
     */
    public function ajaxindex(){


        // 搜索条件
        $condition = array();

        /**********************扩展 lishibo 20190527 三渔 二版*************************/
        $keyType = I("keytype");
        $keywords = I('keywords','','trim');

        $consignee =  ($keyType && $keyType == 'consignee') ? $keywords : I('consignee','','trim');
        $consignee ? $condition['consignee'] = trim($consignee) : false;

        $user_id = ($keyType && $keyType == 'user_id') ? $keywords : I('user_id') ;
        $user_id ? $condition['user_id'] = trim($user_id) : false;

        $mobile = ($keyType && $keyType == 'mobile') ? $keywords : I('mobile') ;
        $mobile ? $condition['mobile'] = trim($mobile) : false;

        $levelq = ($keyType && $keyType == 'levelq') ? $keywords : I('levelq') ;
        switch ($levelq){
            case "注册会员":
                $levelq=1;
                break;
            case "消费商":
                $levelq=2;
                break;
            case "代理商":
                $levelq=3;
                break;
            case "董事级":
                $levelq=4;
                break;
            case "运营中心":
                $levelq=5;
                break;
        }
        $levelq ? $condition['level'] = $levelq : false;




        $nickname = ($keyType && $keyType == 'nickname') ? $keywords : I('nickname') ;
        //$nickname ? $condition['nickname'] = trim($nickname) : false;
        $where_like = " nickname like '%".$nickname."%'"; 

        $this->assign('keytype',$keyType);
        $this->assign('keywords',$keywords);
        /**********************扩展 lishibo 20190527 三渔 二版*************************/

        I('first_leader') && ($condition['first_leader'] = I('first_leader')); // 查看一级代理人有哪些
        I('second_leader') && ($condition['second_leader'] = I('second_leader')); // 查看二级代理人有哪些
        I('third_leader') && ($condition['third_leader'] = I('third_leader')); // 查看三级代理人有哪些
        $sort_order = I('order_by').' '.I('sort');

        $model = M('users');


        if(!empty($nickname)){
            $count = $model->where($where_like)->count();
        }else{
            $count = $model->where($condition)->count();
        }

        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }

        if(!empty($nickname)){
            $userList = $model
                ->where($where_like)
                ->order($sort_order)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
        }else{
            $userList = $model
                ->where($condition)
                ->order($sort_order)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
        }

        $user_id_arr = get_arr_column($userList, 'user_id');
        if(!empty($user_id_arr))
        {
            $first_leader = DB::query("select first_leader,count(1) as count  from __PREFIX__users where first_leader in(".  implode(',', $user_id_arr).")  group by first_leader");
            $first_leader = convert_arr_key($first_leader,'first_leader');
            
            $second_leader = DB::query("select second_leader,count(1) as count  from __PREFIX__users where second_leader in(".  implode(',', $user_id_arr).")  group by second_leader");
            $second_leader = convert_arr_key($second_leader,'second_leader');            
            
            $third_leader = DB::query("select third_leader,count(1) as count  from __PREFIX__users where third_leader in(".  implode(',', $user_id_arr).")  group by third_leader");
            $third_leader = convert_arr_key($third_leader,'third_leader');            
        }

        $this->assign('first_leader',$first_leader);
        $this->assign('second_leader',$second_leader);
        $this->assign('third_leader',$third_leader);                                
        $show = $Page->show();
        //A及其伞下团队 新增业绩 复投业绩
        foreach ($userList as $val){
            $u_xia= Db::name('users')->where(" first_leader = {$val['user_id']} ")->select();
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
            $tuan_id=substr($tuan_id,0,-1);//A伞下团队所有会员的id
            $xjin = Db::name('users')//A伞下团队的新进业绩总和
                ->where('user_id','in',$tuan_id)
                ->sum('xyeji');
            $fou = Db::name('users')//A伞下团队的复投业绩总和
                ->where('user_id','in',$tuan_id)
                ->sum('fyeji');
            $val['xyeji']=$val['xyeji']+$xjin;
            $val['fyeji']=$val['fyeji']+$fou;



        }
        $this->assign('userList',$userList);
        $this->assign('level',M('user_level')->getField('level_id,level_name'));
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('pager',$Page);
        return $this->fetch();
    }
    /**
     * 会员详细信息查看
     */
    public function detail(){
        $uid = I('get.id');
        $user = D('users')->where(array('user_id'=>$uid))->find();
        if(!$user)
            exit($this->error('会员不存在'));
        if(IS_POST){
            //  会员信息编辑
            $password = I('post.password');
            $password2 = I('post.password2');
            if($password != '' && $password != $password2){
                exit($this->error('两次输入密码不同'));
            }
            if($password == '' && $password2 == ''){
                unset($_POST['password']);
            }else{
                $_POST['password'] = encrypt($_POST['password']);
            }

            if(!empty($_POST['email']))
            {   $email = trim($_POST['email']);
                $c = M('users')->where("user_id != $uid and email = '$email'")->count();
                $c && exit($this->error('邮箱不得和已有用户重复'));
            }            
            
            if(!empty($_POST['mobile']))
            {   $mobile = trim($_POST['mobile']);
                $c = M('users')->where("user_id != $uid and mobile = '$mobile'")->count();
                $c && exit($this->error('手机号不得和已有用户重复'));
            }  

            $row = M('users')->where(array('user_id'=>$uid))->save($_POST);
            if($row)
                exit($this->success('修改成功'));
            //exit($this->error('未作内容修改或修改失败'));
            exit($this->success('修改成功'));
        }
        
        $user['first_lower'] = M('users')->where("first_leader = {$user['user_id']}")->count();
        $user['second_lower'] = M('users')->where("second_leader = {$user['user_id']}")->count();
        $user['third_lower'] = M('users')->where("third_leader = {$user['user_id']}")->count();
		
//      $user['distribut_money'] = D('rebate_log')->where(array('user_id'=>$user['user_id'],'status'=>3))->sum('money');  //累计获得佣金
//		$vv['distribut_money'] = $user['distribut_money'];
//		M("users")->where("user_id", $user['user_id'])->save($vv);	

 
        $this->assign('user',$user);
        return $this->fetch();
    }
    
    public function add_user(){
    	if(IS_POST){
    		$data = I('post.');
			
			$data['username'] = $data['nickname'];
            $data['chushengriqi'] = $data['chushengriqi'];
			$data['is_distribut']  = 1;//默认是分销商
			$data['mobile_validated']  = 1;//手机已验证
			
			
			$data['user_money'] = '0.00';//给本人加5元可用余额
			
			$usermobile = $data['usermobile'];//隐传值
			
			$tjr_shibai = 1;
			
			$tjr1_user_id = 0 ;
			$tjr2_user_id = 0 ;
			$tjr3_user_id = 0 ;
			if ( strlen($data['first_leader']) > 3 ) {//如果有 推荐人手机号
				$tjr1_users = M('users')->where("mobile", $data['first_leader'])->find();
				if($tjr1_users){
					$tjr1_user_id = (int)$tjr1_users['user_id'];
					if ( (int)$tjr1_user_id == 0 ) {
						$this->error('没有找到推荐人手机号,',U('User/index'));
						$tjr_shibai = 2;
					}
					if ( (int)$tjr1_user_id > 0 ) {
						$data['first_leader'] = $tjr1_user_id; 
					} 
				}
			} else {
				//$tjr1_user_id = 1;
				//$data['first_leader'] = 1;
				$tjr1_user_id = 0;
				$data['first_leader'] = 0;
			}	
			if ( (int)$tjr1_user_id > 0 ) {		
				$tjr2_users = M('users')->where("user_id", $tjr1_user_id)->find();
				if($tjr2_users){
					
					/*M('users')->where(array('user_id' => $tjr1_user_id))->setInc('frozen_money','0.00'); //给推荐人加销售佣金
					$account_log = array(
						'user_id'       => $tjr1_user_id,
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
					
					
					$tjr2_user_id = (int)$tjr2_users['first_leader'];
					if ( (int)$tjr2_user_id > 0 ) {
						$tjr3_users = M('users')->where("user_id", $tjr2_user_id)->find();
						if($tjr3_users){
							$tjr3_user_id = (int)$tjr3_users['first_leader'];
						}
					}
				}
				
				$data['second_leader'] = $tjr2_user_id; 
				$data['third_leader'] = $tjr3_user_id; 
				
				M('users')->where(array('user_id' => $tjr1_user_id))->setInc('underling_number');
				M('users')->where(array('user_id' => $tjr2_user_id))->setInc('underling_number');
				M('users')->where(array('user_id' => $tjr3_user_id))->setInc('underling_number');
			}
			
			    //$data['user_money'] = $data['my_user_money'];//充值余额
				
			if ( $tjr_shibai ==  2 ) {
				$this->error('没有找到推荐人手机号',U('User/index'));	
			} else {
				$user_obj = new UsersLogic();
				$res = $user_obj->addUser($data);
				if($res['status'] == 1){
					//if ( $data['level'] == 2 ) {
						//$data['user_money'] = '10000.00';//增加股东会员的 余额 10000元
						accountLog($res['user_id'], $data['my_user_money'], 0,"平台充值",0);  	
					//}
					if  ( strlen($usermobile) > 1  ) {
						$this->success('添加成功','/index.php/Admin/User/my_account_edit?usermobile='.$usermobile);exit;
					} else {
						$this->success('添加成功',U('User/index'));exit;
					}
					
				}else{
					$this->error('添加失败,'.$res['msg'],U('User/index'));
				}
			}
    	}
    	return $this->fetch();
    }
    
    public function export_user(){
    	$strTable ='<table width="500" border="1">';
    	$strTable .= '<tr>';
    	$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">会员ID</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="100">会员昵称</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">会员等级</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">手机号</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">邮箱</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">注册时间</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">最后登陆</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">余额</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">积分</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">累计消费</td>';
    	$strTable .= '</tr>';
    	$count = M('users')->count();
    	$p = ceil($count/5000);
    	for($i=0;$i<$p;$i++){
    		$start = $i*5000;
    		$end = ($i+1)*5000;
    		$userList = M('users')->order('user_id')->limit($start.','.$end)->select();
    		if(is_array($userList)){
    			foreach($userList as $k=>$val){
    				$strTable .= '<tr>';
    				$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['user_id'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['nickname'].' </td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['level'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['email'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['reg_time']).'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['last_login']).'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_money'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['pay_points'].' </td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['total_amount'].' </td>';
    				$strTable .= '</tr>';
    			}
    			unset($userList);
    		}
    	}
    	$strTable .='</table>';
    	downloadExcel($strTable,'users_'.$i);
    	exit();
    }


    /**
     * @等级维护
     * @author lbo
     *
     */
    public function graded_nursing_user (){
        $model = M('users');
        $model_order = M('order');
        $usersLogic = new UsersLogic();
        set_time_limit(0);
        echo "<pre>";print_r("==============等级核算 START！！！=================");echo "<pre>";

        /*系统推荐并消费成为会员 ，未推荐下级*/
        /*他人推荐并消费成为会员，未推荐下级*/
       // M('users')->where(' first_leader >= 0 and level = 2 ' )->save(array('level' => 3));

        /**获取零元购用户id**/
       /* $order_where = " goods_price = 0 and shipping_price = 15 and pay_status=1 and order_status in(2,4) ";
        $filter_order_user_id = M('order')->where($order_where)->getField('user_id',true);
        if(is_array($filter_order_user_id)){
            $filter_user_id = M('users')->where("level = 1")->where("user_id in (".implode(',', $filter_order_user_id).')')->getField("user_id",true);
            if(is_array($filter_user_id)){ //准体验用户升级
                echo "<pre>";print_r("==============体验用户等级核算 START！！！=================");echo "<pre>";
                M('users')->where("user_id", "in" ,implode(',', $filter_user_id))->save(array('level' => 2));
                echo "<pre>";print_r("==============体验用户等级核算 END！！！=================");echo "<pre>";
            }
        }*/
        /**获取零元购用户id**/

        $userList =  M('users')->where('first_leader > 0')->select();
        if(is_array($userList)){
            foreach($userList as $k=>$val){
                $usersLogic->CheckingRelationshipLevel_Graded_Nursing($val['user_id'],1);
            }
            unset($userList);
        }
        /*更新所有会员折扣*/
        M('users')->where(' user_id > 0 ' )->save(array('discount' => 1));

        echo "<pre>";print_r("==============等级核算 END！！！=================");echo "<pre>";

    }

    /**
     * 核算用户消费姐
     */
    public function total_amount_user(){
        $orderList = M('order')->where(' pay_status=1 and order_status in(2,4) ')->select();
        if(is_array($orderList)){
            foreach($orderList as $k=>$val){
                M('users')->where("user_id", $val['user_id'])->save(array('total_amount'   => ['exp','total_amount+'.$val['total_amount']] ));
                echo "<pre>";print_r("用户编号：".$val['user_id'].'消费：'.$val['total_amount']);echo "<pre>";
            }
            unset($userList);
        }
    }


    /**
     * 用户收货地址查看
     */
    public function address(){
        $uid = I('get.id');
        $lists = D('user_address')->where(array('user_id'=>$uid))->select();
        $regionList = get_region_list();
        $this->assign('regionList',$regionList);
        $this->assign('lists',$lists);
        return $this->fetch();
    }

    /**
     * 删除会员
     */
    public function delete(){
        $uid = I('get.id');
        $row = M('users')->where(array('user_id'=>$uid))->delete();
        if($row){
            $this->success('成功删除会员');
        }else{
            $this->error('操作失败');
        }
    }
    /**
     * 删除会员
     */
    public function ajax_delete(){
        $uid = I('id');
        if($uid){
            $row = M('users')->where(array('user_id'=>$uid))->delete();
            if($row !== false){
                $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功', 'data' => ''));
            }else{
                $this->ajaxReturn(array('status' => 0, 'msg' => '删除失败', 'data' => ''));
            }
        }else{
            $this->ajaxReturn(array('status' => 0, 'msg' => '参数错误', 'data' => ''));
        }
    }

    /**
     * 账户资金记录
     */
    public function account_log(){
        $user_id = I('get.id');
        //获取类型
        $type = I('get.type');
        //获取记录总数
        $count = M('account_log')->where(array('user_id'=>$user_id))->count();
        $page = new Page($count);
        $lists  = M('account_log')->where(array('user_id'=>$user_id))->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('user_id',$user_id);
        $this->assign('page',$page->show());
        $this->assign('lists',$lists);
        return $this->fetch();
    }
	
    /**
     * 账户资金记录
     */
    public function my_account_log(){
        $map['pay_points']  = array('gt',0);  
        $count = M('account_log')->where('1=1')->count();
        $page = new Page($count);
        $lists  = M('account_log')->where('1=1')->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        //$this->assign('user_id',$user_id);
        $this->assign('page',$page->show());
        $this->assign('lists',$lists);
        return $this->fetch();
    }
	

    /**
     * 账户资金调节
     */
    public function account_edit(){
        $order_info = I('get.');
        $user_id = $order_info['user_id'];
        if(!$user_id > 0)
            $this->error("参数有误");
        $user = M('users')->field('user_id,user_money,frozen_money,pay_points,is_lock')->where('user_id',$user_id)->find();
        if(IS_POST){
            $return_info = I('post.');
            $return_id   = $return_info['return_id'];
            if(!$return_info['desc'])
                $this->error("请填写操作说明");
            //加减用户资金
            $m_op_type = I('post.money_act_type');
            $user_money = I('post.user_money/f');
            $user_money =  $m_op_type ? $user_money : 0-$user_money;

            //加减用户积分
            $p_op_type = I('post.point_act_type');
            $pay_points = I('post.pay_points/d');
            $pay_points =  $p_op_type ? $pay_points : 0-$pay_points;
            //加减冻结资金
            $f_op_type = I('post.frozen_act_type');
            $revision_frozen_money = I('post.frozen_money/f');
            if( $revision_frozen_money != 0){    //有加减冻结资金的时候
                $frozen_money =  $f_op_type ? $revision_frozen_money : 0-$revision_frozen_money;
                $frozen_money = $user['frozen_money']+$frozen_money;    //计算用户被冻结的资金
                if($f_op_type==1 and $revision_frozen_money > $user['user_money']){ $this->error("用户剩余资金不足！！");}
                if($f_op_type==0 and $revision_frozen_money > $user['frozen_money']){$this->error("冻结的资金不足！！");}
                $user_money = $f_op_type ? 0-$revision_frozen_money : $revision_frozen_money ;    //计算用户剩余资金
                M('users')->where('user_id',$user_id)->update(['frozen_money' => $frozen_money]);
            }

            if(accountLog2($user_id,$user_money,$pay_points,$return_info['desc'],0,$return_info['order_id'],$return_info['order_sn'])){
                //添加销售额
                if($user_money != 0){//如果是操作u币
                    $userq=M('Users')->where("user_id",$user_id)->find();
                    if($m_op_type==1){//如果是增加u币
                            //增加购买人的充值业绩
                        $yeji=M('Users')->where("user_id",$user_id)->setInc('chongzhi_yeji',$user_money);
                        if($yeji){
                            $rebq['buy_user_id']=$user_id;
                            $rebq['user_id']=$user_id;
                            $rebq['nickname']=$userq['nickname'];
                            $rebq['money']=$user_money;
                            $rebq['status']=3;
                            $rebq['create_time']=time();
                            $rebq['jxmc']="本人充值获得充值业绩";
                            M('rebate_log')->add($rebq);
                            $logq['user_id']=$user_id;
                            $logq['chongzhi_yeji']=$user_money;
                            $logq['change_time']=time();
                            $logq['desc']="本人充值获得充值业绩";
                            $logq['jxmc']="本人充值获得充值业绩";
                            M('account_log')->add($logq);
                            //判断是否满足升级条件
                            $first_leader = M('Users')->where("user_id",$user_id)->find();
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
                            //判断是否满足升级条件 
                        }
                        //增加上线的充值业绩
                        if($userq['first_leader']>0){//如果有上线

                            $tui=M('users')->where("user_id=".$userq['first_leader'])->find();
                            while ($tui){
                                $jiexin=M('Users')->where("user_id",$tui['user_id'])->setInc('chongzhi_yeji',$user_money);
                                if($jiexin){
                                    $rebqs['buy_user_id']=$user_id;
                                    $rebqs['user_id']=$tui['user_id'];
                                    $rebqs['nickname']=$tui['nickname'];
                                    $rebqs['money']=$user_money;
                                    $rebqs['status']=3;
                                    $rebqs['create_time']=time();
                                    $rebqs['jxmc']="下线会员（ID:".$user_id.")充值获得充值业绩";
                                    M('rebate_log')->add($rebqs);
                                    $logqs['user_id']=$tui['user_id'];
                                    $logqs['chongzhi_yeji']=$user_money;
                                    $logqs['change_time']=time();
                                    $logqs['desc']="下线会员（ID:".$user_id.")充值获得充值业绩";
                                    $logqs['jxmc']="下线会员（ID:".$user_id.")充值获得充值业绩";
                                    M('account_log')->add($logqs);
                                    //判断是否满足升级条件
                                    $shengji_s = M('Users')->where("user_id",$tui['user_id'])->find();
                                    while($shengji_s){
                                        if($shengji_s['level']!=4){
                                            //判断推荐人等级
                                            switch ($shengji_s['level']){
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
                                            $tuiCo=M('users')->where(array('first_leader'=>$shengji_s['user_id']))->count();
                                            if($tuiCo>=$zhiren){//如果直推人数满足了升级条件
                                                $where=array();
                                                $u_xia= Db::name('users')->where(" first_leader = {$shengji_s['user_id']} ")->select();
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
                                                    if($shengji_s['chongzhi_yeji']>=$xiaohe){//升级
                                                        $daqui['level']=$shengji_s['level']+1;
                                                        M('users')->where(array('user_id'=>$shengji_s['user_id']))->update($daqui);
                                                    }
                                                }
                                            }
                                        }

                                        $shengji_s = M('users')->where("user_id", $shengji_s['first_leader'])->find();
                                    }
                                    //判断是否满足升级条件
                                }
                                //拿团队总业绩的百分之多少
                                if($tui['level']>1){
                                switch($tui['level']){
                                    case 2://区级代理：可拿团队总业绩的2%
                                        $jibie=M('user_level')->where(array('level_id'=>2))->find();
                                        $bai=$jibie['commission_rate']/100;
                                        break;
                                    case 3://市级代理：可拿团队总业绩的3%
                                        $jibie=M('user_level')->where(array('level_id'=>3))->find();
                                        $bai=$jibie['commission_rate']/100;
                                        break;
                                    case 4://省级代理：可拿团队总业绩的4%
                                        $jibie=M('user_level')->where(array('level_id'=>4))->find();
                                        $bai=$jibie['commission_rate']/100;
                                        break;
                                    }
                                    $tuan_m=$user_money*$bai;
                                    $ji_m=M('Users')->where("user_id",$tui['user_id'])->setInc('chongzhi_yeji',$tuan_m);
                                    if($ji_m){
                                        $m_m['buy_user_id']=$user_id;
                                        $m_m['user_id']=$tui['user_id'];
                                        $m_m['nickname']=$tui['nickname'];
                                        $m_m['money']=$tuan_m;
                                        $m_m['status']=3;
                                        $m_m['create_time']=time();
                                        $m_m['jxmc']="下线会员（ID:".$user_id.")充值获得团队总业绩的".$jibie['commission_rate']."%";
                                        M('rebate_log')->add($m_m);
                                        $m_logs['user_id']=$tui['user_id'];
                                        $m_logs['chongzhi_yeji']=$tuan_m;
                                        $m_logs['change_time']=time();
                                        $m_logs['desc']="下线会员（ID:".$user_id.")充值获得团队总业绩的".$jibie['commission_rate']."%";
                                        $m_logs['jxmc']="下线会员（ID:".$user_id.")充值获得团队总业绩的".$jibie['commission_rate']."%";
                                        M('account_log')->add($m_logs);
                                        //判断是否满足升级条件
                                        $tt_s = M('Users')->where("user_id",$tui['user_id'])->find();
                                        while($tt_s){
                                            if($tt_s['level']!=4){
                                                //判断推荐人等级
                                                switch ($tt_s['level']){
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
                                                $tuiCo=M('users')->where(array('first_leader'=>$tt_s['user_id']))->count();
                                                if($tuiCo>=$zhiren){//如果直推人数满足了升级条件
                                                    $where=array();
                                                    $u_xia= Db::name('users')->where(" first_leader = {$tt_s['user_id']} ")->select();
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
                                                        if($tt_s['chongzhi_yeji']>=$xiaohe){//升级
                                                            $daqui['level']=$tt_s['level']+1;
                                                            M('users')->where(array('user_id'=>$tt_s['user_id']))->update($daqui);
                                                        }
                                                    }
                                                }
                                            }

                                            $tt_s = M('users')->where("user_id", $tt_s['first_leader'])->find();
                                        }
                                        //判断是否满足升级条件
                                    }
                                }
                                //拿团队总业绩的百分之多少 end
                                $tui=M('users')->where("user_id=".$tui['first_leader'])->find();
                            }
                        }
                        //增加上线的充值业绩 end
                    }else{//如果是减少u币
                        $user_money=-$user_money;
                        //减少购买人的充值业绩
                        $jiansao=M('Users')->where("user_id",$user_id)->setDec('chongzhi_yeji',$user_money);
                        if($yeji){
                            $rebq['buy_user_id']=$user_id;
                            $rebq['user_id']=$user_id;
                            $rebq['nickname']=$userq['nickname'];
                            $rebq['money']=-$user_money;
                            $rebq['status']=3;
                            $rebq['create_time']=time();
                            $rebq['jxmc']="本人充值减少充值业绩";
                            M('rebate_log')->add($rebq);
                            $logq['user_id']=$user_id;
                            $logq['chongzhi_yeji']=-$user_money;
                            $logq['change_time']=time();
                            $logq['desc']="本人充值减少充值业绩";
                            $logq['jxmc']="本人充值减少充值业绩";
                            M('account_log')->add($logq);
                        }
                        //减少上线的充值业绩
                        if($userq['first_leader']>0){//如果有上线
                            $tui=M('users')->where("user_id=".$userq['first_leader'])->find();
                            while ($tui){
                                $jiexin=M('Users')->where("user_id",$tui['user_id'])->setDec('chongzhi_yeji',$user_money);
                                if($jiexin){
                                    $rebqs['buy_user_id']=$user_id;
                                    $rebqs['user_id']=$tui['user_id'];
                                    $rebqs['nickname']=$tui['nickname'];
                                    $rebqs['money']=-$user_money;
                                    $rebqs['status']=3;
                                    $rebqs['create_time']=time();
                                    $rebqs['jxmc']="下线会员（ID:".$user_id.")充值减少充值业绩";
                                    M('rebate_log')->add($rebqs);
                                    $logqs['user_id']=$tui['user_id'];
                                    $logqs['chongzhi_yeji']=$user_money;
                                    $logqs['change_time']=time();
                                    $logqs['desc']="下线会员（ID:".$user_id.")充值减少充值业绩";
                                    $logqs['jxmc']="下线会员（ID:".$user_id.")充值减少充值业绩";
                                    M('account_log')->add($logqs);
                                }
                                $tui=M('users')->where("user_id=".$tui['first_leader'])->find();
                            }
                        }
                        //减少上线的充值业绩 end
                    }
                }
                //添加销售额 end

                if($return_id>0){  //有退货id,是订单退款，要更新退货单状态
                    $orderLogic = new OrderLogic();
                    $res = $orderLogic->alterReturnGoodsStatus($return_id,$return_info['order_id']);
                    $orderLogic->closeOrderByReturn($return_info['order_id']);
                    //如果有分成订单，取消分成 lishibo
                    M('rebate_log')->where("order_id" ,$return_info['order_id'])->save(array('status'=>4));

                    if($res)
                        $this->success("操作成功", U("Admin/order/return_info", array('id' => $return_id)));
                    $this->error("操作失败");
                }
                $this->success("操作成功",U("Admin/User/account_log",array('id'=>$user_id)));
            }else{
                $this->error("操作失败");
            }
            exit;
        }
        if($order_info['return_id']){  //有退货id,是订单退款
            $return_info = M('return_goods')->field('order_sn,order_id,goods_id,spec_key')->where('id',$order_info['return_id'])->find(); //查找退货商品信息
            $order_info=array_merge($return_info,$order_info);  //合并数值
            $order_goods= M('order_goods')->where(array_splice($return_info ,1))->find();  //去掉order_sn 后作为条件去查找
            $order_info['user_money']  =$order_goods['member_goods_price']*$order_goods['goods_num'];  //计算默认退款
        }
        $this->assign('user_id',$user_id);
        $this->assign('user',$user);
        $this->assign('order_info',$order_info);
        return $this->fetch();
    }
	
	
	
  /**
     * 账户资金调节
     */
    public function my_account_edit(){
        $order_info = I('get.');
        
        if(IS_POST){
			
            $smsLogic = new \app\common\logic\SmsLogic;
    
			
            $return_info = I('post.');
            $return_id   = $return_info['return_id'];
            if(!$return_info['user_mobile'])
                $this->error("请填写手机号");
//          if(!$return_info['duoshaoyuan'])
//                $this->error("请填写消费额");
            //if(!$return_info['pay_points'])
			//$this->error("请填写积分");
				
			$user_id = 0;
			$user = M('users')->where("mobile", $return_info['user_mobile'])->find();
			if($user){
				$user_id = (int)$user['user_id'];
				if ( (int)$user_id == 0 ) {
					$this->error("没有找到手机号");
				}
    		}
			
			
			$userid = $return_info['userid'];//消费人id
			$tuijianrenid = $return_info['tuijianrenid'];//推荐人id
			
//			$consignee = $return_info['consignee'];//收货人姓名
//			$province = $return_info['province'];//省id
//			$city = $return_info['city'];//市id
//			$district = $return_info['district'];//县id
//			$twon = $return_info['twon'];//乡镇id
//			$address = $return_info['address'];//详细地址
//			
//			$address_id = 0 ;
//			$myaddressdata = M('user_address')->where(array('user_id'=>$user_id,'province'=>$province,'city'=>$city,'district'=>$district,'address'=>$address,'consignee'=>$consignee))->find();
//			if( (int)$myaddressdata['address_id'] > 0 ) {
//				$address_id = $myaddressdata['address_id'];
//			} else {
//				//插入地址
//				$post['user_id']=$user_id;
//				$post['consignee']=$consignee;
//				$post['province']=(int)$province;
//				$post['city']=(int)$city;
//				$post['district']=(int)$district;
//				$post['twon']=(int)$twon;
//				$post['address']=$address;
//				$post['mobile']=$return_info['user_mobile'];
//				$address_id = M('user_address')->add($post);
//			}			
			
			$jiaoyizongjia = $return_info['jiaoyizongjia'];//交易总价
			$zongtjryj = $return_info['zongtjryj'];//一级佣金
			$zongssjyj = $return_info['zongssjyj'];//二级佣金
			$zongsssjyj = $return_info['zongsssjyj'];//三级佣金
			
			
			//加减用户资金
            $m_op_type = 0;//减少余额
            $user_money = I('post.user_money/f');
            $user_money =  $m_op_type ? $jiaoyizongjia : 0-$jiaoyizongjia;
			
			
//			if (  $return_info['jiaoyizongjia'] > $user['user_money']  ) {
//				$this->error("余额不足！");
//			} else {
//				$my_user_money = $user['user_money']-$user_money;    //计算用户被冻结的资金
//				M('users')->where('user_id',$user_id)->update(['user_money' => $my_user_money]);			
//			}
			
			
            
			$shangpinmingarr = explode("</br>", implode("</br>",$return_info['shangpinming']) );
			$chengjiaojiaarr = explode("</br>", implode("</br>",$return_info['chengjiaojia']) );
			$tjryjarr = explode("</br>", implode("</br>",$return_info['tjryj']) );
			$desc = '购买：';
			//第一种遍历方式,只适用于索引数组。PHP数组在没有指明key的情况下，默认是索引数组  
			for ($i = 0; $i < sizeof($shangpinmingarr); $i++) {  
				if ( strlen($shangpinmingarr[$i]) > 1 ) {
					//所购商品入库
					$desc.= trim($shangpinmingarr[$i])." ；";
				} else {
					continue;
				} 
			}
			
			
            //向 account_log 表写数据  
            if(my_accountLog($user_id,0,0,$desc,0,0,'',$address_id,$jiaoyizongjia)){
/*会员分佣开始*/

							/* 发送短信_给消费本人 */
							
							//普通用户消费, 发送短信
							
									$res = checkEnableSendSms("3");
									$sender = $user['mobile'];
									if($res && $res['status'] ==1 && !empty($sender)  ){
										$params['user_name'] = $user['username'];
										$params['duoshaoyuan'] =  $jiaoyizongjia;
										$params['mobile'] = $user['mobile'];
										$params = array('user_name'=>$user['username'] , 'duoshaoyuan'=>$jiaoyizongjia , 'mobile' => $user['mobile'] );
										$resp = sendSms("3", $sender, $params, $user['user_id'] );
									}
									
							/* 发送短信_给消费本人 */
									
									
									
										 // 一级 分销商赚 的钱. 小于一分钱的 不存储
										 if($user['first_leader'] > 0 && $zongtjryj > 0.01)
										 {
											$data = array(             
												'user_id' =>$user['first_leader'],
												'buy_user_id'=>$user['user_id'],
												'nickname'=>$user['nickname'],
												'goods_price' => $jiaoyizongjia,
												'money' => $zongtjryj,
												'level' => 1,
												'status' => 3,//已分成
												'shifouhuiyuanfenyong' => 1,
												'create_time' => time(), 
												           
											);                  
											M('rebate_log')->add($data); //向 rebate_log  分成日志 表 写入数据
											

											my_accountLog($user['first_leader'], $zongtjryj, 0,"您的推荐 ".$user['nickname']." 消费购物产生的分佣",$zongtjryj,0,'',0,0);      
											/* 发送短信_一级代理 */
											$user_1 = M('users')->where("user_id", $user['first_leader'])->find();
											if($user_1){

												$res = checkEnableSendSms("10");
												$sender = $user_1['mobile'];
												//if($res && $res['status'] ==1 && !empty($sender)){
													//if ($sender != '15314170988') {  
													    $params['user_name'] = $user['username'];
														$params['sms_sign'] =  '三渔工坊创业联盟';
														$params['duoshaoyongjin'] = $zongtjryj;
														$params = array('user_name'=>$user['nickname'] ,'sms_sign'=>'三渔工坊创业联盟','duoshaoyongjin'=>$zongtjryj  );
														$resp = sendSms("10", $sender, $params, $user['first_leader'] );
													//}
												//}
											}
											/* 发送短信_一级代理 */
											
										 }
										 
										 
										 
										 
										  // 二级 分销商赚 的钱.
											 if($user['second_leader'] > 0 && $zongssjyj > 0.01)
											 {         
												$data = array(
													'user_id' =>$user['second_leader'],
													'buy_user_id'=>$user['user_id'],
													'nickname'=>$user['nickname'],
													'goods_price' => $jiaoyizongjia,
													'money' => $zongssjyj,
													'level' => 2,
													'status' => 3,//已分成
													'shifouhuiyuanfenyong' => 1,
													'create_time' => time(),             
												);                  
												M('rebate_log')->add($data);
												my_accountLog($user['second_leader'], $zongssjyj, 0,"您的二级代理 ".$user['nickname']." 消费购物产生的分佣",$zongssjyj,0,'',0,0);      

												/* 发送短信_二级代理 */
												$user_2 = M('users')->where("user_id", $user['second_leader'])->find();
												if($user_2){
													
													$res = checkEnableSendSms("10");
													$sender = $user_2['mobile'];
													//if($res && $res['status'] ==1 && !empty($sender)){
														//if ($sender != '15314170988') {  
															$params['user_name'] = $user['username'];
															$params['sms_sign'] =  '三渔工坊创业联盟';
															$params['duoshaoyongjin'] = $zongssjyj;
															$params = array('user_name'=>$user['nickname'] ,'sms_sign'=>'三渔工坊创业联盟','duoshaoyongjin'=>$zongssjyj  );
															$resp = sendSms("10", $sender, $params, $user['second_leader'] );
														//}
													//}
													
													
												}
												/* 发送短信_二级代理 */
											 }
											 
											 
												// 三级 分销商赚 的钱.
											 if($user['third_leader'] > 0 && $zongsssjyj > 0.01)
											 {         
												$data = array(
													'user_id' =>$user['third_leader'],
													'buy_user_id'=>$user['user_id'],
													'nickname'=>$user['nickname'],
													'goods_price' => $jiaoyizongjia,
													'money' => $zongsssjyj,
													'level' => 3,
													'status' => 3,//已分成
													'shifouhuiyuanfenyong' => 1,
													'create_time' => time(),             
												);                  
												M('rebate_log')->add($data);
												my_accountLog($user['third_leader'], $zongssjyj, 0,"您的三级代理 ".$user['nickname']." 消费购物产生的分佣",$zongsssjyj,0,'',0,0);      

												/* 发送短信_三级代理 */
												$user_3 = M('users')->where("user_id", $user['third_leader'])->find();
												if($user_3){
													
													$res = checkEnableSendSms("10");
													$sender = $user_3['mobile'];
													//if($res && $res['status'] ==1 && !empty($sender)){
														//if ($sender != '15314170988') {  
															$params['user_name'] = $user['username'];
															$params['sms_sign'] =  '三渔工坊创业联盟';
															$params['duoshaoyongjin'] = $zongsssjyj;
															$params = array('user_name'=>$user['nickname'] ,'sms_sign'=>'三渔工坊创业联盟','duoshaoyongjin'=>$zongsssjyj  );
															$resp = sendSms("10", $sender, $params, $user['third_leader'] );
														//}
													//}
													
													
												}
												/* 发送短信_二级代理 */
											 }												
				   
				 
				 
/*会员分佣结束*/	
                //$this->success("操作成功",U("Admin/User/account_log",array('id'=>$user_id)));
				
                $this->success("操作成功",U("Admin/User/my_account_log")); //账户金额记录列表
				
            }else{
                $this->error("操作失败");
            }
            exit;
        }
		
		$province = M('region')->where(array('parent_id'=>0,'level'=> 1))->field('id,name')->select();
    	$this->assign('province',$province);
		
		$where = ' 1 = 1 '; // 搜索条件 
		$goodsList = M('Goods')->where($where)->order('convert(goods_name using gb2312) asc')->select();
    	$this->assign('goodsList',$goodsList);
		
		
		$mygoodsList='<option  value="0">选择商品</option>';
		foreach($goodsList as $k=>$val){ 
			$mygoodsList.= '<option  value="'. $val["goods_id"] .'|'. $val["shop_price"] .'">' . $val["goods_name"] . '</option>';
		} 
    	$this->assign('mygoodsList',$mygoodsList);
		

        return $this->fetch();
    }
    	
    
    public function recharge(){
    	$timegap = I('timegap');
    	$nickname = I('nickname');
    	$map = array();
    	if($timegap){
    		$gap = explode(' - ', $timegap);
    		$begin = $gap[0];
    		$end = $gap[1];
    		$map['ctime'] = array('between',array(strtotime($begin),strtotime($end)));
    	}
    	if($nickname){
    		$map['nickname'] = array('like',"%$nickname%");
    	}  	
    	$count = M('recharge')->where($map)->count();
    	$page = new Page($count);
    	$lists  = M('recharge')->where($map)->order('ctime desc')->limit($page->firstRow.','.$page->listRows)->select();
    	$this->assign('page',$page->show());
        $this->assign('pager',$page);
    	$this->assign('lists',$lists);
    	return $this->fetch();
    }
    
    public function level(){
    	$act = I('get.act','add');
    	$this->assign('act',$act);
    	$level_id = I('get.level_id');
    	if($level_id){
    		$level_info = D('user_level')->where('level_id='.$level_id)->find();
    		$this->assign('info',$level_info);
    	}
    	return $this->fetch();
    }
    
    public function levelList(){
    	$Ad =  M('user_level');
        $p = $this->request->param('p');
    	$res = $Ad->order('level_id')->page($p.',10')->select();
    	if($res){
    		foreach ($res as $val){
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);
    	$count = $Ad->count();
    	$Page = new Page($count,10);
    	$show = $Page->show();
    	$this->assign('page',$show);
    	return $this->fetch();
    }

    /**
     * 会员等级添加编辑删除
     */
    public function levelHandle()
    {
        $data = I('post.');
        $userLevelValidate = Loader::validate('UserLevel');
        $return = ['status' => 0, 'msg' => '参数错误', 'result' => ''];//初始化返回信息
        if ($data['act'] == 'add') {
            if (!$userLevelValidate->batch()->check($data)) {
                $return = ['status' => 0, 'msg' => '添加失败', 'result' => $userLevelValidate->getError()];
            } else {
                $r = D('user_level')->add($data);
                if ($r !== false) {
                    $return = ['status' => 1, 'msg' => '添加成功', 'result' => $userLevelValidate->getError()];
                } else {
                    $return = ['status' => 0, 'msg' => '添加失败，数据库未响应', 'result' => ''];
                }
            }
        }
        if ($data['act'] == 'edit') {
            if (!$userLevelValidate->scene('edit')->batch()->check($data)) {
                $return = ['status' => 0, 'msg' => '编辑失败', 'result' => $userLevelValidate->getError()];
            } else {
                $r = D('user_level')->where('level_id=' . $data['level_id'])->save($data);
                if ($r !== false) {
                    $return = ['status' => 1, 'msg' => '编辑成功', 'result' => $userLevelValidate->getError()];
                } else {
                    $return = ['status' => 0, 'msg' => '编辑失败，数据库未响应', 'result' => ''];
                }
            }
        }
        if ($data['act'] == 'del') {
            $r = D('user_level')->where('level_id=' . $data['level_id'])->delete();
            if ($r !== false) {
                $return = ['status' => 1, 'msg' => '删除成功', 'result' => ''];
            } else {
                $return = ['status' => 0, 'msg' => '删除失败，数据库未响应', 'result' => ''];
            }
        }
        $this->ajaxReturn($return);
    }

    /**
     * 搜索用户名
     */
    public function search_user()
    {
        $search_key = trim(I('search_key'));        
        if(strstr($search_key,'@'))    
        {
            $list = M('users')->where(" email like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['email']}</option>";
            }                        
        }
        else
        {
            $list = M('users')->where(" mobile like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['mobile']}</option>";
            }            
        } 
        exit;
    }
    
    /**
     * 分销树状关系
     */
    public function ajax_distribut_tree()
    {
          $list = M('users')->where("first_leader = 1")->select();
          return $this->fetch();
    }

    /**
     *
     * @time 2016/08/31
     * @author dyr
     * 发送站内信
     */
    public function sendMessage()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $users = M('users')->field('user_id,nickname')->where(array('user_id' => array('IN', $user_id_array)))->select();
        }
        $this->assign('users',$users);
        return $this->fetch();
    }

    /**
     * 发送系统消息
     * @author dyr
     * @time  2016/09/01
     */
    public function doSendMessage()
    {
        $call_back = I('call_back');//回调方法
        $text= I('post.text');//内容
        $type = I('post.type', 0);//个体or全体
        $admin_id = session('admin_id');
        $users = I('post.user/a');//个体id
        $message = array(
            'admin_id' => $admin_id,
            'message' => $text,
            'category' => 0,
            'send_time' => time()
        );

        if ($type == 1) {
            //全体用户系统消息
            $message['type'] = 1;
            M('Message')->add($message);
        } else {
            //个体消息
            $message['type'] = 0;
            if (!empty($users)) {
                $create_message_id = M('Message')->add($message);
                foreach ($users as $key) {
                    M('user_message')->add(array('user_id' => $key, 'message_id' => $create_message_id, 'status' => 0, 'category' => 0));
                }
            }
        }
        echo "<script>parent.{$call_back}(1);</script>";
        exit();
    }

    /**
     *
     * @time 2016/09/03
     * @author dyr
     * 发送邮件
     */
    public function sendMail()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $user_where = array(
                'user_id' => array('IN', $user_id_array),
                'email' => array('neq', '')
            );
            $users = M('users')->field('user_id,nickname,email')->where($user_where)->select();
        }
        $this->assign('smtp', tpCache('smtp'));
        $this->assign('users', $users);
        return $this->fetch();
    }

    /**
     * 发送邮箱
     * @author dyr
     * @time  2016/09/03
     */
    public function doSendMail()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $title = I('post.title');//标题
        $users = I('post.user/a');
        $email= I('post.email');
        if (!empty($users)) {
            $user_id_array = implode(',', $users);
            $users = M('users')->field('email')->where(array('user_id' => array('IN', $user_id_array)))->select();
            $to = array();
            foreach ($users as $user) {
                if (check_email($user['email'])) {
                    $to[] = $user['email'];
                }
            }
            $res = send_email($to, $title, $message);
            echo "<script>parent.{$call_back}({$res['status']});</script>";
            exit();
        }
        if($email){
            $res = send_email($email, $title, $message);
            echo "<script>parent.{$call_back}({$res['status']});</script>";
            exit();
        }
    }

    /**
     * 提现申请记录
     */
    public function withdrawals()
    {
        $model = M("withdrawals");
        $_GET = array_merge($_GET,$_POST);
        unset($_GET['create_time']);

        $status = I('status');
        $user_id = I('user_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');
        $create_time = I('create_time');
        $create_time = $create_time  ? $create_time  : date('Y/m/d',strtotime('-1 year')).'-'.date('Y/m/d',strtotime('+1 day'));
        $create_time2 = explode('-',$create_time);
        $this->assign('start_time', $create_time2[0]);
        $this->assign('end_time', $create_time2[1]);
        $where = " create_time >= '".strtotime($create_time2[0])."' and create_time <= '".strtotime($create_time2[1])."' ";

        if($status === '0' || $status > 0)
            $where .= " and status = $status ";
        $user_id && $where .= " and user_id = $user_id ";
        $account_bank && $where .= " and account_bank like '%$account_bank%' ";
        $account_name && $where .= " and account_name like '%$account_name%' ";

        $count = $model->where($where)->count();
        $Page  = new Page($count,16);
        $list = $model->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('create_time',$create_time);
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('pager',$Page);
        foreach ($list as& $vl){
            $vl['money']=$vl['money']*0.9;
        }
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        return $this->fetch();
    }
    /**
     * 删除申请记录
     */
    public function delWithdrawals()
    {
        $model = M("withdrawals");
        $model->where('id ='.$_GET['id'])->delete();
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
        $this->ajaxReturn($return_arr);
    }

    /**
     * 修改编辑 申请提现
     */
    public function editWithdrawals()
    {
        $id = I('id');
        $withdrawals = DB::name('withdrawals')->where('id',$id)->find();
        $user = M('users')->where("user_id = {$withdrawals[user_id]}")->find();
        if (IS_POST) {
            $data = I('post.');
            // 如果是已经给用户转账 则生成转账流水记录
            if ($data['status'] == 1 && $withdrawals['status'] != 1) {
                if ($user['user_money'] < $withdrawals['money']) {
                    $this->error("可用余额不足{$withdrawals['money']}，不够提现");
                    exit;
                }
                accountLog($withdrawals['user_id'], ($withdrawals['money'] * -1), 0, "平台提现");
                $remittance = array(
                    'user_id' => $withdrawals['user_id'],
                    'bank_name' => $withdrawals['bank_name'],
                    'account_bank' => $withdrawals['account_bank'],
                    'account_name' => $withdrawals['account_name'],
                    'money' => $withdrawals['money'],
                    'status' => 1,
                    'create_time' => time(),
                    'admin_id' => session('admin_id'),
                    'withdrawals_id' => $withdrawals['id'],
                    'remark' => $data['remark'],
                );
                M('remittance')->add($remittance);
            }
            DB::name('withdrawals')->update($data);
            $this->success("操作成功!", U('Admin/User/remittance'), 3);
            exit;
        }

        if ($user['nickname'])
            $withdrawals['user_name'] = $user['nickname'];
        elseif ($user['email'])
            $withdrawals['user_name'] = $user['email'];
        elseif ($user['mobile'])
            $withdrawals['user_name'] = $user['mobile'];
        $this->assign('user', $user);
        $this->assign('data', $withdrawals);
        return $this->fetch();
    }

    public function withdrawals_update(){
        $id = I('id/a');
        $status = I('status');
        $withdrawals = M('withdrawals')->where('id','in', $id)->select();
        if($status == 1){
            $r = M('withdrawals')->where('id','in', $id)->save(array('status'=>$status,'check_time'=>time()));
        }else if($status == -1){
            $r = M('withdrawals')->where('id','in', $id)->save(array('status'=>$status,'refuse_time'=>time()));
        }else if($status == 2){
            foreach($withdrawals as $val){
                $user = M('users')->where(array('user_id'=>$val['user_id']))->find();
                if($user['user_money'] < $val['money'])
                {
                    $data['status'] = -2;
                    $data['remark'] = '可用余额不足';
                    M('withdrawals')->where(array('id'=>$val['id']))->save($data);
                }else{
                    if($val['bank_name'] == '支付宝 '){
                        //流水号1^收款方账号1^收款账号姓名1^付款积分1^备注说明1|流水号2^收款方账号2^收款账号姓名2^付款积分2^备注说明2
                        $alipay['batch_no'] = time();
                        $alipay['batch_fee'] += $val['money'];
                        $alipay['batch_num'] += 1;
                        $str = isset($alipay['detail_data']) ? '|' : '';
                        $alipay['detail_data'] .= $str.$val['pay_code'].'^'.$val['account_bank'].'^'.$val['realname'].'^'.$val['money'].'^'.$val['remark'];
                    }
                    if($val['bank_name'] == '微信'){
                        $wxpay = array(
                            'userid' => $val['user_id'],//用户ID做更新状态使用
                            'openid' => $val['account_bank'],//收钱的人微信 OPENID
                            'pay_code'=>$val['pay_code'],//提现申请ID
                            'money' => $val['money'],//积分
                            'desc' => '恭喜您提现申请成功!'
                        );
                        $res = $this->transfer('weixin',$wxpay);//微信在线付款转账
                        if($res['partner_trade_no']){
                            accountLog($val['user_id'], ($val['money'] * -1), 0,"平台处理用户提现申请");
                            $r = M('withdrawals')->where(array('id'=>$val['id']))->save(array('status'=>$status,'pay_time'=>time()));
                        }else{
                            $this->ajaxReturn(array('status'=>0,'msg'=>$res['msg']),'JSON');
                        }
                    }
                }
            }
            if(!empty($alipay)){
                $this->transfer('alipay',$alipay);
            }
            $this->ajaxReturn(array('status'=>1,'msg'=>"操作成功"),'JSON');
        }else if($status == 3){
            $r = M('withdrawals')->where('id in ('.implode(',', $id).')')->delete();
        }else{
            accountLog($val['user_id'], ($val['money'] * -1), 0,"管理员处理用户提现申请");//手动转账，默认视为已通过线下转方式处理了该笔提现申请
            $r = M('withdrawals')->where('id in ('.implode(',', $id).')')->save(array('status'=>2,'pay_time'=>time()));
        }
        if($r){
            $this->ajaxReturn(array('status'=>1,'msg'=>"操作成功"),'JSON');
        }else{
            $this->ajaxReturn(array('status'=>0,'msg'=>"操作失败"),'JSON');
        }

    }

    public function transfer($atype,$data){
        if($atype == 'weixin'){
            include_once  PLUGIN_PATH."payment/weixin/weixin.class.php";
            $wxpay_obj = new \weixin();
            return $wxpay_obj->transfer($data);
        }else{
            //支付宝在线批量付款
            include_once  PLUGIN_PATH."payment/alipay/alipay.class.php";
            $alipay_obj = new \alipay();
            return $alipay_obj->transfer($data);
        }
    }
    /**
     *  转账汇款记录
     */
    public function remittance(){
        $model = M("remittance");
        $_GET = array_merge($_GET,$_POST);
        unset($_GET['create_time']);

        $user_id = I('user_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');

        $create_time = I('create_time');
        $create_time = $create_time  ? $create_time  : date('Y-m-d',strtotime('-1 year')).' - '.date('Y-m-d',strtotime('+1 day'));
        $create_time2 = explode(' - ',$create_time);
        $this->assign('start_time',$create_time2[0]);
        $this->assign('end_time',$create_time2[1]);
        $where = " create_time >= '".strtotime($create_time2[0])."' and create_time <= '".strtotime($create_time2[1])."' ";
        $user_id && $where .= " and user_id = $user_id ";
        $account_bank && $where .= " and account_bank like '%$account_bank%' ";
        $account_name && $where .= " and account_name like '%$account_name%' ";

        $count = $model->where($where)->count();
        $Page  = new Page($count,16);
        $list = $model->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('pager',$Page);
        $this->assign('create_time',$create_time);
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        return $this->fetch();
    }
}