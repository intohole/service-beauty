<?php

class OrganizeController extends Yaf_Controller_Abstract {

    private $_tpl;
    private $_organizeModel;
    private $_operationModel;

    public function init() {
        $this->_tpl = $this->getView();
        $this->_organizeModel = new OrganizeModel();
        $this->_operationModel = new OperationModel();
    }

    public function indexAction() {
//        $org = $this->_organizeModel->getOrganizeItem();
//        $this->_tpl->assign('org',$org);
        //查询所有地区
        $afm = new AreaFlowModel();
        $areas = $afm->getAreaItem();
        $this->_tpl->assign('areas',$areas);
    }
    public function getSearchorgAction(){
        $word = $this->getRequest()->get('key');
        $searchorg = $this->_organizeModel->getSearchorg($word);
        Utils_Output::ajaxJsonReturn(array('error'=>0, 'data'=>$searchorg));
        return FALSE;
    }

    public function getOrganizeListAjaxAction() {
        $where = array();
        if($_POST['org_sx'] !=''){
            $where['insti_attr'] = $_POST['org_sx'];
        }
        if($_POST['org_name'] != ''){
            $where['name'] = $_POST['org_name'];
        }
        if($_POST['area_ser'] != ''){
            $where['area_id'] = (int)$_POST['area_ser'];
        }
        if($_POST['clerk_ser'] != ''){
            $where['own_clerk'] = array("like",'%'.htmlspecialchars($_POST['clerk_ser']).'%');;
        }
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" => '',
            "data" => array()
        );
        $organizeCount = $this->_organizeModel->getOrganizeCount($where);
        if ($organizeCount > 0) {
            $organizeList = $this->_organizeModel->getOrganizeList($where,$_POST['start'], $_POST['length']);
        }else{
            $organizeList='';
        }
        $output['recordsFiltered'] = $organizeCount;
        $output['data'] = $organizeList;
        echo json_encode($output);
        exit;
    }

    public function addOrganizeAction() {
        //查询所有地区
        $afm = new AreaFlowModel();
        $areas = $afm->getAreaItem();
        $this->_tpl->assign('areas',$areas);
    }

    public function addOrganizeAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        $addDatas = $this->getRequest()->getPost();
        $addDatas = Utils_FilterXss::filterArray($addDatas);
		if (!$addDatas['insti_attr']) {
            $data['errorMsg'] = '机构属性为空';
            echo json_encode($data);
            exit;
        }
        if (!$addDatas['name']) {
            $data['errorMsg'] = '机构名称为空';
            echo json_encode($data);
            exit;
        }
        if (!$addDatas['rate']) {
            $data['errorMsg'] = '利率为空';
            echo json_encode($data);
            exit;
        }
        //检查name是否有重复
        $info = $this->_organizeModel->infoExist($addDatas['name']);
        if (!empty($info)) {
            $data['errorMsg'] = '机构名称重复';
            echo json_encode($data);
            exit;
        }
        $result = $this->_organizeModel->add($addDatas);
        if ($result) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "机构列表";
            $logInfo['option'] = "添加机构";
            $addDatas['id'] = $result;
            $logInfo['new_data'] = Utils_Helper::arrayToString($addDatas);
            $this->_operationModel->add($logInfo);
            
            //如果添加成功,在机构权限表中也插入对应数据
            $ofModel = new OrganizeFlowModel();
            $org_flow_datas['oid'] = $result;
            $ofModel->addData($org_flow_datas);

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

    public function editOrganizeAction() {
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if ($id <= 0) {
            die('机构ID获取失败');
        }

        $info = $this->_organizeModel->get($id);
        if (!$info) {
            die('机构信息获取失败');
        }

        //查询所有地区
        $afm = new AreaFlowModel();
        $areas = $afm->getAreaItem();

        $this->_tpl->assign('areas',$areas);
        $this->_tpl->assign('info', $info);
    }

    public function editOrganizeAjaxAction() {
        $id = (int) $this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if ($id <= 0 || !$data['name'] || !$data['rate']) {
            Utils_Output::errorResponse('参数缺失');
            exit;
        }
        //获取旧数据
        $oldData = $this->_organizeModel->get($id);
        if ($this->_organizeModel->mod($id, $data)) {
            //记录日志
            $newData = $this->_organizeModel->get($id);
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "机构列表";
            $logInfo['option'] = "修改机构";
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

    public function deleteOrganizeAjaxAction() {
        $id = (int) $this->getRequest()->getPost('id');
        if ($id <= 0) {
            Utils_Output::errorResponse('没有权限');
            exit;
        }
        $oldData = $this->_organizeModel->get($id);
        if ($this->_organizeModel->del($id)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "机构列表";
            $logInfo['option'] = "删除机构";
            $logInfo['old_data'] = Utils_Helper::arrayToString($oldData);
            $this->_operationModel->add($logInfo);
            
            //如果删除成功同时删除机构权限列表中记录
            $ofModel = new OrganizeFlowModel();
            $ofModel->del($id);
            
            Utils_Output::errorResponse('OK', 0);
            exit;
        } else {
            Utils_Output::errorResponse('删除失败');
            exit;
        }
        return FALSE;
    }
    
    
    /**
    * 获取机构权限列表
    * @author hgy
    * @since 2016-05-05
    */
    public function flowlistAction() {
      
    }
    
    /**
    * 获取机构权限列表Ajax
    * @author hgy
    * @since 2016-05-05
    */
    public function getflowlistAjaxAction() {
        $where = array();
        if($_POST['org_sx'] !=''){
            $where['xmcd_org.insti_attr'] = (int)$_POST['org_sx'];
        }
        if($_POST['area_ser'] !=''){
            $where['xmcd_org.area'] = (int)$_POST['area_ser'];
        }
        if($_POST['org_name'] != ''){
            $where['xmcd_org.name'] = htmlspecialchars($_POST['org_name']);
        }
        if($_POST['clerk_ser'] != ''){
            $where['xmcd_org.own_clerk'] = htmlspecialchars($_POST['clerk_ser']);
        }
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" => '',
            "data" => array()
        );
        
         //查询出所有节点
        $work_flow =  new WorkflowModel();
        $flows = $work_flow->getNodeList('id asc');
        $flowNames = array();
        if($flows){
            foreach($flows as $k => $v){
                $flowNames[$v['id']] = strtolower($v['ename']);
            }
        }
       // print_r($flowNames);
        //查出总记录数
        $orgFlowCount = $this->_organizeModel->getOrgFlowCount($where);
        if ($orgFlowCount > 0) {
            
            //查出所有机构名称
            $all_orgs = $this->_organizeModel->getAllOrgs();
            $orgNames = array();
            $orgAttrs = array();
            foreach($all_orgs as $k => $v){
                $orgNames[$v['id']] = $v['name'];
                $orgAttrs[$v['id']] = $v['insti_attr'];
            }
            //查询机构权限列表
            $orgFlowList = $this->_organizeModel->getOrgFlowList($where,$_POST['start'], $_POST['length']);
        }
        //print_r($orgNames);
        if($orgFlowList){
            foreach($orgFlowList as $key => $val){
                //遍历审核节点
                foreach($flowNames as $f_key => $f_val){
                    //判断是否是审核节点字段
                    if(!empty($val[$f_val])){
                        //如果是取出该节点下配置的机构的ID,可能有多个，由字符串转为数组
                        $orgs = explode(',',$val[$f_val]); 
                        $org_name = array();
                        
                        //查出机构ID对应的名称
                        foreach($orgs as $o_key => $o_val){
                            $org_name[] = $orgNames[$o_val];
                        }
                        $orgFlowList[$key][$f_val.'_name'] = implode(',',$org_name);


                    }
                }
                foreach($orgAttrs as $kk=>$v){
                    if($kk == $val['id']){
                        $orgFlowList[$key]['insti_attr'] = $v;
                    }
                }
            }
        }
//        print_r($orgFlowList);
        $output['recordsFiltered'] = $orgFlowCount;
        $output['data'] = $orgFlowList;
        echo json_encode($output);
        exit;
    }
    
    /**
    * 编辑机构权限列表
    * @author hgy
    * @since 2016-05-05
    */
    public function editOrganizeFlowAction() {
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if ($id <= 0) {
            die('机构ID获取失败');
        }
		
		$org_info = $this->_organizeModel->get($id);
		if ($org_info['sorted'] == 2) {
            die('该模板不能在此编辑');
        }
        
        //查出机构权限配置详情
        $of_info = $this->_organizeModel->getOrgFlowInfo($id);
        if (!$of_info) {
            die('机构信息获取失败');
        }
        
         //查询出所有节点
        $work_flow =  new WorkflowModel();
        $flows = $work_flow->getNodeList('id asc');
		
		//获取配置权限表中的字段,即审核节点
        $ofModel = new OrganizeFlowModel();
        $fields = $ofModel->getFields();
		//$arra = '(';
		$arra = array();
        foreach($fields as $k => $v){
            if($v == 'oid' || $v == 'created' || $v == 'modified' || $v == 'node_check_pre' || $v == 'node_info_check'|| $v == 'node_notary_got'|| $v == 'node_mortgage_got'){
                
            }else{
				$arra["$v"] = $v;
			}
        }
		unset($fields);

		$name = "";
        if($flows){
            foreach($flows as $key => $val){
				$name = strtolower($val['ename']);
				if(!empty($arra["$name"])){
					$flows[$key]['ename'] = strtolower($val['ename']);
				}else{
					unset($flows[$key]);
				}
               
            }
        }
		
        
        //查出所有机构名称
        // $all_orgs = $this->_organizeModel->getAllOrgs();
        // $orgNames = array();
        // foreach($all_orgs as $k => $v){
            // $orgNames[$v['id']] = $v['name'];
        // }
        
        //遍历审核节点
        foreach($flows as $f_key => $f_val){
            //判断是否是审核节点字段
            if(!empty($of_info[$f_val['ename']])){
                //如果是取出该节点下配置的机构的ID，可能由多个，由字符串转为数组
                $org_vals = explode(',',$of_info[$f_val['ename']]); 
                $of_info[$f_val['ename']] = $org_vals;
            }
        }

        $this->_tpl->assign('info', $of_info);
        $this->_tpl->assign('flows', $flows);
        //$this->_tpl->assign('orgNames', $orgNames);
    }

    /**
    * 获取机构权限列表Ajax
    * @author hgy
    * @since 2016-05-05
    */
    public function editOrganizeFlowAjaxAction() {
        $id = (int) $this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
  
        //检验表单元素
        if (empty($id) || $id <= 0 ) {
            Utils_Output::errorResponse('参数错误');
            exit;
        }
        
        //获取配置权限表中的字段,即审核节点
        $ofModel = new OrganizeFlowModel();
        $fields = $ofModel->getFields();
        foreach($fields as $k => $v){
            if($v == 'oid' || $v == 'created' || $v == 'modified' || $v == 'node_check_pre' || $v == 'node_info_check'|| $v == 'node_notary_got'|| $v == 'node_mortgage_got'){
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
	
	
	//机构配置优化
	public function getorganizeinfoajaxAction(){
		$data = $this->getRequest()->getPost();
		$data = Utils_FilterXss::filterArray($data);
        
        //检验表单元素
        if (empty($data['id']) || $data['id'] <= 0 ) {
            Utils_Output::errorResponse('参数错误');
            exit;
        }
		
		//搜索的机构
		$result = $this->_organizeModel->getOrgName($data['key']);
		
		
		
		//机构节点内容
        $ofModel = new OrganizeFlowModel();
       
		
		$info =  $ofModel->get($data['id']);
		$node = $data["node"];
		$infoq = $info["$node"];
		
		if(!empty($infoq)){
			//如果是取出该节点下配置的机构的ID，可能由多个，由字符串转为数组
			$org_vals = explode(',',$infoq); 
		}
		
		//选中结果
		
		$resulta = $this->_organizeModel->getArrOrgName($org_vals);
		
		foreach($resulta as $key=>$rea){
			$result_in[$rea['id']]['id'] = $rea['id'];
			$result_in[$rea['id']]['name'] = $rea['name'];
			$result_in[$rea['id']]['type'] = 1;
		}
		
		//print_r($result_in);
		
		foreach($result as $key=>$re){
			if(!in_array($re['id'],$org_vals)){
				$result_notin[$re['id']]['id'] = $re['id'];
				$result_notin[$re['id']]['name'] = $re['name'];
				$result_notin[$re['id']]['type'] = 0;
			}
		}
		
		//print_r($result_notin);
		
		if(!empty($result_in) && !empty($result_notin)){
			$result = array_merge($result_in, $result_notin); 
		}else if(!empty($result_in)){
			$result = $result_in;
		}else if(!empty($result_notin)){
			$result = $result_notin;
		}else{
			$result = '';
		}
		
		if(empty($result)){
			echo json_encode(array('error'=>0,"errormsg"=>"查询机构为空"));
			exit;
		}
		unset($result_in);
		unset($result_notin);
		unset($resulta);
		echo json_encode(array('error'=>1,'data'=>$result));
		
		
		return FALSE;
	}
	
	/**
    * 编辑机构权限模板列表
    * @author hgy
    * @since 2016-05-05
    */
    public function editOrganizeFlowTemAction() {
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if ($id <= 0) {
            die('机构ID获取失败');
        }
		
		$org_info = $this->_organizeModel->get($id);
		if ($org_info['sorted'] != 2) {
            die('该机构权限不能在此编辑');
        }
        
        //查出机构权限配置详情
        $of_info = $this->_organizeModel->getOrgFlowInfo($id);
        if (!$of_info) {
            die('机构信息获取失败');
        }
        
         //查询出所有节点
        $work_flow =  new WorkflowModel();
        $flows = $work_flow->getNodeList('id asc');
		
		//获取配置权限表中的字段,即审核节点
        $ofModel = new OrganizeFlowModel();
        $fields = $ofModel->getFields();
		//$arra = '(';
		$arra = array();
        foreach($fields as $k => $v){
            if($v == 'oid' || $v == 'created' || $v == 'modified' || $v == 'node_check_pre' || $v == 'node_info_check'|| $v == 'node_notary_got'|| $v == 'node_mortgage_got'){
                
            }else{
				$arra["$v"] = $v;
			}
        }
		unset($fields);
		
		$name = "";
        if($flows){
            foreach($flows as $key => $val){
				$name = strtolower($val['ename']);
				if(!empty($arra["$name"])){
					$flows[$key]['ename'] = strtolower($val['ename']);
				}else{
					unset($flows[$key]);
				}
               
            }
        }
		
        
        //查出所有机构名称
        // $all_orgs = $this->_organizeModel->getAllOrgs();
        // $orgNames = array();
        // foreach($all_orgs as $k => $v){
            // $orgNames[$v['id']] = $v['name'];
        // }
        
        //遍历审核节点
        foreach($flows as $f_key => $f_val){
            //判断是否是审核节点字段
            if(!empty($of_info[$f_val['ename']])){
                //如果是取出该节点下配置的机构的ID，可能由多个，由字符串转为数组
                $org_vals = explode(',',$of_info[$f_val['ename']]); 
                $of_info[$f_val['ename']] = $org_vals;
            }
        }

        $this->_tpl->assign('info', $of_info);
        $this->_tpl->assign('flows', $flows);
        //$this->_tpl->assign('orgNames', $orgNames);
    }

    /**
    * 获取机构权限列表Ajax
    * @author hgy
    * @since 2016-05-05
    */
    public function editOrganizeFlowTemAjaxAction() {
        $id = (int) $this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
  
        //检验表单元素
        if (empty($id) || $id <= 0 ) {
            Utils_Output::errorResponse('参数错误');
            exit;
        }
        
        //获取配置权限表中的字段,即审核节点
        $ofModel = new OrganizeFlowModel();
        $fields = $ofModel->getFields();
        foreach($fields as $k => $v){
            if($v == 'oid' || $v == 'created' || $v == 'modified' || $v == 'node_check_pre' || $v == 'node_info_check'|| $v == 'node_notary_got'|| $v == 'node_mortgage_got'){
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
	
	//应用模板
	public function useOrganizeTemAction(){
		//查询出模板机构
		$useTem = $this->_organizeModel->getOrgNames();
		$this->_tpl->assign('useTem', $useTem);
	}
	
	//应用模板修改
	public function useOrganizeTemAjaxAction() {
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        //print_r($data);exit;
		
		//检验表单元素
        if (empty($data['org_id']) || $data['org_id'] <= 0 ) {
            Utils_Output::errorResponse('参数错误');
            exit;
        }
		
		if (empty($data['orgNames'])) {
            Utils_Output::errorResponse('未选择需要应用的机构');
            exit;
        }

		//查询模板机构配置
		$ofModel = new OrganizeFlowModel();
		$useTemInfo = $ofModel->get($data['org_id']);
		if($useTemInfo){
			unset($useTemInfo['oid']);
			unset($useTemInfo['created']);
			unset($useTemInfo['modified']);

			$res = $ofModel->upAllTem($data['orgNames'],$useTemInfo);
			if($res){
				Utils_Output::errorResponse('OK', 0);
				exit;
			}
			else{
				Utils_Output::errorResponse('未做修改或其它错误');
				exit; 
			}
		}else{
			Utils_Output::errorResponse('机构模板配置错误');
			exit; 
		}
       
        return FALSE;       
    }
	
	//应用模板搜索
	public function getAllOrgAjaxAction(){
		$data = $this->getRequest()->getPost();
		$data = Utils_FilterXss::filterArray($data);
        
		//搜索的机构
		$result = $this->_organizeModel->getOrgName($data['key']);
		
		if(empty($result)){
			echo json_encode(array('error'=>0,"errormsg"=>"查询机构为空"));
			exit;
		}
		foreach($result as $ke=>$va){
			$result[$ke]['type'] = 0;
		}
		
		echo json_encode(array('error'=>1,'data'=>$result));
		
		
		return FALSE;
	}
    
    
    
}
