<?php
class ChedaiModel {
	private $app;	//申请单model
	private $auditInfo;	//审核记录model
	private $car;	//车辆信息model
	private $user;	//用户信息model
	private $userAuditInfo;	//后台用户审核记录model
	private $nodePriv;
	private $carBrand;
	
	public $nodes = array(
		1=>'业务员',
		2=>'评估师',
		3=>'业务经理',
		4=>'分公司风控经理',
		5=>'分公司总经理',
		6=>'总部风控总监',
		7=>'总经理',
	);
	
	private $flow_setting = array(
		1=>2,
		2=>3,
		3=>4,
		4=>5,
		5=>6,
		6=>7,
	);

	public $role_nodes_test = array(
		26=>1,
		27=>2,
		28=>3,
		29=>4,
		30=>5,
		31=>6,
		32=>7,
	);

	public $role_nodes_develop = array(
		28=>1,
		29=>2,
		30=>3,
		31=>4,
		32=>5,
		33=>6,
		34=>7,
	);

	public $type = array(
		1=>'以租代购',
		2=>'车辆质押',
		3=>'车辆抵押',
	);
	
	public $error;
	public $errmsg;
	
	public function __construct() {
		$this->app = new Cd_AppModel();
		$this->auditInfo = new Cd_AuditInfoModel();
		$this->car = new Cd_CarModel();
		$this->user = new Cd_UserModel();
		$this->userAuditInfo = new Cd_UserAuditInfoModel();
		$this->nodePriv = new Cd_NodePrivModel();
		$this->carBrand = new CarBrandModel();
	}
	
	/**
	 * 分类型获取业务员创建的客户数量
	 * @param int $creator
	 * @param int $type
	 * @return number
	 */
	public function getUserCnt($creator, $type) {
		$status = '';
		switch ($type) {
			case 1:	//待发起
				$status = [0, 1];	//包含未提交完成的记录
				break;
			case 2: //发起中
				$status = 2;
				break;
			case 3: //已完成
				$status = 3;
				break;
			case 4:
				$status = 1;
				break;
			default:
				return 0;
		}
		
		return $this->user->getCntByCreator($creator, $status);
	}
	
	/**
	 * 分类型获取业务员创建的客户列表
	 * @param int $creator 业务员id
	 * @param int $type 类型 1 待发起 2 发起中 3 已完成 4 待发起（不包含未提交用户）
	 * @param int $page 页码
	 * @param number $pagesize
	 * @return array
	 */
	public function getUserList($creator, $type, $page, $pagesize=10) {
		$status = '';
		switch ($type) {
			case 1:	//待发起
				$status = [0, 1];	//包含未提交完成的记录
				break;
			case 2: //发起中
				$status = 2;
				break;
			case 3: //已完成
				$status = 3;
				break;
			case 4:
				$status = 1;
				break;
			default:
				return [];
		}
		$users = $this->user->getListByCreator($creator, $Page, $status, $pagesize, 'id,name,idcard,gender,marriage,phone,email');
		foreach ($users as $k=>$user) {
			$users[$k]['marriage_display'] = $this->user->fields['marriage'][$user['marriage']];
			$users[$k]['created_display'] = date('Y-m-d H:i:s', $user['created']);
		}
		return $users;
	}
	
	public function setApp($creator, $user, $car, $amount, $rate) {
		if (!$this->user->setApp($creator, $user)) {
			$this->errmsg = '客户状态异常，不能发起';
			return FALSE;
		}
		
		//@todo 检查车辆是否能发起，如果不能发起 则恢复客户为待发起状态
		
		//添加申请
		return $this->app->addApp(array(
			'creator'=>$creator,
			'customer'=>$user,
			'car'=>$car,
			'amount'=>$amount,
			'rate'=>$rate
		));
	}
	
	/**
	 * 获取业务员的申请列表数量
	 * @param int $creator
	 * @param int $status
	 * @return number
	 */
	public function getCreatorAppCnt($creator, $status) {
		return $this->app->getAppCntByCreator($creator, $status);
	}
	
	/**
	 * 获取业务员的申请列表
	 * @param int $creator
	 * @param int $status
	 * @param int $page
	 * @param int $pagesize
	 * @return array
	 */
	public function getCreatorAppList($creator, $status, $page, $pagesize) {
		$apps = $this->app->getAppListByCreator($creator, $page, $status, NULL, $pagesize);
		foreach ($apps as $k=>$app) {
			$apps[$k]['customer_info'] = $this->user->getInfo($app['customer'], 'id,name');
		}
		
		return $apps;
	}
	
	public function audit($uid, $appid, $result, $comment, $nextflow=0) {
		$app = $this->app->getApp($appid);
		
		//@todo 判断用户是否有审核当前流程的权限 没有权限则返回失败
		
		$this->auditInfo->addInfo($appid, $uid, $app['flow'], $result, $comment);	//记录申请单审核记录
		$this->userAuditInfo->addInfo($appid, $uid, $app['flow']);	//记录用户审核记录 用于做用户审核列表
		
		if ($result == 1) { //通过
			//获取当前流程的下一个流程
			$nextflow = $this->flow_setting[$app['flow']];
			if (!$nextflow) {
				//如果没有下一个流程 一般即总经理之后 是结束还是暂时@todo
			}
		} else { //驳回
			//总部风控总监、总经理可以选择驳回到哪个环节，其余驳回均变为终止
			if (in_array($flow, [6, 7])) {
				
			} else {
				$flow = 0;
			}
		}
	}
	
	/**
	 * 获取角色的节点权限
	 * @param array $roles
	 * @return array
	 */
	public function getNodePrivSetting($roles) {
		$ret = [];
		$privs = $this->nodePriv->getNodePrivSetting($roles);
		foreach ($privs as $role_id=>$priv) {
			foreach ($priv as $kk=>$p) {
				$ret[$role_id][] = $this->nodes[$p['nid']];
			}
		}
		return $ret;
	}
	
	/**
	 * 获取指定角色的节点权限
	 * @param int $role_id
	 * @return array
	 */
	public function getRoleNodes($role_id) {
		return $this->nodePriv->getRoleNodes($role_id);
	}
	
	/**
	 * 设置角色的节点权限
	 * @param int $role_id
	 * @param array $nodes
	 * @return boolean
	 */
	public function setRoleNodes($role_id, $nodes) {
		return $this->nodePriv->setRoleNodes($role_id, $nodes);
	}
	
	/**
	 * 判断用户是否有指定节点权限
	 * @param int $user_id
	 * @param int $node
	 * @return boolean
	 */
	public function checkUserNodePriv($user_id, $node) {
		$roleModel = new RoleModel();
		$userRoles = $roleModel->getUserRoles($user_id);
		//没有任何角色 直接返回无权限
		if (!$userRoles) {
			return FALSE;
		}
		
		$roles = [];
		foreach ($userRoles as $r) {
			$roles[] = $r['id'];
		}
		
		return $this->nodePriv->checkRoleNodePriv($roles, $nid);
	}

	/**
	 * 获取业务员的申请单列表详细信息
	 * @param $creator
	 * @param string $flow
	 * @param string $status
	 */
	public function getAppInfo($app_id) {
		return $this->app->getAppInfo($app_id);
	}

	/**
	 * 获取评估师列表详细信息
	 * @param $creator
	 * @param string $flow
	 * @param string $status
	 */
	public function getAssessInfo($app_id) {
		return $this->app->getAssessInfo($app_id);
	}

	/**
	 * 评估师添加评估报告
	 * @param int $app_id
	 * @param array $updataData
	 */
	public function upAssessmentReport($app_id, $updataData){
		$ids = $this->app->getCustomerCar($app_id);
		$car_id = $ids['car'];
		return $this->car->upAssessmentReport($car_id, $updataData);
	}

	/**
	 * 获取待评估列表
	 */
	public function AssessInfo($app_id){
		return $this->app->UnAssessInfo($app_id);
	}

	//获取车辆品牌列表
	public function getbrandlist(){
		$list = $this->carBrand->getBrandGroupByFirst();
		return $list;
	}

	//获取车辆车系列表
	public function getserieslist($brand_id){
		$list = $this->carBrand->getSeriesListByBrand($brand_id);
		return $list;
	}

	//获取车辆款式列表
	public function getstylelist($series_id){
		$list = $this->carBrand->getStyleListBySeries($series_id);
		return $list;
	}

	//获取首字母及显示
	public function getletters(){
		$list = $this->carBrand->getletters();
		return $list;
	}

	//获取热门车品牌
	public function gethotbrands(){
		$list = $this->carBrand->gethotbrands();
		return $list;
	}

	//获取经理待审核详情
	public function getmanagerappinfo($app_id, $user_id){
		$data = $this->app->getInfo($app_id);
		$nodes = $this->nodes;
		$arr = array();
		$data['age'] = $this->user->getAge($data['birthday']);
		$data['result'] = mb_substr($data['result'], 0, 17, 'utf-8');
		$audit_list = $this->auditInfo->getAppInfo($app_id);
		foreach($audit_list as $v){
			$arr = array('name'=>$nodes[$v['flow']], 'comment'=>$v['comment']);
			$data['audit'][] = $arr;
		}
		$data['legal_info'] = $this->auditInfo->getAppLegalInfo($app_id, $user_id);
		return $data;
	}

	/**
	 * 总监与总监理驳回
	 * @param int $app_id
	 * @param text $reason 驳回原因
	 * @param int $reject  驳回至
	 */
	public function managerReject($app_id, $user_id, $user_role, $reason, $reject){
		// $role_nodes = $this->role_nodes;
		$role_nodes = ini_get("yaf.environ") == 'test' ? $this->role_nodes_test : $this->role_nodes_develop;
		$flow = $role_nodes[$user_role];
		$customer = $this->app->getCustomerCar($app_id);
		$user_res = true;
		$this->app->startTrans();
		if($reject){
			if($reject == 1){//驳回到业务员
				$app_res = $this->app->changeStatus($app_id);
				// $user_res = $this->user->setOver($customer['creator'], $customer['customer']);
				$user_res = $this->user->setBack($customer['creator'], $customer['customer']);
				$car_res = $this->car->setBack($customer['creator'], $customer['car']);
			}else{
				$app_res = $this->app->changeFlow($app_id, $reject);
			}
		}else{
			$app_res = $this->app->changeStatus($app_id);
			// $user_res = $this->user->setOver($customer['creator'], $customer['customer']);
			$user_res = $this->user->setBack($customer['creator'], $customer['customer']);
			$car_res = $this->car->setBack($customer['creator'], $customer['car']);
		}
		$result = 0;
		$audit_res = $this->auditInfo->addInfo($app_id, $user_id, $flow, $result, $reason);
		$user_audit_res = $this->userAuditInfo->addInfo($app_id, $user_id, $flow);

		if($app_res!==false && $audit_res && $user_audit_res!==false && $user_res!==false){
			$this->app->commit();
			return true;
		}else{
			$this->app->rollback();
			return false;
		}
	}

	//经理级同意操作
	public function appAgree($app_id, $uid, $user_role, $reason, $more_data){
		$role_nodes = ini_get("yaf.environ") == 'test' ? $this->role_nodes_test : $this->role_nodes_develop;
		$flow = $role_nodes[$user_role];
		$app_flow = $this->app->getApp($app_id)['flow'];
		$customer = $this->app->getCustomerCar($app_id);
		$this->app->startTrans();
        // $app_res = $this->app->appAgree($app_id, $uid, $flow);
        $app_res = $this->app->appAgree($app_id, $uid, $app_flow);

        $result = 1;
        // $audit_res = $this->auditInfo->addInfo($app_id, $uid, $flow, $result, $reason, $more_data);
        $audit_res = $this->auditInfo->addInfo($app_id, $uid, $app_flow, $result, $reason, $more_data);
		// $user_audit_res = $this->userAuditInfo->addInfo($app_id, $uid, $flow);
		$user_audit_res = $this->userAuditInfo->addInfo($app_id, $uid, $app_flow);
		$user_res = true;
		// if($flow == 7){
		if($app_flow == 7){
			$user_res = $this->user->setOk($customer['creator'], $customer['customer']);
		}

		if($app_res!==false && $audit_res && $user_audit_res!==false && $user_res!==false){
			$this->app->commit();
			return true;
		}else{
			$this->app->rollback();
			return false;
		}

	}

	/**
	 * 获取经理级列表
	 * @param $tab
	 * @param $flow 所处节点
	 * @param $page
	 * @param $type
	 * @param $ids  子级ids
	 * @return $array
	 */
	public function getManagerAppListByTab($tab=0, $flow=NULL, $page=1, $type, $ids, $user_id, $pagesize=20){
        $fields = "xmcd_cd_app.*, u.realname as name, cu.type, cu.name as customer_name, o.name as area, from_unixtime(xmcd_cd_app.created) as createdtime, cc.estimate_zh as result ";
                
		if($tab == 1){
			$this->userAuditInfo->where(array('xmcd_cd_user_audit_info.flow'=>$flow, 'xmcd_cd_user_audit_info.user_id'=>$user_id));

			$this->userAuditInfo->where(array('cu.type'=>$type));
			$this->userAuditInfo->where(array("xmcd_cd_app.creator"=>array('in',$ids)));

			$this->userAuditInfo->join("LEFT JOIN xmcd_cd_app on xmcd_cd_user_audit_info.app_id=xmcd_cd_app.id")
								->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
	        	   				->join("LEFT JOIN xmcd_org o on u.oid=o.id")
	        	   				->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id")
	        	   				->join("LEFT JOIN xmcd_cd_car cc on xmcd_cd_app.car=cc.id")
	        	   				->field($fields);
	        $result = $this->userAuditInfo->order('id desc')->page($page, $pagesize)->select();
	        foreach($result as $key=>$value){
	        	$result[$key]['area'] = (mb_substr($value['area'], 0, 5, 'utf-8')).'...';
	        	$result[$key]['result'] = (mb_substr($value['result'], 0, 5, 'utf-8')).'...';
	        	$result[$key]['type'] = $this->type[$value['type']];
	        }

		}else{
			$this->app->where(array('flow'=>$flow, 'xmcd_cd_app.status'=>1));
		
			$this->app->where(array('cu.type'=>$type));
			$this->app->where(array("xmcd_cd_app.creator"=>array('in',$ids)));
	                
	        $this->app->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
	        	   ->join("LEFT JOIN xmcd_org o on u.oid=o.id")
	        	   ->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id")
	        	   ->join("LEFT JOIN xmcd_cd_car cc on xmcd_cd_app.car=cc.id")
	        	   ->field($fields);
	        // $this->app->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
	        // 	   ->field($fields);
	        $result = $this->app->order('id desc')->page($page, $pagesize)->select();
	        foreach($result as $key=>$value){
	        	$result[$key]['area'] = (mb_substr($value['area'], 0, 5, 'utf-8')).'...';
	        	$result[$key]['result'] = (mb_substr($value['result'], 0, 5, 'utf-8')).'...';
	        	$result[$key]['type'] = $this->type[$value['type']];
	        }
		}
		return $result;
	}

	/**
	 * 获取当前用户所属机构的评估师
	 * @param $uid 当前用户id
	 * @param $role_id 评估师角色id
	 * @author jiwei
	 */
	public function getAppraisers($uid, $role_id){
		$_user = new AdminUserModel();
		$oid = $_user->get($uid);
		$oid = $oid['oid'];//获取当前用户所属机构
		$_user = new Admin_UserModel();
		$where['oid'] = $oid;
		$where['role_id'] = $role_id;
		$appraisers = $_user
					  ->join("LEFT JOIN xmcd_user_role fur on xmcd_users.id=fur.user_id")
					  ->field("xmcd_users.id, xmcd_users.realname")
					  ->where($where)
					  ->select();
		return $appraisers;
	}

	//获取评估师完成发送短信详细信息
    public function getSMSInfo($app_id){
        $role = ini_get('yaf.environ')=='test' ? 26 : 28;
        
    	$saleman_info = $this->app->getSMSInfo($app_id);
    	$linkModel = new Cd_LinkModel();
    	$leader_info = $linkModel->getUserLinkList($saleman_info['creator'],$role);
    	$saleman_info['leader_info'] = $leader_info[0];
    	return $saleman_info;
    }
    
    
    /**
    * 查询还款日期所需字段
    * @auth hgy
    * @param int $appid
    * @return array
    */
    public function getPayDate($appid) {
        $select = $this->app;
            
        $fields = "xmcd_cd_app.id as app_id, xmcd_cd_app.no, u.name, u.idcard, "
                . "o.cardno, o.loan, o.rate, o.deadline, o.signcreated, o.company ";

        $select->where(array('xmcd_cd_app.id'=>$appid));

        $select->join("LEFT JOIN xmcd_cd_user u on xmcd_cd_app.customer=u.id");
        $select->join("LEFT JOIN xmcd_cd_contract_order o on xmcd_cd_app.id=o.aid");

        $select->field($fields);

        return $select->find();
        
    }
    

}