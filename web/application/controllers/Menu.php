<?php 
class MenuController extends Yaf_Controller_Abstract {
	private $_tpl;
    private $_menuModel;
    private $_operationModel;
	
	public function init() {
		$this->_tpl = $this->getView();
        $this->_menuModel = new MenuModel();
        $this->_operationModel = new OperationModel();
	}
	
	public function indexAction() {

	}

	public function getParentMenuListAjaxAction() {
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" =>'',
            "data" => array()
        );

		$menuCount = $this->_menuModel->getParentMenuCount();

        if($menuCount > 0 ){
            $menuList = $this->_menuModel->getParentMenuList($_POST['start'],$_POST['length']);
        }
        $output['recordsFiltered'] = $menuCount;
        $output['data'] = $menuList;
        echo json_encode( $output );exit;

	}

    //查看子菜单
    public function detailAction(){
        $menu_id = (int)$this->getRequest()->get('id');
        if ($menu_id < 0) {
            echo "没有权限";
        }
        $this->_tpl->assign('menu_id', $menu_id);
    }


    public function getMenuListAjaxAction() {
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" =>'',
            "data" => array()
        );
        $menuParentId = (int)$this->getRequest()->get('id');
        $menuCount = $this->_menuModel->getMenuCount($menuParentId);

        if($menuCount > 0 ){
            $menuList = $this->_menuModel->getMenuList($_POST['start'],$_POST['length'],$menuParentId);
        }
        $output['recordsFiltered'] = $menuCount;
        $output['data'] = $menuList;
        echo json_encode( $output );exit;

    }
	
	public function addParentMenuAction() {
		
	}
	
	public function addParentMenuAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        $addDatas = $this->getRequest()->getPost();
        $addDatas = Utils_FilterXss::filterArray($addDatas);
		if (!$addDatas['name']) {
            $data['errorMsg'] = '菜单名称为空';
            echo json_encode($data);
            exit;
		}
        //检查name是否有重复
        $info = $this->_menuModel->parentNameExist($addDatas['name']);
        if(!empty($info)){
            $data['errorMsg'] = '菜单名称重复';
            echo json_encode($data);exit;
        }
		$result = $this->_menuModel->add($addDatas);
		if ($result) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "菜单列表";
            $logInfo['option'] = "添加菜单";
            $addDatas['id'] = $result;
            $logInfo['new_data'] = Utils_Helper::arrayToString($addDatas);
            $this->_operationModel->add($logInfo);

            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            echo json_encode($data);exit;
		} else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);exit;
		}

	}

    public function addMenuAction() {
        $menu_id = (int)$this->getRequest()->get('id');
        if ($menu_id < 0) {
            echo "没有权限";
        }
        $this->_tpl->assign('parent', $menu_id);
    }

    public function addMenuAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        $addDatas = $_POST;
        if (!$addDatas['name']) {
            $data['errorMsg'] = '子菜单名称为空';
            echo json_encode($data);
            exit;
        }
        $addDatas = $_POST;
        if (!$addDatas['url']) {
            $data['errorMsg'] = 'url为空';
            echo json_encode($data);
            exit;
        }
        //检查name是否有重复
        $info = $this->_menuModel->nameExist($addDatas['name']);
        if(!empty($info)){
            $data['errorMsg'] = '菜单名称重复';
            echo json_encode($data);exit;
        }
        $result = $this->_menuModel->add($addDatas);
        if ($result) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "菜单列表";
            $logInfo['option'] = "添加子菜单";
            $addDatas['id'] = $result;
            $logInfo['new_data'] = Utils_Helper::arrayToString($addDatas);
            $this->_operationModel->add($logInfo);
            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            echo json_encode($data);exit;
        } else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);exit;
        }

    }

    public function editParentMenuAction() {
        $id = (int)$this->getRequest()->get('id');
        if ($id <= 0) {
            echo "菜单ID获取失败";
        }

        $info = $this->_menuModel->get($id);
        if (!$info) {
            echo "菜单信息获取失败";
        }

        $this->_tpl->assign('info', $info);
    }

    public function editParentMenuAjaxAction() {
        $id = (int)$this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if ($id <= 0 || !$data['name']) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
        //获取修改前数据
        $oldData = $this->_menuModel->get($id);
        if ($this->_menuModel->mod($id, $data)) {
            //记录日志
            $newData = $this->_menuModel->get($id);
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "菜单列表";
            $logInfo['option'] = "修改菜单";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = Utils_Helper::arrayToString($newData);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('OK',0);exit;
        } else {
            Utils_Output::errorResponse('未做修改或其它错误');exit;
        }
        return FALSE;
    }

    public function deleteParentMenuAjaxAction() {
        $id = (int)$this->getRequest()->getPost('id');
        if ($id <= 0) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
        //获取删除前数据
        $oldData = $this->_menuModel->get($id);
        //删除菜单和子菜单
        if ($this->_menuModel->delParentMenu($id)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "菜单列表";
            $logInfo['option'] = "删除菜单";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('删除成功',0);exit;
        } else {
            Utils_Output::errorResponse('删除失败');exit;
        }
        return FALSE;
    }


    public function editMenuAction() {
        $id = (int)$this->getRequest()->get('id');
        if ($id <= 0) {
            echo ('子菜单ID获取失败');
        }

        $info = $this->_menuModel->get($id);
        if (!$info) {
            echo ('子菜单信息获取失败');
        }

        $this->_tpl->assign('info', $info);
    }

    public function editMenuAjaxAction() {
        $id = (int)$this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if ($id <= 0 || !$data['name'] || !$data['url']) {
            $error = 1;
            $errmsg = '参数缺失';
        }
        //获取修改前数据
        $oldData = $this->_menuModel->get($id);
        if ($this->_menuModel->mod($id, $data)) {
            //记录日志
            $newData = $this->_menuModel->get($id);
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "菜单列表";
            $logInfo['option'] = "修改子菜单";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = Utils_Helper::arrayToString($newData);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('OK',0);exit;
        } else {
            Utils_Output::errorResponse('未做修改或其它错误');exit;
        }
        return FALSE;
    }

    public function deleteMenuAjaxAction() {
        $id = (int)$this->getRequest()->getPost('id');
        if ($id <= 0) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
        //获取删除前数据
        $oldData = $this->_menuModel->get($id);
        if ($this->_menuModel->del($id)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "菜单列表";
            $logInfo['option'] = "删除子菜单";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('OK',0);exit;
        } else {
            Utils_Output::errorResponse('删除失败');exit;
        }
        return FALSE;
    }

    public function addCdPidAction(){
        $id = (int)$this->getRequest()->get('id');
        $name = $this->_menuModel->get($id);
        $menu_list = $this->_menuModel->getChildList();
        $this->_tpl->assign('id', $id);
        $this->_tpl->assign('name', $name['name']);
        $this->_tpl->assign('menu_list', $menu_list);
    }

    public function addCdPidAjaxAction(){
        $id = (int)$this->getRequest()->getPost('id');
        $pid = (int)$this->getRequest()->getPost('pid');
        $cdMenuLinkModel = new Cd_MenuLinkModel();
        $res = $cdMenuLinkModel->addMenuLink($id, $pid);
        if($res){
            Utils_Output::errorResponse('OK',0);exit;
        }else{
            Utils_Output::errorResponse('添加/修改失败');exit;
        }
    }

}