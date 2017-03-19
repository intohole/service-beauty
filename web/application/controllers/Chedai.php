<?php

class ChedaiController extends Yaf_Controller_Abstract {
    private $logic;
    private $tpl;
    private $req;
    private $_user;
    private $_appModel;
    private $_userModel;
    private $_carModel;
    private $redis;
    private $user_role = [];

    public function init() {
        $this->tpl = $this->getView();
        $this->req = $this->getRequest();
        $this->_loginUser = Session_AdminFengkong::instance();
        $this->_user = new AdminUserModel();
        $this->logic = new ChedaiModel();
        $this->_appModel = new Cd_AppModel();
        $this->_userModel = new Cd_UserModel();
        $this->_carModel = new Cd_CarModel();
        $this->_auditModel = new Cd_AuditInfoModel();
        $this->_userAuditModel = new Cd_UserAuditInfoModel();
        $this->_LegalAuditModel = new Cd_AppLegalAuditModel();
        $this->redis = new Utils_Redis();
    }

    private $role_url_test = array(
            26=>"/chedai/salesman_before",
            27=>"/chedai/appraiser_list",
            28=>"/chedai/manager_list",
            29=>"/chedai/manager_list",
            30=>"/chedai/manager_list",
            31=>"/chedai/manager_list",
            32=>"/chedai/manager_list",
    );

    private $role_url_develop = array(
            28=>"/chedai/salesman_before",
            29=>"/chedai/appraiser_list",
            30=>"/chedai/manager_list",
            31=>"/chedai/manager_list",
            32=>"/chedai/manager_list",
            33=>"/chedai/manager_list",
            34=>"/chedai/manager_list",
    );

    public $user_params = array('type','username','idcardfront','idcardback','drivinglicence','sexual','birthday','marry','phone','email','education','household','driving','employee','client','credit','litigation','sin','impression','career','agelimit','unit','position','earning','house','company','registeraddress','livingaddress','idcard');

    public $car_params = array('type','carNameBid','carNameSid','carNameSpecid','color','kilo','check-day','run-day','car-numb1','car-numb2','car-page-1','car-page-2','car-page-3','gear','orgin','litre','car-source','dealvalue');

    public function indexAction(){
        if(!$this->_loginUser->isLogin()){
            $url = 'http://'.Yaf_Application::app()->getConfig()->get("website")['host'].'/chedai/login';
            header("Location:".$url);
            return false;
        }

    }


    public function redirectAction(){
        $uid = $this->_loginUser->getUid();
        $roles = (new RoleModel())->getUserRolesOrderByRole($uid);
        foreach($roles as $r){
            $this->user_role[] = $r['id'];
        }

        $role_url = ini_get("yaf.environ") == 'test' ? $this->role_url_test : $this->role_url_develop;
        $flow = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test : $this->logic->role_nodes_develop;
    	$nodes = $this->logic->nodes;
    	$roles_array = array_keys($role_url);

    	if (count($this->user_role)==1 && in_array($this->user_role[0], $roles_array)){
            $url = $role_url[$this->user_role[0]]; //用户只有一个角色
            $url = 'http://'.Yaf_Application::app()->getConfig()->get("website")['host'].$url.'?user_role='.$this->user_role[0];
            header('Location:'.$url);
            return false;
    	} else {
            foreach ($this->user_role as $value) { //用户有多个角色
                if(in_array($value, $roles_array)){
                    $url_arr[] = array('role'=>$nodes[$flow[$value]], 'url'=>$role_url[$value].'?user_role='.$value);
                }
            }
            if($url_arr){
                foreach($url_arr as $k=>$v){
                    $url_arr[$k] = implode(',', $v);
                }
                $url = 'http://'.Yaf_Application::app()->getConfig()->get("website")['host'].'/chedai/authority?data='.urlencode(implode(';', $url_arr));
                header('Location:'.$url);
                return false;
            }else{
                Utils_Output::merrorMsg('该用户没有以租代购角色');
            }
    	}
    }

    
	//登录
	public function loginAction() {
            if($this->_loginUser->isLogin()){
                $this->_loginUser->setLogout();
                setcookie('backfkauthv10','',-1,'/', $this->_host);
            } 
            $forward = (string)$this->getRequest()->get("forward", null);
            $forward = Utils_FilterXss::filterXss($forward);//过滤xss
            $this->tpl->assign("forward", $forward);
            return true;
	}
        
	//登陆ajax判断
	public function loginAjaxAction(){

            $username = $this->getRequest()->getPost("loginname", "");
            $password = trim($this->getRequest()->getPost("password", ""));
            
            if(empty($username) || empty($password)){
                Utils_Output::jsonResponse(101,"参数缺失");
            }
            $info = $this->_user->findUser($username);
            
            if (md5(md5($password).$info['salt']) == $info['passwd']) {
             
                $this->_loginUser->setLogin($info['id'], $info['phone'], null, time()+3600*24*30, $info['realname']);
                $roles = (new RoleModel())->getUserRolesOrderByRole($info['id']);
                foreach($roles as $r){
                    $this->user_role[] = $r['id'];
                }

                $role_url = ini_get("yaf.environ") == 'test' ? $this->role_url_test : $this->role_url_develop;
                $flow = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test : $this->logic->role_nodes_develop;
                $nodes = $this->logic->nodes;
                $roles_array = array_keys($role_url);

                if(count($this->user_role)==1 && in_array($this->user_role[0], $roles_array)){
                        $url = $role_url[$this->user_role[0]]; //用户只有一个角色
                        
                    Utils_Output::jsonResponse(0,$url.'?user_role='.$this->user_role[0],"登录成功");
                } else if(count($this->user_role) == 0){
                   
                        Utils_Output::jsonResponse(101,'',"该用户未分配角色"); //用户没有任何角色
                } else {
                        foreach ($this->user_role as $value) { //用户有多个角色
                                if(in_array($value, $roles_array)){
                                        $url_arr[] = array('role'=>$nodes[$flow[$value]], 'url'=>$role_url[$value].'?user_role='.$value);
                                }
                        }
                        if($url_arr){
                                foreach($url_arr as $k=>$v){
                                        $url_arr[$k] = implode(',', $v);
                                }
                                 
                                Utils_Output::jsonResponse(1,urlencode(implode(';', $url_arr)),"选择角色");
                        }else{
                            
                                Utils_Output::jsonResponse(101,'',"该用户未分配角色"); //用户没有任何角色
                        }
                }

            }else {
                //登录错误次数校验
                $key = "loginAjax_{$username}";
                $res = $this->_redisVerify($key,10,1800);//半小时内密码输入错误10次
                if(!$res){
                    Utils_Output::jsonResponse(103,'',"手机号或密码错误次数过多，请稍后再试");
                }else{
                    Utils_Output::jsonResponse(102,'',"手机号或密码错误");
                }
            }
	}

    //权限选择页面
    public function authorityAction(){
    	$data = urldecode(htmlspecialchars($this->req->get("data", '')));
    	$data = explode(";", $data);
    	foreach($data as $k=>$v){
    		$data[$k] = explode(',', $v);
    	}
    	$this->tpl->assign("data", $data);
    	$this->tpl->display();
    }

	//退出方法
	public function logoutAction(){
		$this->_loginUser->setLogout();
        header("Location: /chedai/login");
        return false;
	}

	//个人中心页面
	public function userAction() {
            //print_r($this->_loginUser);exit;
            //查询用户信息
			$uid = $this->_loginUser->uid;
            $user_mod = new AdminUserModel();
            $user = $user_mod->get($uid);
           
            //查询角色信息
            $role_mod = new RoleModel();
            $role = $role_mod->getUserRoles($uid);

            $role_id = $this->redis->get("user_role_".$uid);
            // $role_id = $_COOKIE['user_role_'.$uid];
            foreach($role as $key=>$value){
            	if($value['id'] == $role_id)
            		$role_key = $key;
            }
            $share_url = ini_get('yaf.environ')=='test' ? 'http://fk.fangwudiya.com/chedai/landing?id='.$uid : 'http://fk.yianjinrong.com/chedai/landing?id='.$uid;
            
           	$this->tpl->assign('share_url', $share_url);
           	$this->tpl->assign('id', $uid);
            $this->tpl->assign('user', $user);
            $this->tpl->assign('role', $role[$role_key]);
            
	}

	//业务员-首页
	public function salesman_indexAction() {
		$user_role = (int)$this->req->get('user_role', "");
		if(!$user_role){
			header('Location: /chedai/index');
			return false;
		}
		$this->_checkLogin($user_role);
	}
	//业务员-车辆业务-引导页
	public function salesman_serviceAction() {

	}
	
	//业务员-车辆业务-贷前管理
	public function salesman_beforeAction() {
		$user_role = (int)$this->req->get('user_role', "");
		if(!$user_role){
			header('Location: /chedai/index');
			return false;
		}
		$this->_checkLogin($user_role);
	}

	//业务员-车辆业务-贷前管理-客户信息管理列表
	public function salesman_customerlistAction() {

	}

	//业务员-车辆业务-贷前管理-客户信息管理列表ajax
	public function salesman_customerlistajaxAction(){
		$tab = (int)$this->getRequest()->getPost("list", "");

		$page = (int)$this->getRequest()->getPost("page",1);
		$uid = $this->_loginUser->getUid();
		$launch = $this->getLaunch();

		$data = $this->_userModel->getListByCreatorAndTab($uid, $tab, $page);
		foreach($data as $k=>$v){
			if($launch==2){
				$data[$k]['marry'] = '';
			}
		}
		if(!$data){
			Utils_Output::jsonResponse(101, '', '无更多数据');
		}
		foreach($data as $key=>$value){
			$data[$key]['age'] = $this->_userModel->getAge($value['birthday']);
			$data[$key]['time'] = date('Y.m.d', $value['time']);
		}
		Utils_Output::jsonResponse(0, $data,"获取成功");
	}

	//业务员-车辆业务-贷前管理-添加客户信息
	public function salesman_customeraddAction() {
		$uid = (int)$this->getRequest()->get('id', '');//客户id
		$creator = $this->_loginUser->getUid();
		$oid = $this->_user->get($creator)['oid'];
		$org_xm = ini_get("yaf.environ") == 'test' ? array(38, 39, 41) : array(38, 39, 41);
		if(in_array($oid, $org_xm)){
			$launch = 1;
			if($uid){
				$data = $this->_userModel->getInfo($uid);
				$this->tpl->assign('uid', $uid);
				$this->tpl->assign('data', $data);
			}else{
				$param_array = $this->user_params;
				$user_id = $this->_loginUser->getUid();
				$redis_info = $this->redis->get("redis_user_info_".$user_id);
		    	$info = json_decode($redis_info);
		    	foreach($info as $k=>$v){
		    		foreach($param_array as $value){
		    			if($k == $value){
		    				$getParam[$k] = $v;
		    			}
		    		}
		    	}
		    	$getParam = $this->idToImg($getParam);
		    	$this->tpl->assign('redis_info', $getParam);
			}
		}else{
			$launch = 2;
			if($uid){
				$data = $this->_userModel->getInfo($uid);
				$this->tpl->assign('uid', $uid);
				$this->tpl->assign('data', $data);
			}
		}
		
		$this->tpl->assign('launch', $launch);
		$this->tpl->display();
	}

	//业务员-车辆业务-贷前管理-添加客户信息ajax方法
	public function salesman_customeraddajaxAction() {
		$uid = $this->_loginUser->getUid();

		$oid = $this->_user->get($uid)['oid'];
		$org_xm = ini_get("yaf.environ") == 'test' ? array(38, 39, 41) : array(38, 39, 41);
		if(in_array($oid, $org_xm)){
			$data['creator'] = (int)$this->_loginUser->getUid();
			$data['type'] = $this->getRequest()->getPost('type','') ? $this->getRequest()->getPost('type','') : Utils_Output::jsonResponse(101, '', '业务类型不能为空');
			$data['name'] = $this->getRequest()->getPost('username','') ? $this->getRequest()->getPost('username','') : Utils_Output::jsonResponse(101, '', '用户名不能为空');
			$data['idcard'] = $this->getRequest()->getPost('idcard','') ? $this->getRequest()->getPost('idcard','') : Utils_Output::jsonResponse(101, '', '用户名不能为空');
			$data['idcard_positive_fileid'] = $this->getRequest()->getPost('idcardfront','');
			$data['idcard_reverse_fileid'] = $this->getRequest()->getPost('idcardback','');
			$data['drivinglicence_fileid'] = $this->getRequest()->getPost('drivinglicence','');
			$data['gender'] = $this->getRequest()->getPost('sexual', 1);
			$data['birthday'] = $this->getRequest()->getPost('birthday',"") ? $this->getRequest()->getPost('birthday',"") : Utils_Output::jsonResponse(101, '', '生日不能为空');
			$data['marriage'] = $this->getRequest()->getPost('marry', '') ? $this->getRequest()->getPost('marry', '') : Utils_Output::jsonResponse(101, '', '是否已婚不能为空');
			$data['phone'] = $this->getRequest()->getPost('phone', '') ? $this->getRequest()->getPost('phone', '') : Utils_Output::jsonResponse(101, '', '手机号不能为空');
			$data['email'] = $this->getRequest()->getPost('email', '') ? $this->getRequest()->getPost('email', '') : Utils_Output::jsonResponse(101, '', '邮箱不能为空');
			$data['edu'] = $this->getRequest()->getPost('education', '') ? $this->getRequest()->getPost('education', '') : Utils_Output::jsonResponse(101, '', '最高学历不能为空');
			$data['hukou_type'] = $this->getRequest()->getPost('household', '') ? $this->getRequest()->getPost('household', '') : Utils_Output::jsonResponse(101, '', '户口性质不能为空');
			$data['driving_years'] = $this->getRequest()->getPost('driving', '') ? $this->getRequest()->getPost('driving', '') : Utils_Output::jsonResponse(101, '', '驾龄不能为空');
			$data['isstaff'] = $this->getRequest()->getPost('employee', '') ? $this->getRequest()->getPost('employee', '') : Utils_Output::jsonResponse(101, '', '是否为我公司员工不能为空');
			$data['isclient'] = $this->getRequest()->getPost('client', '') ? $this->getRequest()->getPost('client', '') : Utils_Output::jsonResponse(101, '', '是否为我公司老客户不能为空');
			$data['credit'] = $this->getRequest()->getPost('credit', '') ? $this->getRequest()->getPost('credit', '') : Utils_Output::jsonResponse(101, '', '信用记录不能为空');
			$data['sue'] = $this->getRequest()->getPost('litigation', '') ? $this->getRequest()->getPost('litigation', '') : Utils_Output::jsonResponse(101, '', '诉讼记录不能为空');
			$data['crime'] = $this->getRequest()->getPost('sin', '') ? $this->getRequest()->getPost('sin', '') : Utils_Output::jsonResponse(101, '', '犯罪记录不能为空');
			$data['interview'] = $this->getRequest()->getPost('impression', '') ? $this->getRequest()->getPost('impression', '') : Utils_Output::jsonResponse(101, '', '面谈主观印象不能为空');
			$data['work_type'] = $this->getRequest()->getPost('career', '') ? $this->getRequest()->getPost('career', '') : Utils_Output::jsonResponse(101, '', '行业类型不能为空');
			$data['work_years'] = $this->getRequest()->getPost('agelimit', '') ? $this->getRequest()->getPost('agelimit', '') : Utils_Output::jsonResponse(101, '', '现单位工作年限不能为空');
			$data['work_cate'] = $this->getRequest()->getPost('unit', '') ? $this->getRequest()->getPost('unit', '') : Utils_Output::jsonResponse(101, '', '现单位性质不能为空');
			$data['work_level'] = $this->getRequest()->getPost('position', '') ? $this->getRequest()->getPost('position', '') : Utils_Output::jsonResponse(101, '', '现单位岗位级别不能为空');
			$data['income'] = $this->getRequest()->getPost('earning', '') ? $this->getRequest()->getPost('earning', '') : Utils_Output::jsonResponse(101, '', '个人月收入状况不能为空');
			$data['house'] = $this->getRequest()->getPost('house', '') ? $this->getRequest()->getPost('house', '') : Utils_Output::jsonResponse(101, '', '个人住房状况不能为空');
            $data['work_address'] = $this->getRequest()->getPost('company', '');
            $data['census_register'] = $this->getRequest()->getPost('registeraddress', '');
            $data['temp_address'] = $this->getRequest()->getPost('livingaddress', '');
			$data['status'] = 0;
			$data['created'] = time();
			$data['birthday'] = str_replace('/', '-', $data['birthday']);
			$data['launch'] = 1;
	                                
			$id = (int)$this->getRequest()->getPost('id','');
			if(!$id){
				$res = $this->_userModel->addUserInfo($data);
				if($res){
					$redis_res = $this->clearUserRedis($uid);
					$result = $this->_userModel->confirmSave($res);
					if($result['new_id']){
						$score['score'] = $result['score'];
						$score['rank'] = $result['rank'];
						Utils_Output::jsonResponse(0, $score, '信息录入成功');
					}else{
						Utils_Output::jsonResponse(101, '', '评分失败');
					}
				}
				else{
					Utils_Tool::fileLog('客户信息录入失败--'.$data['name'].$data['phone']);
					Utils_Output::jsonResponse(102, '', '信息录入失败');
				}
			}else{
				$res = $this->_userModel->saveUserInfo($id, $data);
				if($res){
					$redis_res = $this->clearUserRedis($uid);
					$result = $this->_userModel->confirmSave($id);
					if($result['new_id']){
						$score['score'] = $result['score'];
						$score['rank'] = $result['rank'];
						Utils_Output::jsonResponse(0, $score, '信息录入成功');
					}else{
						Utils_Output::jsonResponse(101, '', '评分失败');
					}
				}
				else{
					Utils_Tool::fileLog('客户信息录入失败--'.$data['name'].$data['phone']);
					Utils_Output::jsonResponse(102, '', '信息录入失败');
				}
			}
		}else{
			$data['creator'] = (int)$this->_loginUser->getUid();
			$data['type'] = $this->getRequest()->getPost('type','');
			$data['name'] = $this->getRequest()->getPost('username','');
			$data['idcard'] = $this->getRequest()->getPost('idcard','');
			$data['idcard_positive_fileid'] = $this->getRequest()->getPost('idcardfront','');
			$data['idcard_reverse_fileid'] = $this->getRequest()->getPost('idcardback','');
			$data['drivinglicence_fileid'] = $this->getRequest()->getPost('drivinglicence','');
			$data['gender'] = $this->getRequest()->getPost('sexual', 1);
			$data['birthday'] = $this->getRequest()->getPost('birthday',"");
			$data['marriage'] = $this->getRequest()->getPost('marry', '');
			$data['phone'] = $this->getRequest()->getPost('phone', '');
			$data['email'] = $this->getRequest()->getPost('email', '');
			$data['edu'] = $this->getRequest()->getPost('education', '');
			$data['hukou_type'] = $this->getRequest()->getPost('household', '');
			$data['driving_years'] = $this->getRequest()->getPost('driving', '');
			$data['isstaff'] = $this->getRequest()->getPost('employee', '');
			$data['isclient'] = $this->getRequest()->getPost('client', '');
			$data['credit'] = $this->getRequest()->getPost('credit', '');
			$data['sue'] = $this->getRequest()->getPost('litigation', '');
			$data['crime'] = $this->getRequest()->getPost('sin', '');
			$data['interview'] = $this->getRequest()->getPost('impression', '');
			$data['work_type'] = $this->getRequest()->getPost('career', '');
			$data['work_years'] = $this->getRequest()->getPost('agelimit', '');
			$data['work_cate'] = $this->getRequest()->getPost('unit', '');
			$data['work_level'] = $this->getRequest()->getPost('position', '');
			$data['income'] = $this->getRequest()->getPost('earning', '');
			$data['house'] = $this->getRequest()->getPost('house', '');
            $data['work_address'] = $this->getRequest()->getPost('company', '');
            $data['census_register'] = $this->getRequest()->getPost('registeraddress', '');
            $data['temp_address'] = $this->getRequest()->getPost('livingaddress', '');
			$data['status'] = 0;
			$data['created'] = time();
			$data['birthday'] = str_replace('/', '-', $data['birthday']);
			$data['launch'] = 2;
			$data['illegal'] = $this->getRequest()->getPost('illegal', '');//违章
			$data['litigation_car'] = $this->getRequest()->getPost('litigation_car', '');//诉讼
	                                
			$id = (int)$this->getRequest()->getPost('id','');
			if(!$id){
				$res = $this->_userModel->addUserInfo($data);
				if($res){
					$redis_res = $this->clearUserRedis($uid);
					$result = $this->_userModel->confirmSave($res);
					if($result['new_id']){
						$score['score'] = $result['score'];
						$score['rank'] = $result['rank'];
						Utils_Output::jsonResponse(0, $score, '信息录入成功');
					}else{
						Utils_Output::jsonResponse(101, '', '评分失败');
					}
				}
				else{
					Utils_Tool::fileLog('客户信息录入失败--'.$data['name'].$data['phone']);
					Utils_Output::jsonResponse(102, '', '信息录入失败');
				}
			}else{
				$res = $this->_userModel->saveUserInfo($id, $data);
				if($res){
					$redis_res = $this->clearUserRedis($uid);
					$result = $this->_userModel->confirmSave($id);
					if($result['new_id']){
						$score['score'] = $result['score'];
						$score['rank'] = $result['rank'];
						Utils_Output::jsonResponse(0, $score, '信息录入成功');
					}else{
						Utils_Output::jsonResponse(101, '', '评分失败');
					}
				}
				else{
					Utils_Tool::fileLog('客户信息录入失败--'.$data['name'].$data['phone']);
					Utils_Output::jsonResponse(102, '', '信息录入失败');
				}
			}
		}
		
	}

	//业务员-车辆业务-贷前管理-客户信息详情页
	public function salesman_customerinfoAction() {
		$id = (int)$this->getRequest()->get('id','');
		$from = htmlspecialchars($_GET['from']);
		$data = $this->_userModel->getInfo($id);
		$launch = $this->getLaunch();
		$this->tpl->assign('launch', $launch);
		$this->tpl->assign('from', $from);
		$this->tpl->assign('uid', $id);
		$this->tpl->assign('data', $data);
		$this->tpl->display();
	}

	//业务员-车辆业务-贷前管理-车辆信息管理列表
	public function salesman_carlistAction(){

	}

	//业务员-车辆业务-贷前管理-车辆信息管理列表ajax接口
	public function salesman_carlistajaxAction(){
		$tab = (int)$this->getRequest()->getPost("list", "");
		$page = (int)$this->getRequest()->getPost("page",1);
		$uid = $this->_loginUser->getUid();
		$data = $this->_carModel->getListByCreatorAndTab($uid, $tab, $page);

		if(!$data){
			Utils_Output::jsonResponse(101, '', '无更多数据');
		}
		Utils_Output::jsonResponse(0, $data,"获取成功");
	}

	//业务员-车辆业务-重新编辑-上一步
	public function salesman_carinfoupdataAction(){

	}

	//业务员-车辆业务-贷前管理-获取车辆品牌ajax接口
	public function salesman_getcarbrandlistajaxAction(){
		$list = $this->logic->getbrandlist();
		$letters = $this->logic->getletters();
		$hotbrands = $this->logic->gethotbrands();
		$data = array();
		foreach($list as $key=>$value){
			$data['brands'][] = array('letter'=>$key, 'item'=>$value);
		}
		$data['letters'] = $letters;
		$data['hotbrand'] = $hotbrands;
		echo json_encode($data);exit;
	}

	//业务员-车辆业务-贷前管理-获取车辆车系ajax接口
	public function salesman_getcarserieslistajaxAction(){
		$brand_id = (int)$_GET['id'];
		if(!$brand_id) Utils_Output::jsonResponse(101, '','没有此品牌车辆');
		$data = $this->logic->getserieslist($brand_id);
		$data = array('item'=>$data);
		echo json_encode($data);exit;
	}

	//业务员-车辆业务-贷前管理-获取车辆型号ajax接口
	public function salesman_getcarstylelistajaxAction(){
		$series_id = (int)$_GET['id'];
		if(!$series_id) Utils_Output::jsonResponse(101, '','没有此品牌车辆');
		$data = $this->logic->getstylelist($series_id);
		$data = array('spec', $data);
		echo json_encode($data);exit;
	}


	//业务员-车辆业务-贷前管理-车辆信息添加
	public function salesman_caraddAction() {
		$launch = $this->getLaunch();
		$id = (int)$_GET['id'];
		if($id){
			$data = $this->_carModel->getUpdateInfo($id);
			$this->tpl->assign('id', $id);
			$this->tpl->assign('data', $data);
		}else{
			$param_array = $this->car_params;
			$user_id = $this->_loginUser->getUid();
			$redis_info = $this->redis->get("redis_car_info_".$user_id);
	    	$info = json_decode($redis_info);
	    	foreach($info as $k=>$v){
	    		foreach($param_array as $value){
	    			if($k == $value){
	    				$getParam[$k] = $v;
	    			}
	    		}
	    	}
	    	$getParam = $this->idToImg($getParam);
	    	$carBrandModel = new CarBrandModel();
	    	$getParam['carBrand'] = $carBrandModel->getCarInfo($getParam['carNameBid'], $getParam['carNameSid'], $getParam['carNameSpecid']);
	    	$this->tpl->assign('redis_info', $getParam);
		}
		$this->tpl->assign('launch', $launch);
		$this->tpl->display();
	}

	//业务员-车辆业务-贷前管理-车辆信息添加ajax接口
	public function salesman_caraddajaxAction() {
		$launch = $this->getLaunch();
		if($launch == 1){
			$data['creator'] = $this->_loginUser->getUid();
			$data['type'] = $this->getRequest()->getPost('car_status',0) ? $this->getRequest()->getPost('car_status',0) : Utils_Output::jsonResponse(101, '', '车辆状况不能为空');
			$data['brand'] = $this->getRequest()->getPost('carNameBid',0) ? $this->getRequest()->getPost('carNameBid',0) : Utils_Output::jsonResponse(101, '', '车辆品牌不能为空');
			$data['series'] = $this->getRequest()->getPost('carNameSid',0) ? $this->getRequest()->getPost('carNameSid',0) : Utils_Output::jsonResponse(101, '', '车辆车系不能为空');
			$data['item'] = $this->getRequest()->getPost('carNameSpecid',0) ? $this->getRequest()->getPost('carNameSpecid',0) : Utils_Output::jsonResponse(101, '', '车辆型号不能为空');
	        $data['color'] = $this->getRequest()->getPost('car_color',0) ? $this->getRequest()->getPost('car_color',0) : Utils_Output::jsonResponse(101, '', '车辆颜色不能为空');
	        $data['drive_record'] = $this->getRequest()->getPost('car_kilo',0) ? $this->getRequest()->getPost('car_kilo',0) : Utils_Output::jsonResponse(101, '', '行驶里程不能为空');
	        $data['sp_date'] = $this->getRequest()->getPost('check_day',0) ? $this->getRequest()->getPost('check_day',0) : Utils_Output::jsonResponse(101, '', '上牌日期不能为空');
	        $data['sl_date'] = $this->getRequest()->getPost('run_day',0);
	        $data['auto_id'] = $this->getRequest()->getPost('car_numb1',0) ? $this->getRequest()->getPost('car_numb1',0) : Utils_Output::jsonResponse(101, '', '车架号不能为空');
	        $data['frame_id'] = $this->getRequest()->getPost('car_numb2',0) ? $this->getRequest()->getPost('car_numb2',0) : Utils_Output::jsonResponse(101, '', '发动机号不能为空');
	        $data['license'] = $this->getRequest()->getPost('car_page_1',0);
	        $data['registration'] = $this->getRequest()->getPost('car_page_2',0);
	        $data['invoice'] = $this->getRequest()->getPost('car_page_3',0);

	        $mode = $this->getRequest()->getPost('car_gear','');
	        if($mode!=='' && $mode!==false)
	        	$data['mode'] = $mode;
	        else
	        	Utils_Output::jsonResponse(101, '', '车档不能为空');

	        $product_place = $this->getRequest()->getPost('car_orgin',0);
	        if($product_place!=='' && $product_place!==false)
	        	$data['product_place'] = $product_place;
	        else
	        	Utils_Output::jsonResponse(101, '', '车辆产地不能为空');
	        $data['displacement'] = $this->getRequest()->getPost('litre',0) ? $this->getRequest()->getPost('litre',0) : Utils_Output::jsonResponse(101, '', '排量不能为空');
	        $data['source_income'] = $this->getRequest()->getPost('car_source',0) ? $this->getRequest()->getPost('car_source',0) : Utils_Output::jsonResponse(101, '', '车辆来源不能为空');
	        $data['trans_amount'] = $this->getRequest()->getPost('dealvalue',0) ? $this->getRequest()->getPost('dealvalue',0) : Utils_Output::jsonResponse(101, '', '成交价值不能为空');
	        $data['created'] = time();
	        $data['status'] = 2;
	        $data['launch'] = 1;

	        $car_id = (int)$this->getRequest()->getPost('id','');
	        if(!$car_id){
	        	$id = $this->_carModel->addCarInfo($data);
		        if($id){
		        	$data['id'] = $id;
		        	Utils_Output::jsonResponse(0, $id, "添加成功");
		        }else{
		        	Utils_Output::jsonResponse(101, '','信息插入失败');
		        }
	        }else{
	        	$id = $this->_carModel->updatacarinfo($car_id, $data);
	        	if($id !== false){
		        	Utils_Output::jsonResponse(0, $car_id, "修改成功");
		        }else{
		        	Utils_Output::jsonResponse(101, '','信息修改失败');
		        }
	        }
		}else{
			$data['creator'] = $this->_loginUser->getUid();
			$data['type'] = $this->getRequest()->getPost('car_status',0);
			$data['brand'] = $this->getRequest()->getPost('carNameBid',0);
			$data['series'] = $this->getRequest()->getPost('carNameSid',0);
			$data['item'] = $this->getRequest()->getPost('carNameSpecid',0);
	        $data['color'] = $this->getRequest()->getPost('car_color',0);
	        $data['drive_record'] = $this->getRequest()->getPost('car_kilo',0);
	        $data['sp_date'] = $this->getRequest()->getPost('check_day',0);
	        $data['sl_date'] = $this->getRequest()->getPost('run_day',0);
	        $data['auto_id'] = $this->getRequest()->getPost('car_numb1',0);
	        $data['frame_id'] = $this->getRequest()->getPost('car_numb2',0);
	        $data['license'] = $this->getRequest()->getPost('car_page_1',0);
	        $data['registration'] = $this->getRequest()->getPost('car_page_2',0);
	        $data['invoice'] = $this->getRequest()->getPost('car_page_3',0);
	        $data['mode'] = $this->getRequest()->getPost('car_gear','');
	        $data['product_place'] = $this->getRequest()->getPost('car_orgin',0);
	        $data['displacement'] = $this->getRequest()->getPost('litre','');
	        $data['source_income'] = $this->getRequest()->getPost('car_source','');
	        $data['trans_amount'] = $this->getRequest()->getPost('dealvalue',0);
	        $data['created'] = time();
	        $data['status'] = 0;
	        $data['launch'] = 2;

	        $car_id = (int)$this->getRequest()->getPost('id','');
	        if(!$car_id){
	        	$id = $this->_carModel->addCarInfo($data);
		        if($id){
		        	$data['id'] = $id;
		        	Utils_Output::jsonResponse(0, $id, "添加成功");
		        }else{
		        	Utils_Output::jsonResponse(101, '','信息插入失败');
		        }
	        }else{
	        	$id = $this->_carModel->updatacarinfo($car_id, $data);
	        	if($id !== false){
		        	Utils_Output::jsonResponse(0, $car_id, "修改成功");
		        }else{
		        	Utils_Output::jsonResponse(101, '','信息修改失败');
		        }
	        }
		}
		
        
	}

	//业务员-车辆业务-贷前管理-车辆信息添加-添加车辆照片
	public function salesman_carphotoAction() {
		$id = (int)$this->getRequest()->get("submitid",'');
		$data = $this->_carModel->getCarPhotos($id);
		$photo_names = $this->_carModel->getPhotoNames();

		$this->tpl->assign('id',$id);
		$this->tpl->assign('data',$data);
		$this->tpl->assign('photo_names',$photo_names);
	}

	//业务员-车辆业务-贷前管理-车辆信息添加-添加车辆照片ajax接口
	public function salesman_carphotoajaxAction(){
		$uid = $this->_loginUser->getUid();
		$id = (int)$this->getRequest()->getPost('id', '');
		$photo_arr = $this->getRequest()->getPost('image', '');
		$launch = $this->_appModel->getApp($id)['launch'];
		if($photo_arr){
			foreach($photo_arr as $v){
				foreach($v as $key=>$value){
					$photo_str .= $key.':'.$value.',';
				}
			}
			$res = $this->_carModel->addCarPhoto($id, $photo_str);
			if($res){
				$redis_res = $this->clearCarRedis($uid);
				Utils_Output::jsonResponse(0, $id, "添加成功");
			}else{
				if($launch==1){
					Utils_Output::jsonResponse(101, '','图片插入失败');
				}else{
					$redis_res = $this->clearCarRedis($uid);
					Utils_Output::jsonResponse(0, $id, "添加成功");
				}
			}
		}else{
			$redis_res = $this->clearCarRedis($uid);
			Utils_Output::jsonResponse(0, $id, "添加成功");
		}

	}

	//业务员-车辆业务-贷前管理-车辆信息删除-删除车辆信息
	public function salesman_carinfodeleteajaxAction(){
		$id = (int)$this->getRequest()->getPost('id', '');
		$res = $this->_carModel->deleteCarInfo($id);
		if($res)
			Utils_Output::jsonResponse(0, '', "撤销成功");
		else
			Utils_Output::jsonResponse(101, '', "撤销失败");
	}

	//业务员-车辆业务-贷前管理-车辆信息添加-车辆照片展示
	public function salesman_carinfophotoAction() {
		// $id = $this->getRequest()->get('id', '');
		$id = (int)$_GET['id'];
		$data = $this->_carModel->getCarPhotos($id);
		// Utils_Output::jsonResponse(0, $data,"获取成功");
		$this->tpl->assign('id', $id);
		$this->tpl->assign('data', $data);
		$this->tpl->display();
	}


	//业务员-车辆业务-贷前管理-车辆信息确认
	public function salesman_carinfoconfirmAction() {
		// $id = $this->getRequest()->get('id','');
		$id = (int)$_GET['id'];
		$data = $this->_carModel->getInfo($id);
		$launch = $this->getLaunch();
		$this->tpl->assign('id', $id);
		$this->tpl->assign('data', $data);
		$this->tpl->assign('launch', $launch);
		$this->tpl->display();
	}
	//业务员-车辆业务-贷前管理-车辆信息详情
	public function salesman_carinfoAction() {
		$id = (int)$this->getRequest()->get('id','');
		$from = $_GET['from'];
		$data = $this->_carModel->getInfo($id);
		$launch = $this->getLaunch();
		$this->tpl->assign('from', $from);
		$this->tpl->assign('id', $id);
		$this->tpl->assign('data', $data);
		$this->tpl->assign('launch', $launch);
		$this->tpl->display();
	}
        
    /**
    * 业务员-车辆业务-贷前管理-贷款额度确认列表
    * @author hgy
    */
    public function salesman_loanlistAction() {

    }
    /**
    * 业务员-车辆业务-贷前管理-贷款额度确认列表Ajax接口
    * @author hgy
    */
    public function salesman_loanlistAjaxAction() {
    //获取业务员ID
    $creator = $this->_loginUser->getUid();
    //获取页数
    $page = $this->getRequest()->getPost("page",1);
    //获取申请单状态
    $status = $this->getRequest()->getPost("list",1);

    //查询申请单列表
    $data = $this->_appModel->getAppList($page,null,$status,$creator);

    if(!$data){
        Utils_Output::jsonResponse(101, '', '无更多数据');
    }

        Utils_Output::jsonResponse(0, $data);
    }
	
    /**
    * 业务员-车辆业务-贷前管理-贷款额度发起
    * @author hgy
    */
    public function salesman_loanaddAction() {
        $uid = $this->_loginUser->getUid();
        $role_id = ini_get("yaf.environ") == 'test' ? 27 : 29;
        $appraisers = $this->logic->getAppraisers($uid, $role_id);
        if(count($appraisers) == 1){
            $this->tpl->assign("appraiser", $appraisers[0]);
        }else{
            $this->tpl->assign("appraisers", $appraisers);
        }
    }

    /**
    * 业务员-车辆业务-贷前管理-贷款额度-绑定用户与车辆Ajax接口
    * @author hgy
    */
    public function salesman_loanaddAjaxAction() {
    //获取业务员ID
    $sal_id = $this->_loginUser->getUid();
    //获取页数
    $page = $this->getRequest()->getPost("page",1);
    //获取查询类型：1.客户信息 2.车辆信息
    $sel_type = $this->getRequest()->getPost("sel_type",1);
    //获取业务类型: 1.以租代购
    $type = $this->getRequest()->getPost("type",1);

    $launch = $this->getLaunch();
    //判断查询类型
    if($sel_type == 1){
        //查询客户信息
        $data = $this->_userModel->getListByCreator($sal_id,$page,1);
        //计算生日
        if(!empty($data)){
            foreach ($data as $k => $v){
                if(!empty($v['birthday']))
                    $data[$k]['age'] = $this->_userModel->getAge($v['birthday']);
                else
                    $data[$k]['age'] = '';
                $data[$k]['created'] = date('Y.m.d', $v['created']);
                if($launch == 2){
                    $data[$k]['marriage'] = 2;
                }
            }
        }
    }
    else if($sel_type == 2){
        //查询车辆信息
        $data = $this->_carModel->getListByCreator($sal_id,0,0,$page);
    }
    else{
        Utils_Output::jsonResponse(101, '', '查询类型错误');
    }

    if(!$data){
        Utils_Output::jsonResponse(101, '', '无更多数据');
    }

    Utils_Output::jsonResponse(0, $data);

    }
        
    /**
    * 业务员-车辆业务-贷前管理-贷款额度-确认提交Ajax接口
    * @author hgy
    */
    public function salesman_loansubmitAjaxAction() {
        //获取业务员ID
        $data['creator'] = $this->_loginUser->getUid();
        //获取业务从属机构
        $launch = $this->getLaunchByCreator($data['creator']);
        $data['launch'] = $launch;
        //获取客户ID
        $data['customer'] = $this->getRequest()->getPost("customer",'');
        //获取车辆ID
        $data['car'] = $this->getRequest()->getPost("car",'');
        //获取贷款金额
        $data['amount'] = $this->getRequest()->getPost("amount",0);
        //获取利率
        $data['rate'] = $this->getRequest()->getPost("rate",0);
        //获取贷款期限
        $data['deadline'] = $this->getRequest()->getPost("deadline",0);
        //获取评估师id
        $data['appraiser_id'] = $this->getRequest()->getPost("pingushi", 0);
        //获取业务员意见
        $data['creator_comment'] = $this->getRequest()->getPost("opinion", '');
        //判断表单数据
        if(empty($data['amount'])  || !is_numeric($data['amount']) || $data['amount'] > 9999.99){
            Utils_Output::jsonResponse(101,'',"请正确填写贷款金额,贷款金额的填写请在9999.99(万元)以下");
        }
        if(empty($data['rate'])  || !is_numeric($data['rate']) || $data['rate'] > 999.99){
            Utils_Output::jsonResponse(101,'',"请正确填写利率，利率的填写请在999.99以下");
        }
        if(empty($data['customer']) || empty($data['car']) || !is_numeric($data['customer']) || !is_numeric($data['car'])){
            Utils_Output::jsonResponse(101,'',"客户ID或车辆ID错误");
        }
        if(empty($data['deadline'])  || !is_numeric($data['deadline'])){
            Utils_Output::jsonResponse(101,'',"请选择贷款期限");
        }
        if(empty($data['appraiser_id'])  || !is_numeric($data['appraiser_id'])){
        	Utils_Output::jsonResponse(101,'',"请选择评估师");
        }
        if (empty($data['creator_comment'])) {  
            Utils_Output::jsonResponse(101,'',"业务员意见不能为空");
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['amount'])) {  
            Utils_Output::jsonResponse(101,'',"贷款金额小数位请在两位以下"); 
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['rate'])) {  
            Utils_Output::jsonResponse(101,'',"贷款利率小数位请在两位以下"); 
        }

        //查询客户是否存在，如果存在是否已发起
        $user = $this->_userModel->getInfo($data['customer']);
        if(empty($user) || $user['status'] != 1){
            Utils_Output::jsonResponse(101,'',"您选择的客户不存在或已发起");
        }

        //查询车辆是否存在，如果存在是否已发起
        $car = $this->_carModel->getInfo($data['car']);
        if(empty($car) || $car['status'] != 0){
            Utils_Output::jsonResponse(101,'',"您选择的车辆不存在或已发起");
        }

        //查询业务员详情
        $admin_info = $this->_user->get($data['creator']);
        $data['oid'] = $admin_info['oid'];


        //生成申请单
        $res = $this->_appModel->addApp($data);

        if($res){
		    //获取选择评估师详情
		    $appraiser_info = $this->_user->get($data['appraiser_id']);
            //查出业务员直属上级
            $link = new Cd_LinkModel();
            $parent = $link->getUserLinkList($data['creator']);
            // if($parent[0]){
            if($appraiser_info && $parent[0]){
                //实例短信队列
                $resqueQueue = new Resque_Queue();
                //给业务员发送短信通知
                $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $admin_info['phone'], 'type' => 14, 'param' => array('type' => 1,'customer_name' => $user['name'], 'amount' => $data['amount'], 'p_name' => $appraiser_info['realname'], 'p_phone' => $appraiser_info['phone']), 'smstype' => 1));
                //给评估师发送短信通知
                $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $appraiser_info['phone'], 'type' => 14, 'param' => array('type' => 4,'customer_name' => $user['name'], 'amount' => $data['amount'], 'p_name' => $appraiser_info['realname'], 'p_phone' => $appraiser_info['phone'], 'salesman'=>$admin_info['realname']), 'smstype' => 1));
            }
            
            Utils_Output::jsonResponse(0, $res, '申请单生成成功');
        }
        else{
            Utils_Tool::fileLog('申请单生成失败--客户编号:'.$data['customer'].', 车辆编号'.$data['car']);
            Utils_Output::jsonResponse(102, '', '申请单生成失败');
        }

    }

    /**
    * 业务员-车辆业务-贷前管理-贷款额度详情
    * @author hgy
    */
    public function salesman_loaninfoAction() {
        //获取申请单ID
        $id = Utils_FilterXss::filterXss($this->getRequest()->get('id'));

        //查询申请单详情
        $data = $this->_appModel->getAppInfo($id);

        if(empty($data[0]))
            Utils_Output::merrorMsg('申请单详情获取失败');
        else
            $info = $data[0]; 

        //计算生日
        if(!empty($info['birthday']))
            $info['age'] = $this->_userModel->getAge($info['birthday']);
        else
            $info['age'] = '';

        //查询审核记录
        $audits = $this->_auditModel->getUserAudits($id);
    // echo "<pre><meta charset='utf-8'>";var_dump($audits);exit;
        //待审核环节,从当前环节开始显示
        //$wait_nodes = array_slice($this->logic->nodes,$info['flow']-1);

        //如果订单在进行中的话,查出当前用户所有上级,这里从业务员角色开始查
        $wait_nodes = null;
        if($info['status'] == 1){
            $role_id = ini_get("yaf.environ") == 'test' ? 26 : 28;
            $link = new Cd_LinkModel();
            $p_users = $link->getPids($info['creator'],$role_id);
            //$p_users = $link->getPids(77,26);

            //根据flow进程,截取出待审核环节
            if($info['flow'] == 2){
                $wait_nodes = array_splice($p_users, $info['flow']-1);
            }
            else{
                $wait_nodes = array_splice($p_users, $info['flow']-2);
            }

            //如果flow=2,证明订单还未被任何人审核过，因为评估师是独立于
            //上下级关系之外的,所以这里手动添加评估师环节
            if($info['flow'] == 2){
                array_unshift($wait_nodes, array('name'=>'车贷评估师'));
            }

        }

        //客户与车辆信息
        $this->tpl->assign('info', $info);
        //经办人记录
        $this->tpl->assign('audits', $audits);
        //审核环节列表
        $this->tpl->assign('wait_nodes', $wait_nodes);

    }


    /**
    * 评估师列表页
    * @author hgy
    */
    public function appraiser_listAction() {
        $user_role = (int)$this->req->get('user_role', "");
        if(!$user_role){
                header('Location: /chedai/index');
                return false;
        }
        $this->_checkLogin($user_role);
    }
    /**
    * 评估师列表页Ajax接口
    * @author hgy
    */
    public function appraiser_listAjaxAction() {
        $uid = $this->_loginUser->getUid();
        //获取页数
        $page = $this->getRequest()->getPost("page",1);
        //获取申请单状态
        $status = $this->getRequest()->getPost("status",0);
        //获取所处流程
        $flow = $this->getRequest()->getPost("list",0);
        //获取业务查询类型
        $type = $this->getRequest()->getPost("type",1);

        //查询评估列表
        $data = $this->_appModel->getUnAssessList($flow,$status,$uid,$page);

        if(!$data){
            Utils_Output::jsonResponse(101, '', '无更多数据');
        }

        //拼接业务详情
        foreach ($data as $k => $v){
            $data[$k]['business'] = '贷款金额: '.$v['amount'].'万元, 利率: '.$v['rate'] . '%， 期限: '.$v['deadline'] . '个月';
        }

        //Utils_Tool::fileLog(var_export($data,1));
        Utils_Output::jsonResponse(0, $data,"获取成功");

    }

    /**
    * 评估师评估报告
    * @author hgy
    */
    public function appraiser_reportAction() {
        //获取申请单ID
        $id = Utils_FilterXss::filterXss($this->getRequest()->get('id',''));

        $this->tpl->assign('id', $id);
    }
    /**
    * 评估师评估报告Ajax接口
    * @author hgy
    */
    public function appraiser_reportAjaxAction() {
        //获取申请单ID
        $app_id = $this->getRequest()->getPost("id",'');
        //获取业务员ID
        $creator = $this->_loginUser->getUid();
        //手续费
        $data['estimate_sx'] = $this->getRequest()->getPost("procedures",0);
        //配置情况
        $data['estimate_pz'] = $this->getRequest()->getPost("config",'');
        //静态检测
        $data['estimate_jt'] = $this->getRequest()->getPost("static",'');
        //动态检测
        $data['estimate_dt'] = $this->getRequest()->getPost("dynamic",'');
        //综合评定
        $data['estimate_zh'] = $this->getRequest()->getPost("synthesis",'');
        //车辆价值
        $data['estimate_total'] = $this->getRequest()->getPost("assessable",'');

        //判断表单数据
        // if(empty($data['estimate_sx'])){
        //     Utils_Output::jsonResponse(101,'',"请填写手续费、规费情况");
        // }
        // if(empty($data['estimate_pz'])){
        //     Utils_Output::jsonResponse(101,'',"请填写配置情况");
        // }
        // if(empty($data['estimate_jt'])){
        //     Utils_Output::jsonResponse(101,"请填写静态检测");
        // }
        // if(empty($data['estimate_dt'])){
        //     Utils_Output::jsonResponse(101,'',"请填写动态检测");
        // }
        if(empty($data['estimate_zh'])){
            Utils_Output::jsonResponse(101,'',"请填写综合评定");
        }
        if(empty($app_id)  || !is_numeric($app_id)){
            Utils_Output::jsonResponse(101,'',"申请单ID错误或不存在");
        }
        if(empty($data['estimate_total'])  || !is_numeric($data['estimate_total'])){
            Utils_Output::jsonResponse(101,'',"请填写车辆价值");
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $data['estimate_total'])) {  
            Utils_Output::jsonResponse(101,'',"车辆价值小数位请在两位以下"); 
        }


        //查询待评估订单是否存在
        $app = $this->_appModel->getApp($app_id);
        if(empty($app)){
            Utils_Output::jsonResponse(101,'',"待评估的申请单不存在");
        }

        //判断是否已评估过
        if($app['flow'] > 2){
            Utils_Output::jsonResponse(101,'',"申请单已评估过");
        }

         //查询角色
    //             $roles = (new RoleModel())->getUserRoles($creator);
    //             foreach($roles as $r){
    //                 $user_role = $r['id'];
    //             }

    //查询角色	
        $user_role = $this->redis->get("user_role_".$creator);
        //实例车贷model
        $cd_model = new ChedaiModel;

        //如果角色不存在或不是评估师，提示错误
        if(empty($user_role)){
            Uitls_Output::jsonResponse(101,'',"您还没有选择任何角色,请先选择角色");
        }
        else if(ini_get("yaf.environ") == 'test'){
            if($cd_model->nodes[$this->logic->role_nodes_test[$user_role]] != '评估师'){
                Utils_Output::jsonResponse(101,'',"您的角色不是评估师,请选择评估师角色后再进行此操作");
            }
            $flow = $this->logic->role_nodes_test[$user_role];
        }
        else if(ini_get("yaf.environ") != 'test'){
            if($cd_model->nodes[$this->logic->role_nodes_develop[$user_role]] != '评估师'){
                Utils_Output::jsonResponse(101,'',"您的角色不是评估师,请选择评估师角色后再进行此操作");
            } 
            $flow = $this->logic->role_nodes_develop[$user_role];
        }

        if(empty($flow)){
            Utils_Output::jsonResponse(101,'',"审核流程错误");
        }

        //进行评估
        $data['id'] = $app['car'];
        $res = $this->_carModel->upAssessmentReport($app_id,$data,$creator,$flow);

        if($res){
                $sms_info = $this->logic->getSMSInfo($app_id);
                $user = $this->_user->getUserInfoByUid($creator);//当前操作者信息
                //实例短信队列
            $resqueQueue = new Resque_Queue();
            //给业务员发送短信通知
            $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $sms_info['phone'], 'type' => 14, 'param' => array('type' => 5,'customer_name' => $sms_info['customer_name'], 'amount' => $sms_info['amount'], 'user_name'=>$user['realname'], 'p_name' => $sms_info['leader_info']['real_name'], 'p_phone' => $sms_info['leader_info']['phone']), 'smstype' => 1));
            //给业务经理发送短信通知
            $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $sms_info['leader_info']['phone'], 'type' => 14, 'param' => array('type' => 6,'customer_name' => $sms_info['customer_name'], 'amount' => $sms_info['amount'], 'user_name'=>$user['realname'], 'p_name' => $sms_info['leader_info']['real_name'], 'p_phone' => $sms_info['leader_info']['phone']), 'smstype' => 1));
            //如果评估成功添加审核记录
            $audit_mod =  new Cd_AuditInfoModel();
            $audit_mod->addInfo($app_id,$creator,$flow,1,$data['estimate_zh']);

            $audit_user_mod = new Cd_UserAuditInfoModel();
            $audit_user_mod->addInfo($app_id, $creator, $flow);
            Utils_Output::jsonResponse(0, $res, '评估成功');
        }
        else{
                Utils_Tool::fileLog('评估失败--申请单号: '.$app_id);
                Utils_Output::jsonResponse(102, '', '评估失败');
            }

    }

    /**
    * 评估师待评估订单和已评估订单
    * @author hgy
    */
    public function appraiser_infoAction() {
        //获取申请单ID
        $id = Utils_FilterXss::filterXss($this->getRequest()->get('id',''));

        //查询此订单是否已评估过
        $app = $this->_appModel->getApp($id);
        if(empty($app)){
            Utils_Output::merrorMsg('订单获取失败');
        }

        $launch = $this->getLaunchByCreator($app['creator']);

        //查询申请单评估详情
        $assess_info = $this->_appModel->AssessInfo($id,$app['flow']);

        if(empty($assess_info)){
            Utils_Output::merrorMsg('订单详情获取失败');
        }
        if($launch==2){
            $assess_info['displacement'] = '';
        }

        $this->tpl->assign('launch', $launch);
        //渲染评估详情
        $this->tpl->assign('assess_info', $assess_info);
        //审核环节列表
        $this->tpl->assign('nodes', $this->logic->nodes);

    }

    //经理首页
    public function manager_listAction() {
            $user_role = (int)$this->req->get('user_role', "");
            if(!$user_role){
                    header('Location: /chedai/index');
                    return false;
            }
            $this->_checkLogin($user_role);
    }

    //经理首页list ajax接口
    public function manager_listajax_bakAction() {
            $tab = (int)$this->getRequest()->getPost('list', '');
            $page = $this->getRequest()->getPost('page', '');
            $type = $this->getRequest()->getPost('type', '');
            $user_id = $this->_loginUser->getUid();

            $user_role = $this->redis->get("user_role_".$user_id);

    $node = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$user_role] : $this->logic->role_nodes_develop[$user_role];
    $link = new Cd_LinkModel();
    $childs = $link->getChilds($user_id, $user_role);
    $role = ini_get('yaf.environ')=='test' ? 26 : 28;
    foreach($childs as $v){
            if($v['role_id'] == $role){
                    $ids[] = $v['user_id'];
            }
    }
    if(!$ids){
            Utils_Output::jsonResponse(101, '', '该角色没有管辖业务员');
    }
    $data = $this->_appModel->getManagerAppListByTab($tab, $node, $page, $type, $ids);

    if(!$data){
            Utils_Output::jsonResponse(101, '', '无更多数据');
    }else{
            foreach($data as $key=>$value){
                    $data[$key]['detail'] = '贷款金额:'.$data[$key]['amount'].',利率:'.$data[$key]['rate'].',期限:'.$data[$key]['deadline'];
            }
            // Utils_Tool::fileLog(var_export($data,1));
                    Utils_Output::jsonResponse(0, $data,"获取成功");
    }
    }

    //经理首页list ajax接口
    public function manager_listajaxAction() {
            $tab = (int)$this->getRequest()->getPost('list', '');
            $page = $this->getRequest()->getPost('page', '');
            $type = $this->getRequest()->getPost('type', '');
            $user_id = $this->_loginUser->getUid();

            $user_role = $this->redis->get("user_role_".$user_id);

    $node = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$user_role] : $this->logic->role_nodes_develop[$user_role];
    $link = new Cd_LinkModel();
    $childs = $link->getChilds($user_id, $user_role);
    $role = ini_get('yaf.environ')=='test' ? 26 : 28;
    $ids = array();
    foreach($childs as $v){
            if($v['role_id'] == $role){
                    $ids[] = $v['user_id'];
            }
    }
    if(!$ids){
            Utils_Output::jsonResponse(101, '', '该角色没有管辖业务员');
    }
    if(!$ids){
            Utils_Output::jsonResponse(101, '', '该角色没有管辖业务员');
    }
    $data = $this->logic->getManagerAppListByTab($tab, $node, $page, $type, $ids, $user_id);

    if(!$data){
            Utils_Output::jsonResponse(101, '', '无更多数据');
    }else{
            foreach($data as $key=>$value){
                    $data[$key]['detail'] = '贷款金额:'.$data[$key]['amount'].'万元, 利率:'.$data[$key]['rate'].'%, 期限:'.$data[$key]['deadline'] . '个月';
                    $data[$key]['list'] = $tab;
            }
                    Utils_Output::jsonResponse(0, $data,"获取成功");
    }
    }

    //经理待审核详情页
    public function manager_infoAction() {
            $id = (int)$_GET['id'];
            $list = (int)$_GET['list'];
            $user_id = $this->_loginUser->getUid();
            $data = $this->logic->getmanagerappinfo($id, $user_id);
            $user_role = $this->redis->get("user_role_".$user_id);
    $flow = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$user_role] : $this->logic->role_nodes_develop[$user_role];
    //查询审核记录
    $audits = $this->_auditModel->getUserAudits($id);
    $launch = $this->getLaunch();
    $this->tpl->assign('flow', $flow);
            $this->tpl->assign('id', $id);
            $this->tpl->assign('data', $data);
            $this->tpl->assign('audits', $audits);
            $this->tpl->assign('list', $list);
            $this->tpl->assign('launch', $launch);
            $this->tpl->display();
    }

    //经理详情页合法信息提交
    public function manager_infoajaxAction(){
        $user_id = $this->_loginUser->getUid();
        $user_role = $this->redis->get("user_role_".$user_id);
        $nodes = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$user_role] : $this->logic->role_nodes_develop[$user_role];
        $res = true;
        if($nodes==4){
                $data['app_id'] = (int)$this->getRequest()->getPost('id', '');
                $data['creator'] = $user_id;
                $data['court_fileid'] = $this->getRequest()->getPost('courtimg', '');//法院网查询照片
                $data['court_result'] = (int)$this->getRequest()->getPost('court', '');//是否正常
                $data['court_result_reason'] = htmlspecialchars($this->getRequest()->getPost('courtreason', ''));//不正常原因
                $valueimg = $this->getRequest()->getPost('valueimg1', '');
                // $valueimg[] = (int)$this->getRequest()->getPost('valueimg2', '');
                // $valueimg[] = (int)$this->getRequest()->getPost('valueimg3', '');
                $data['assess_cost_fileids'] = $valueimg;//评估价值信息id集
                // $data['assess_cost_fileids'] = implode(',', $valueimg);//评估价值信息id集
                $data['credit_fileid'] = $this->getRequest()->getPost('creditimg', '');//征信图片
                $data['credit_result'] = (int)$this->getRequest()->getPost('credit', '');//征信结果
                $data['credit_result_reason'] = htmlspecialchars($this->getRequest()->getPost('creditreason', ''));//征信结果原因
                $data['bank_information_fileid'] = $this->getRequest()->getPost('bankimg', '');//银行流水信息结果id
                $data['month_income'] = (float)$this->getRequest()->getPost('monthin', '');//月入
                $data['month_outflow'] = (float)$this->getRequest()->getPost('monthout', '');//月出
                $data['phone_verify'] = htmlspecialchars($this->getRequest()->getPost('confirmreason', ''));//电话核实联系人
                $data['phone_verify_result'] = (int)$this->getRequest()->getPost('confirm', '');//电话核实联系人结果
                $data['illegal'] = $this->getRequest()->getPost('illegalimg', '');//违章查询
                $data['created'] = time();
                $res = $this->_LegalAuditModel->addInfo($data);
        }
        if($nodes==6){
                $data['app_id'] = (int)$this->getRequest()->getPost('id', '');
                $data['creator'] = $user_id;
                $data['court_result'] = (int)$this->getRequest()->getPost('result', '');//是否正常
                $data['created'] = time();
                $res = $this->_LegalAuditModel->addInfo($data);
        }
        if($res){
                Utils_Output::jsonResponse(0, $res, '添加成功');
        }else{
                Utils_Output::jsonResponse(101, false, '合法信息添加失败');
        }
    }

	//经理同意原因页
	public function manager_agreeAction() {
		$app_id = (int)$_GET['id'];
		$audit_id = (int)$_GET['auditid'];
		$this->tpl->assign("id", $app_id);
		$this->tpl->assign('auditid', $audit_id);
		$this->tpl->display();
	}

	//经理同意操作ajax
	public function manager_agreeajaxAction(){
		$uid = $this->_loginUser->getUid();
		$user_role = $this->redis->get("user_role_".$uid);//获取当前角色id
		$app_id = (int)$this->getRequest()->getPost("id", '');
		$reason = htmlspecialchars($this->getRequest()->getPost('reason',''));
		$more_data['agree_deadline'] = (int)$this->getRequest()->getPost("deadline", '');
		$more_data['agree_amount'] = (float)$this->getRequest()->getPost("amount", '');
		$more_data['agree_rate'] = (float)$this->getRequest()->getPost("rate", '');
		$more_data['legal_id'] = (int)$this->getRequest()->getPost("auditid", '');
        $result = $this->logic->appAgree($app_id, $uid, $user_role, $reason, $more_data);
        
        if($result){
        	$link = new Cd_LinkModel();
        	$user_info = $link->getLeaderInfo($uid, $user_role);//上级领导信息
        	$app_info = $this->_appModel->getCustomerInfo($app_id);//客户姓名信息
        	$user = $this->_user->getUserInfoByUid($uid);//当前操作者信息
        	$flow = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$user_role] : $this->logic->role_nodes_develop[$user_role];
        	$role = $this->logic->nodes[$flow];
        	$p_flow = $flow + 1;
        	$p_role = $this->logic->nodes[$p_flow];//上级角色名称
        	$resqueQueue = new Resque_Queue();
			if($flow < 7){
				//经理级审批通过，给业务员发送短信
				$token = $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $app_info['phone']/*业务员*/, 'type' => 14, 'param' => array('type' => 7,'customer_name' => $app_info['name'], 'amount' => $app_info['amount'], 'p_name' => $p_role.$user_info['leader_name'], 'p_phone'=>$user_info['leader_phone'], 'user_name'=>$role.$user['realname']), 'smstype' => 1));
				//经理级审批通过，给上级发送短信
				$token = $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $user_info['leader_phone']/*上级*/, 'type' => 14, 'param' => array('type' => 8,'customer_name' => $app_info['name'], 'amount' => $app_info['amount'], 'p_name' => $p_role.$user_info['leader_name'], 'user_name'=>$role.$user['realname']), 'smstype' => 1));
			}else{
				//经理级审批通过，给业务员发送短信,模板跟发给上级的一样  所以 type=>8
				$token = $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $app_info['phone']/*业务员*/, 'type' => 14, 'param' => array('type' => 8,'customer_name' => $app_info['name'], 'amount' => $app_info['amount'], 'p_name' => $p_role.$user_info['leader_name'], 'p_phone'=>$user_info['leader_phone'], 'user_name'=>$role.$user['realname']), 'smstype' => 1));
			}
        	Utils_Output::jsonResponse(0, '',"操作成功");
        }else{
        	Utils_Output::jsonResponse(101, '',"操作失败");
        }
	}

	//经理驳回原因页
	public function manager_rejectAction() {
		$user_id = $this->_loginUser->getUid();
		$app_id = (int)$_GET['id'];
		$reason = htmlspecialchars($this->getRequest()->getPost('reason',''));
		$user_role = $this->redis->get("user_role_".$user_id);
        $role_url = ini_get("yaf.environ") == 'test' ? $this->role_url_test : $this->role_url_develop;
        $role_arr = array_keys($role_url);
        $role_six = $role_arr[count($role_arr)-2];
        $role_seven = $role_arr[count($role_arr)-1];
        $this->tpl->assign("role_six", $role_six);
        $this->tpl->assign("role_seven", $role_seven);
		$this->tpl->assign("user_role", $user_role);
		$this->tpl->assign("id", $app_id);
		$this->tpl->display();
	}

	//经理驳回操作ajax
	public function manager_rejectajaxAction(){
		$user_id = $this->_loginUser->getUid();
		$user_role = $this->redis->get("user_role_".$user_id);

		$app_id = (int)$this->getRequest()->getPost('id', '');
		$reason = htmlspecialchars($this->getRequest()->getPost('reason',''));
		$reject = (int)$this->getRequest()->getPost('reject','');
		$res = $this->logic->managerReject($app_id, $user_id, $user_role, $reason, $reject);

		$reject_user = $this->_auditModel->getUser($app_id, $reject);
		$reject_info = $this->_user->getUserInfoByUid($reject_user['user_id']);
		if($res){
        	$app_info = $this->_appModel->getCustomerInfo($app_id);
        	$user = $this->_user->getUserInfoByUid($user_id);//当前操作者信息
        	$flow = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$user_role] : $this->logic->role_nodes_develop[$user_role];
        	$role = $this->logic->nodes[$flow];//当前角色名称
        	$reject_role = $this->logic->nodes[$reject];//上级角色名称

			$resqueQueue = new Resque_Queue();
			if($reject==1 || !$reject){ //驳回到业务员，给业务员发送短信
				// $token = $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $app_info['phone']/*业务员*/, 'type' => 14, 'param' => array('type' => 9,'customer_name' => $app_info['name'], 'amount' => $app_info['amount'], 'user_name' => $role.$user['realname']), 'smstype' => 1));
				$token = $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $app_info['phone'], 'type' => 14, 'param' => array('type' => 10,'customer_name' => $app_info['name'], 'amount' => $app_info['amount'], 'user_name' => $role.$user['realname'], 'reject_name'=>$reject_role.$reject_info['realname']), 'smstype' => 1));
			}else{
				$token = $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $reject_info['phone'], 'type' => 14, 'param' => array('type' => 10,'customer_name' => $app_info['name'], 'amount' => $app_info['amount'], 'user_name' => $role.$user['realname'], 'reject_name'=>$reject_role.$reject_info['realname']), 'smstype' => 1));
				$token = $resqueQueue->enqueue('xmcd_sms', 'Sms_PHP_Job', array('name' => 'sms', 'phone' => $app_info['phone'], 'type' => 14, 'param' => array('type' => 11,'customer_name' => $app_info['name'], 'amount' => $app_info['amount'], 'user_name' => $role.$user['realname'], 'reject_name'=>$reject_role.$reject_info['realname']), 'smstype' => 1));
			}
			Utils_Output::jsonResponse(0, '',"操作成功");
		}else{
			Utils_Output::jsonResponse(101, '',"操作失败");
		}
	}

	//上传文件方法
	public function uploadFileAjaxAction() {
        $ele = array_keys($_FILES)[0];
        if (!$ele) {
            echo json_encode(array('error'=>'1', 'errmsg'=>'文件不存在'));
            return FALSE;
        }
        if ($_FILES[$ele]) {
            $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
            $dir = 'fk/report/'.date('Ymd');
            $upload = new Utils_Upload($dir, $config['root']);
            $upload->file($_FILES[$ele]);
            //仅允许上传gif,jpg,png三种类型图片
            $upload->set_allowed_mime_types(array('image/gif', 'image/jpeg', 'image/png'));
            $results = $upload->upload();
            if (!$results['status']) {
                $errmsg = isset($results['errors'])?implode(',', $results['errors']):'';
                echo json_encode(array('error'=>'2', 'errmsg'=>'文件上传失败,'.$errmsg));
            } else {
                $path = str_replace('\\', '/', $results['path']);
                $filemodel = new FilesModel();
                $id = $filemodel->addfile($path);
                if (!$id) {
                    echo json_encode(array('error'=>'3', 'errmsg'=>'文件上传失败,'));
                } else {
                    $url = $config['host'].$path;
                    echo json_encode(array('error'=>'0', 'data'=>array('path'=>$path, 'url'=>$url, 'id'=>$id)));
                }
            }
        }
        return FALSE;
    }

	/**
	 * 额度确认列表详细信息ajax接口
	 * @param int $appId
	 */
	public function amountConfirmAjaxAction(){
		$appId = $this->req->getPost('app_id');
		if(!$appId) return false;
		$list = $this->logic->getAppInfo($appId);
		if(empty($list)) return false;

		echo json_encode($list);
		exit;
	}

	//提示页
    private function _errorSkip($param,$forWardParam){
        $this->getView()->assign("forWardParam",$forWardParam);
        $this->getView()->assign("param",$param);
        $this->getView()->display("chedai/result.phtml");exit;
    }

    //公共结果页
    public function resultAction(){

    }

    //分享着陆页页面
    public function landingAction(){
    	$uid = (int)$_GET['id'];
        $user_mod = new AdminUserModel();
        $user = $user_mod->get($uid);
       
        //查询角色信息
        $role_mod = new RoleModel();
        $role = $role_mod->getUserRoles($uid);

        $role_id = $this->redis->get("user_role_".$uid);
        // $role_id = $_COOKIE['user_role_'.$uid];
        foreach($role as $key=>$value){
        	if($value['id'] == $role_id)
        		$role_key = $key;
        }
       	$this->tpl->assign('id', $uid);
        $this->tpl->assign('user', $user);
        $this->tpl->assign('role', $role[$role_key]);
    }

    /**
     * redis限制
     * @param $key : redis key
     * @param $times ： 次数
     * @param $time : key有效时间
     * @return bool
     */
    private function _redisVerify($key,$times,$time = 86400){
        $redis = new Utils_Redis();
        $redis->select(11);//选择db 10
        $value = (int)$redis->get($key);
        if($value && $value < $times){//防刷次数
            $redis->increment($key);
        }elseif(!$value){
            $redis->set($key,1,$time);//设置TimeOut
        }elseif($value >= $times){
            return false;
        }
        return true;
    }

    /**
	 * 判断登陆
	 * @param int $user_role
     */
    public function _checkLogin($user_role){
		if($this->_loginUser->isLogin()){
			$uid = $this->_loginUser->getUid();
			$this->redis->set('user_role_'.$uid, $user_role, 3600*24*30);
        	// setcookie('user_role_'.$uid,$user_role,time()+3600*24,'/', $this->_host);
        	return true;
        }else{
        	$this->_loginUser->setLogout();
        	header("Location: /chedai/login");
        	return false;
        }
    }

    /**
	 * 修改密码页面
     */
    public function modifyUserPwdAction(){
        $userId = $this->_loginUser->getUid();
        //获取用户信息
        $userInfo = $this->_user->get($userId);
        if(!empty($userInfo)){
            $phone = Utils_Helper::hidPhone($userInfo['phone']);
            $realName = $userInfo['realname'];
        }else{
            $phone = "获取失败";
            $realName = "获取失败";
        }
        $this->_tpl->assign('id',$userId);
        $this->_tpl->assign('phone',$phone);
        $this->_tpl->assign('realname',$realName);
    }

    //更改已存车贷 车辆数据表图片字段格式 *勿用*
    public function changeCarPhotosAction(){
    	// $res = $this->_carModel->changeCarPhotos();
    	echo "<pre><meta charset='utf-8'>";var_dump($res);exit;
    }
    
    //用户设置
    public function settingAction(){
    	
    }
    
    //修改密码
    public function changepasswordAction(){
    	$userId = $this->_loginUser->getUid();
        //获取用户信息
        $userInfo = $this->_user->get($userId);
        if(!empty($userInfo)){
            $phone = Utils_Helper::hidPhone($userInfo['phone']);
            $realName = $userInfo['realname'];
        }else{
            $phone = "获取失败";
            $realName = "获取失败";
        }
        $this->tpl->assign('id',$userId);
        $this->tpl->assign('phone',$phone);
        $this->tpl->assign('realname',$realName);
    }

    /**
	 * 修改密码ajax接口
     */
    public function changepasswordAjaxAction(){
        $userId = $this->_loginUser->getUid();
        $newpwd = htmlspecialchars($this->getRequest()->getPost('pwd', ''));
        $oldpwd = htmlspecialchars($this->getRequest()->getPost('oldpwd', ''));
        if (strlen($newpwd) < 6) {
            Utils_Output::jsonResponse('新密码长度不符合要求');exit;
        }
        $info = $this->_user->getUserInfoByUid($userId);
        if ((md5(md5($oldpwd).$info['salt']) == $info['passwd'])){
        	//修改密码
        	$info = $this->_user->changePwd($userId,$newpwd);
	        if($info){
	            Utils_Output::jsonResponse(0,'','密码修改成功');exit;
	        }else{
				Utils_Output::jsonResponse(101,'','密码修改失败或未做改动');exit;
	        }
	    }else{
	    	Utils_Output::jsonResponse(101,'','原密码错误');exit;
	    }
    }


    /**
	 * 业务员表单信息实时保存ajax接口
     */
    public function realTimeStorageAjaxAction(){
    	$param_array_user = $this->user_params;
    	$param_array_car = $this->car_params;
    	foreach($_POST as $k=>$v){
    		foreach($param_array_user as $value){
    			if($k == $value){
    				$getParam_user[$k] = $v;
    			}
    		}
    	}
    	foreach($_POST as $k=>$v){
    		foreach($param_array_car as $value){
    			if($k == $value){
    				$getParam_car[$k] = $v;
    			}
    		}
    	}
    	if(!$getParam_user && !$getParam_car){
    		Utils_Output::jsonResponse(101,'','未传递参数');exit;
    	}
    	$uid = $this->_loginUser->getUid();
    	if($getParam_user){
    		$this->redis->set('redis_user_info_'.$uid, json_encode($getParam_user), 3600*24*30);
    	}
    	if($getParam_car){
    		$this->redis->set('redis_car_info_'.$uid, json_encode($getParam_car), 3600*24*30);
    	}
    	if($this->redis->get('redis_user_info_'.$uid) || $this->redis->get('redis_car_info_'.$uid)){
    		Utils_Output::jsonResponse(0,'','存入成功');exit;
    	}else{
    		Utils_Output::jsonResponse(101,'','存入失败');exit;
    	}
    }

    /**
	 * 业务员新加客户信息时判断
	 */
    public function salesman_customeradd_checkExistsNotSaveAjaxAction(){
    	$param_array = $this->user_params;
    	$uid = $this->_loginUser->getUid();
    	$role_id = $this->redis->get("user_role_".$uid);
    	$flow = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$role_id] : $this->logic->role_nodes_develop[$role_id];
    	//判断当前是否为业务员角色
    	if($flow != 1){
    		Utils_Output::jsonResponse(101,'','当前角色不是业务员，请重新登陆');exit;
    	}
    	$redis_info = $this->redis->get("redis_user_info_".$uid);
    	if(!$redis_info || $redis_info==NULL){
    		Utils_Output::jsonResponse(100,'','没有未完成信息');exit;
    	}
    	$info = json_decode($redis_info);
    	foreach($info as $k=>$v){
    		foreach($param_array as $value){
    			if($k == $value){
    				$getParam[$k] = $v;
    			}
    		}
    	}
    	if(count($getParam) == 0){
    		Utils_Output::jsonResponse(100,'','没有未完成信息');exit;
    	}
    	Utils_Output::jsonResponse(0,$info,'获取信息成功');exit;
    }

    /**
	 * 业务员新加车辆信息时判断
	 */
    public function salesman_caradd_checkExistsNotSaveAjaxAction(){
    	$param_array = $this->car_params;
    	$uid = $this->_loginUser->getUid();
    	$role_id = $this->redis->get("user_role_".$uid);
    	$flow = ini_get("yaf.environ") == 'test' ? $this->logic->role_nodes_test[$role_id] : $this->logic->role_nodes_develop[$role_id];
    	//判断当前是否为业务员角色
    	if($flow != 1){
    		Utils_Output::jsonResponse(101,'','当前角色不是业务员，请重新登陆');exit;
    	}

    	$redis_info = $this->redis->get("redis_car_info_".$uid);
    	if(!$redis_info || $redis_info==NULL){
    		Utils_Output::jsonResponse(100,'','没有未完成信息');exit;
    	}
    	$info = json_decode($redis_info);
    	foreach($info as $k=>$v){
    		foreach($param_array as $value){
    			if($k == $value){
    				$getParam[$k] = $v;
    			}
    		}
    	}
    	if(count($getParam) == 0){
    		Utils_Output::jsonResponse(100,'','没有未完成信息');exit;
    	}
    	Utils_Output::jsonResponse(0,$info,'获取成功');exit;
    }

    public function clearUserRedis($uid){
    	$redis_info = $this->redis->get("redis_user_info_".$uid);
		if($redis_info){
			$this->redis->delete("redis_user_info_".$uid);
			return true;
		}
		return false;
    }

    public function clearCarRedis($uid){
    	$redis_info = $this->redis->get("redis_car_info_".$uid);
		if($redis_info){
			$this->redis->delete("redis_car_info_".$uid);
			return true;
		}
		return false;
    }

    public function clearRedisAction(){
    	$uid = $this->_loginUser->getUid();
    	$redis_user_info = $this->redis->get("redis_user_info_".$uid);
    	$redis_car_info = $this->redis->get("redis_car_info_".$uid);
		if(count($redis_user_info)>0 || count($redis_car_info)>0){
			$this->redis->delete("redis_user_info_".$uid);
			$this->redis->delete("redis_car_info_".$uid);
			echo "<pre><meta charset='utf-8'>";echo '成功';exit;
		}
		echo "<pre><meta charset='utf-8'>";echo '失败';exit;
    }

    public function lookredisAction(){
    	$uid = $this->_loginUser->getUid();
    	$redis_user_info = $this->redis->get("redis_user_info_".$uid);
    	$redis_car_info = $this->redis->get("redis_car_info_".$uid);
    	echo "<pre><meta charset='utf-8'>";var_dump(json_decode($redis_user_info),json_decode($redis_car_info));exit;
    }

    /**
	 * 将数组中id转为对应img及图片尺寸
	 * @param 带有图片id的数组
     */
    public function idToImg($param){
    	$fileModel = new FilesModel();
    	$param_array = array('idcardfront','idcardback','drivinglicence','car-page-1','car-page-2','car-page-3');
    	foreach($param as $k=>$v){
    		foreach($param_array as $value){
    			if($k == $value){
    				$fileInfo = $fileModel->getFilePath($v);
    				$param[$k.'_src'] = $fileInfo['url'];
    			}
    		}
    	}
    	return $param;
    }

    //清楚缓存ajax地址
    public function clearUserRedisAjaxAction(){
    	$uid = $this->_loginUser->getUid();
    	$redis_info = $this->redis->get("redis_user_info_".$uid);
		if($redis_info){
			$this->redis->delete("redis_user_info_".$uid);
			Utils_Output::jsonResponse(0,'','删除成功');exit;
		}
		Utils_Output::jsonResponse(101,'','删除失败');exit;
    }

    //获取业务发起的机构所属  return 1为厦门机构 2为全国机构
    public function getLaunch(){
    	$uid = $this->_loginUser->getUid();
    	$oid = $this->_user->get($uid)['oid'];
		$org_xm = ini_get("yaf.environ") == 'test' ? array(38, 39, 41) : array(38, 39, 41);
		if(in_array($oid, $org_xm)){
			return 1;
		}else{
			return 2;
		}
    }

    public function getLaunchByCreator($uid){
    	$oid = $this->_user->get($uid)['oid'];
		$org_xm = ini_get("yaf.environ") == 'test' ? array(38, 39, 41) : array(38, 39, 41);
		if(in_array($oid, $org_xm)){
			return 1;
		}else{
			return 2;
		}
    }

}
