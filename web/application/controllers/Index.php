<?php
class IndexController extends Yaf_Controller_Abstract {

    private $_adminUserModel;

	public function init() {
        $this->_adminUserModel = new AdminUserModel();
        $this->_tpl = $this->getView();
	}
	
	public function indexAction() {

        $userName = Session_AdminFengkong::instance()->getUserName();
        $userId = Session_AdminFengkong::instance()->getUid();

        $menus = (new RoleModel())->getUserMenus($userId);
// echo "<pre><meta charset='utf-8'>";var_dump($menus);exit;
        $this->_tpl->assign('menus', $menus);
        $this->_tpl->assign('username', $userName);
	}


    public function modifyPasswdAction(){

    }

    public function modifyPasswdAjaxAction(){
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        $postDatas = $_POST;
        $oldpwd = $postDatas['oldpwd'];
        $newpwd = $postDatas['newpwd'];
        $confirmpwd = $postDatas['confirmpwd'];
        if (!$oldpwd || !$newpwd || !$confirmpwd) {
            $data['errorMsg'] = '参数缺失';
            echo json_encode($data);
            exit;
        }
        if ($oldpwd == $newpwd) {
            $data['errorMsg'] = '原密码与新密码一致';
            echo json_encode($data);
            exit;
        }
        if (strlen($newpwd) < 6) {
            $data['errorMsg'] = '新密码长度不符合要求';
            echo json_encode($data);
            exit;
        }
        if ($newpwd != $confirmpwd) {
            $data['errorMsg'] = '两次密码不一致';
            echo json_encode($data);
            exit;
        }
        //检查原密码是否正确
        $userId = Session_AdminFengkong::instance()->getUid();
        $info = $this->_adminUserModel->getUserInfoByUid($userId);
        if ((md5(md5($oldpwd).$info['salt']) == $info['passwd'])){
            //修改密码
            $info = $this->_adminUserModel->changePwd($userId,$newpwd);
            if($info){
                $data['error'] = 0;
                $data['errorMsg'] = '密码修改成功';
                echo json_encode($data);exit;
            }else{
                $data['errorMsg'] = '密码修改失败';
                echo json_encode($data);
                exit;
            }

        }else{
            $data['errorMsg'] = '原密码错误';
            echo json_encode($data);
            exit;
        }

    }




}