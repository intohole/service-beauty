<?php

class AreaController extends Yaf_Controller_Abstract {

    private $_tpl;
    private $_areaModel;
    private $_areaflowModel;
    private $_operationModel;
    private $_loanarea;

    public function init() {
        $this->_tpl = $this->getView();
        //$this->_areaModel = new AreaModel();
        $this->_areaflowModel = new AreaFlowModel();
        $this->_operationModel = new OperationModel();
        $this->_loanarea = new LoanareaModel();
    }

    public function indexAction() {
//        $org = $this->_areaModel->getAreaItem();
//        $this->_tpl->assign('org',$org);
    }
    public function getSearchorgAction(){
        $word = $this->getRequest()->get('key');
        $searchorg = $this->_areaModel->getSearchorg($word);
        Utils_Output::ajaxJsonReturn(array('error'=>0, 'data'=>$searchorg));
        return FALSE;
    }

    public function getAreaListAjaxAction() {
        $where = array();
        if($_POST['org_sx'] !=''){
            $where['insti_attr'] = $_POST['org_sx'];
        }
        if($_POST['org_name'] != ''){
            $where['name'] = $_POST['org_name'];
        }
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" => '',
            "data" => array()
        );
        $areaCount = $this->_areaModel->getAreaCount($where);
        if ($areaCount > 0) {
            $areaList = $this->_areaModel->getAreaList($where,$_POST['start'], $_POST['length']);
        }else{
            $areaList='';
        }
        $output['recordsFiltered'] = $areaCount;
        $output['data'] = $areaList;
        echo json_encode($output);
        exit;
    }

    public function addAreaAction() {
        
    }

    public function addAreaAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        $addDatas = $this->getRequest()->getPost();
        $addDatas = Utils_FilterXss::filterArray($addDatas);
		
        if (!$addDatas['area_name']) {
            $data['errorMsg'] = '地区名称为空';
            echo json_encode($data);
            exit;
        }
        
        //检查name是否有重复
        $info = $this->_areaflowModel->infoExist($addDatas['area_name']);
        if (!empty($info)) {
            $data['errorMsg'] = '地区名称重复';
            echo json_encode($data);
            exit;
        }
		$addDatas['created'] = time();
        $result = $this->_areaflowModel->add($addDatas);
        if ($result) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "地区对接人员配置列表";
            $logInfo['option'] = "添加地区";
            $addDatas['id'] = $result;
            $logInfo['new_data'] = Utils_Helper::arrayToString($addDatas);
            $this->_operationModel->add($logInfo);
            
            //如果添加成功,在地区权限表中也插入对应数据
            /* $ofModel = new AreaFlowModel();
            $org_flow_datas['oid'] = $result;
            $ofModel->addData($org_flow_datas); */

            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            echo json_encode($data);
            exit;
        } else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);
            exit;
        }
    }

    public function editAreaAction() {
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if ($id <= 0) {
            die('地区ID获取失败');
        }

        $info = $this->_areaflowModel->get($id);
        if (!$info) {
            die('地区信息获取失败');
        }

        $this->_tpl->assign('info', $info);
    }

    public function editAreaAjaxAction() {
        $id = (int) $this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if ($id <= 0 || !$data['area_name']) {
            Utils_Output::errorResponse('参数缺失');
            exit;
        }
		
		//检查name是否有重复
        $info = $this->_areaflowModel->infoExist($data['area_name']);
        if (!empty($info)) {
            Utils_Output::errorResponse('地区名称重复');
            exit;
        }
		
        //获取旧数据
        $oldData = $this->_areaflowModel->get($id);
        if ($this->_areaflowModel->mod($id, $data)) {
            //记录日志
            $newData = $this->_areaflowModel->get($id);
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "地区对接人员配置列表";
            $logInfo['option'] = "修改地区";
            $logInfo['old_data'] = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = Utils_Helper::arrayToString($newData);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('OK', 0);
            exit;
        } else {
            Utils_Output::errorResponse('未做修改或其它错误');
            exit;
        }

        return FALSE;
    }

    public function deleteAreaAjaxAction() {
        $id = (int) $this->getRequest()->getPost('id');
        if ($id <= 0) {
            Utils_Output::errorResponse('没有权限');
            exit;
        }
        $oldData = $this->_areaflowModel->get($id);
        if ($this->_areaflowModel->del($id)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "地区对接人员配置列表";
            $logInfo['option'] = "删除地区";
            $logInfo['old_data'] = Utils_Helper::arrayToString($oldData);
            $this->_operationModel->add($logInfo);
            
            //如果删除成功同时删除地区权限列表中记录
            /* $ofModel = new AreaFlowModel();
            $ofModel->del($id); */
            
            Utils_Output::errorResponse('OK', 0);
            exit;
        } else {
            Utils_Output::errorResponse('删除失败');
            exit;
        }
        return FALSE;
    }
    
    
    /**
    * 获取地区对接人员配置列表
    * @author hgy
    * @since 2016-05-05
    */
    public function flowlistAction() {
      
    }
    
    /**
    * 获取地区对接人员配置列表Ajax
    * @author hgy
    * @since 2016-05-05
    */
    public function getflowlistAjaxAction() {

        $where = array();
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" => '',
            "data" => array()
        );
        
		//获取所有节点
		$flowNames = array('node_loan_assign','node_loan_allassign','node_loan_recheck','node_loan_financial','node_loan_parent_first','node_loan_parent_review');
        //print_r($flowNames);exit;
        //查出总记录数
        $orgFlowCount = $this->_areaflowModel->getOrgFlowCount();
		
        if ($orgFlowCount > 0) {
            
            //查出所有用户姓名
			$admin_user = new AdminUserModel();
            $all_orgs = $admin_user->getUserList();
			//print_r($all_orgs);exit;
            $orgNames = array();
            //$orgAttrs = array();
            foreach($all_orgs as $k => $v){
                $orgNames[$v['id']] = $v['realname'];
                //$orgAttrs[$v['id']] = $v['insti_attr'];
            }
            //查询地区权限列表
            $orgFlowList = $this->_areaflowModel->getOrgFlowList($_POST['start'], $_POST['length']);
//            var_dump($orgFlowList);exit;
        }
		
        //print_r($orgNames);exit;
        if($orgFlowList){
            foreach($orgFlowList as $key => $val){
                //遍历审核节点
                foreach($flowNames as $f_key => $f_val){
                    //判断是否是审核节点字段
                    if(!empty($val[$f_val])){
                        //如果是取出该节点下配置的地区的ID,可能有多个，由字符串转为数组
                        $orgs = explode(',',$val[$f_val]); 
                        $org_name = array();
                        
                        //查出地区ID对应的名称
                        foreach($orgs as $o_key => $o_val){
                            $org_name[] = $orgNames[$o_val];
                        }

                        $orgFlowList[$key][$f_val.'_name'] = implode(',',$org_name);

                    }
                }
                /* foreach($orgAttrs as $kk=>$v){
                    if($kk == $val['id']){
                        $orgFlowList[$key]['insti_attr'] = $v;
                    }
                } */
            }
        }
        //print_r($orgFlowList);exit;
        $output['recordsFiltered'] = $orgFlowCount;
        $output['data'] = $orgFlowList;
        echo json_encode($output);
        exit;
    }
    
    /**
    * 编辑地区对接人员配置列表
    * @author hgy
    * @since 2016-05-05
    */
    public function editAreaFlowAction() {
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if ($id <= 0) {
            die('地区ID获取失败');
        }
        
        //查出地区对接人员配置详情
        $of_info = $this->_areaflowModel->getOrgFlowInfo($id);
        if (!$of_info) {
            die('地区信息获取失败');
        }
        
        //查询出所有节点
        $flows = array
		(
			array('ename'=>'node_loan_assign','name'=>'放款审批风控负责人'),
			array('ename'=>'node_loan_allassign','name'=>'放款审批总公司初审'),
			array('ename'=>'node_loan_recheck','name'=>'放款审批总公司复审'),
			array('ename'=>'node_loan_financial','name'=>'放款审批总公司财务放款'),
			array('ename'=>'node_loan_parent_first','name'=>'放款资料总公司初审'),
			array('ename'=>'node_loan_parent_review','name'=>'放款资料总公司复审')
		);

        //查出所有用户姓名
		$admin_user = new AdminUserModel();
		$all_orgs = $admin_user->getUserList();
		$orgNames = array();
		foreach($all_orgs as $k => $v){
			$orgNames[$v['id']] = $v['realname'];
		}
        
		$roleModel = new RoleModel();
		if(ini_get('yaf.environ')=='test' || ini_get('yaf.environ')=='develop'){
			//本地或测试环境
			$role_id_o   = '54';	//放款审批风控负责人角色id
			$role_id_t   = '55';	//放款审批总公司初审角色id
			$role_id_th  = '56';	//放款审批总公司复审角色id
			$role_id_the = '82';	//放款审批总公司财务放款角色id
			$role_id_f   = '57';	//放款资料总公司初审角色id
			$role_id_fi  = '58';	//放款资料总公司复审角色id
		}else if(ini_get('yaf.environ')=='product' || ini_get('yaf.environ')=='pre'|| ini_get('yaf.environ')=='yad'){
			$role_id_o   = '50';	//放款审批风控负责人角色id
			$role_id_t   = '51';	//放款审批总公司初审角色id
			$role_id_th  = '52';	//放款审批总公司复审角色id
			$role_id_the = '76';	//放款审批总公司财务放款角色id
			$role_id_f   = '53';	//放款资料总公司初审角色id
			$role_id_fi  = '54';	//放款资料总公司复审角色id
		}
		
		$allUsers['assigNames'] = $roleModel->getRoleUsers($role_id_o);			//放款审批风控负责人
		$allUsers['allAssigNames'] = $roleModel->getRoleUsers($role_id_t);		//放款审批总公司初审
		$allUsers['recheckNames'] = $roleModel->getRoleUsers($role_id_th);		//放款审批总公司复审
		$allUsers['financialNames'] = $roleModel->getRoleUsers($role_id_the);		//放款审批总公司财务放款
		$allUsers['parFirstNames'] = $roleModel->getRoleUsers($role_id_f);		//放款资料总公司初审
		$allUsers['parReviewNames'] = $roleModel->getRoleUsers($role_id_fi);	//放款资料总公司复审
		//print_r($assigNames);exit;
        //遍历审核节点
        foreach($flows as $f_key => $f_val){
            //判断是否是审核节点字段
            if(!empty($of_info[$f_val['ename']])){
                //如果是取出该节点下配置的地区的ID，可能由多个，由字符串转为数组
                $org_vals = explode(',',$of_info[$f_val['ename']]); 
                $of_info[$f_val['ename']] = $org_vals;
            }
        }

        $this->_tpl->assign('info', $of_info);
        $this->_tpl->assign('flows', $flows);
        $this->_tpl->assign('orgNames', $orgNames);
        $this->_tpl->assign('allUsers', $allUsers);
    }

    /**
    * 获取地区权限列表Ajax
    * @author hgy
    * @since 2016-05-05
    */
    public function editAreaFlowAjaxAction() {
        $id = (int) $this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        //print_r($data);exit;
        //检验表单元素
        if (empty($id) || $id <= 0 ) {
            Utils_Output::errorResponse('参数错误');
            exit;
        }
        
        //获取配置权限表中的字段,即审核节点
        $ofModel = new AreaFlowModel();
        $fields = $ofModel->getFields();
        foreach($fields as $k => $v){
            if($v == 'created' || $v == 'id' || $v == 'area_name' ){
                unset($fields[$k]);
            }
        }
        
        //根据数据表中字段,配置审核节点,为空的代表用户未勾选的
        foreach($fields as $key => $val){
            if($data[$val]){
                $data[$val] = implode(',',$data[$val]);
            }
            else{
                $data[$val] = '';
            }
        }
        
        $res = $ofModel->mod($id, $data);
        if($res){
            Utils_Output::errorResponse('OK', 0);
            exit;
        }
        else{
            Utils_Output::errorResponse('未做修改或其它错误');
            exit; 
        }
        
        return FALSE;       
    }
    public function loanarealistAction(){

    }

    /**
     * 获取地区对接人员配置列表Ajax
     * @author cxw
     * @since 2016-05-05
     */
    public function getloanarealistAjaxAction() {

        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" => '',
            "data" => array()
        );

        //获取所有节点
        $flowNames = array('node_loan');
        //查出总记录数
        $loanFlowCount = $this->_loanarea->getOrgFlowCount();

        if ($loanFlowCount > 0) {
            //查出所有用户姓名
            $admin_user = new AdminUserModel();
            $all_orgs = $admin_user->getUserList();
//            print_r($all_orgs);exit;
            $orgNames = array();
            //$orgAttrs = array();
            foreach($all_orgs as $k => $v){
                $orgNames[$v['id']] = $v['realname'];
                //$orgAttrs[$v['id']] = $v['insti_attr'];
            }
            //查询地区权限列表
            $orgFlowList = $this->_loanarea->getOrgFlowList($_POST['start'], $_POST['length']);
//            var_dump($orgFlowList);exit;
        }

        if($orgFlowList){
            foreach($orgFlowList as $key => $val){
                //遍历审核节点
                foreach($flowNames as $f_key => $f_val){
                    //判断是否是审核节点字段
                    if(!empty($val[$f_val])){
                        //如果是取出该节点下配置的地区的ID,可能有多个，由字符串转为数组
                        $orgs = explode(',',$val[$f_val]);
                        $org_name = array();

                        //查出地区ID对应的名称
                        foreach($orgs as $o_key => $o_val){
                            $org_name[] = $orgNames[$o_val];
                        }

                        $orgFlowList[$key][$f_val.'_name'] = implode(',',$org_name);

                    }
                }
                /* foreach($orgAttrs as $kk=>$v){
                    if($kk == $val['id']){
                        $orgFlowList[$key]['insti_attr'] = $v;
                    }
                } */
            }
        }
        //print_r($orgFlowList);exit;
        $output['recordsFiltered'] = $loanFlowCount;
        $output['data'] = $orgFlowList;
        echo json_encode($output);
        exit;
    }
    /**
     * 编辑地区对接人员配置列表
     * @author hgy
     * @since 2016-05-05
     */
    public function editloanareaflowAction() {
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if ($id <= 0) {
            die('地区ID获取失败');
        }

        //查出地区对接人员配置详情
        $of_info = $this->_loanarea->getOrgFlowInfo($id);
        if (!$of_info) {
            die('地区信息获取失败');
        }

        //查询出所有节点
        $flows = array
        (
            array('ename'=>'node_loan','name'=>'放款报备对接人员')
        );

        //查出所有用户姓名
        $admin_user = new AdminUserModel();
        $all_orgs = $admin_user->getUserList();
        $orgNames = array();
        foreach($all_orgs as $k => $v){
            $orgNames[$v['id']] = $v['realname'];
        }

        $roleModel = new RoleModel();
        if(ini_get('yaf.environ')=='test' || ini_get('yaf.environ')=='develop'){
            //本地或测试环境
            $role_id_o  = '73';	//放款报备对接人
        }else if(ini_get('yaf.environ')=='product' || ini_get('yaf.environ')=='pre'|| ini_get('yaf.environ')=='yad'){
            $role_id_o  = '67';	//放款报备对接人
        }

        $allUsers['assigNames'] = $roleModel->getRoleUsers($role_id_o);			//放款审批风控负责人
        //print_r($assigNames);exit;
        //遍历审核节点
        foreach($flows as $f_key => $f_val){
            //判断是否是审核节点字段
            if(!empty($of_info[$f_val['ename']])){
                //如果是取出该节点下配置的地区的ID，可能由多个，由字符串转为数组
                $org_vals = explode(',',$of_info[$f_val['ename']]);
                $of_info[$f_val['ename']] = $org_vals;
            }
        }

        $this->_tpl->assign('info', $of_info);
        $this->_tpl->assign('flows', $flows);
        $this->_tpl->assign('orgNames', $orgNames);
        $this->_tpl->assign('allUsers', $allUsers);
    }
    /**
     * 获取地区权限列表Ajax
     * @author hgy
     * @since 2016-05-05
     */
    public function editloanareaflowajaxAction() {
        $id = (int) $this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
//        print_r($data);exit;
        //检验表单元素
        if (empty($id) || $id <= 0 ) {
            Utils_Output::errorResponse('参数错误');
            exit;
        }

        //获取配置权限表中的字段,即审核节点
        $ofModel = new LoanareaModel();
        $fields = $ofModel->getFields();
        foreach($fields as $k => $v){
            if($v == 'created' || $v == 'id' || $v == 'area_name' ){
                unset($fields[$k]);
            }
        }

        //根据数据表中字段,配置审核节点,为空的代表用户未勾选的
        foreach($fields as $key => $val){
            if($data[$val]){
                $data[$val] = implode(',',$data[$val]);
            }
            else{
                $data[$val] = '';
            }
        }

        $res = $ofModel->mod($id, $data);
        if($res){
            Utils_Output::errorResponse('OK', 0);
            exit;
        }
        else{
            Utils_Output::errorResponse('未做修改或其它错误');
            exit;
        }

        return FALSE;
    }
    
    
    
}
