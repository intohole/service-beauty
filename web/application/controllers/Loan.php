<?php

class LoanController extends Yaf_Controller_Abstract {

	public function init(){
		$this->tpl = $this->getView();
		$this->_req = $this->getRequest();
		$this->_loginUser = Session_AdminFengkong::instance();
		$this->_user = new AdminUserModel();
        $this->_appModel = new Cd_AppModel();
	}

    public function contranctPrintAction(){
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 1;
        // $type = isset($_GET['type']) ? $_GET['type'] : 1;
        $this->tpl->assign("tab", $tab);
        // $this->tpl->assign('type', $type);
    }

    public function contranctPrintAjaxAction(){
        $tab = $_POST['tab'];
        // $type = $_POST['type'];

        //获取用户角色
        $rm = new RoleModel();
        $roles = $rm->getUserRoles($this->_loginUser->uid);
        if ($roles) {
            $rods = array();
            foreach ($roles as $k => $v) {
                $rods[] = $v['id'];
            }
        }
        
        //获取用户所属机构
        $oid = $this->_user->getOidByUid($this->_loginUser->uid);
       
        
        $count = $this->_appModel->getCount($tab,$rods,$oid);
        $data = $this->_appModel->getList($_POST['start'],$_POST['length'],$tab,$rods,$oid);
        if(!$data){
            $output['recordsFiltered'] = 0;
            $output['data'] = 0;
            echo json_encode($output);
            exit;
        }
        foreach($data as $k=>$v){
            if($v['signcreated']){
                $data[$k]['signcreated'] = date('Y年m月d日', $v['signcreated']);
            }
        }
        $output['recordsFiltered'] = $count;
        $output['data'] = $data;
        echo json_encode($output);
        exit;
        
    }

}
