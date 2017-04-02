<?php 
class RoleController extends Yaf_Controller_Abstract {
	private $_tpl;
	private $_roleModel;
    private $_operationModel;
	
	public function init() {
		$this->_tpl = $this->getView();
        $this->_roleModel = new RoleModel();
        $this->_operationModel = new OperationModel();
	}
	
	public function indexAction() {

	}

	public function getRoleAjaxAction() {
        $where = array();
        if($_POST['role_name'] != ''){
            $where['name'] = $_POST['role_name'];
        }
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" =>'',
            "data" => array()
        );
		$rolesCount = $this->_roleModel->getRoleCount($where);
        if($rolesCount > 0 ){
            $rolesList = $this->_roleModel->getRoleList($where,$_POST['start'],$_POST['length']);
        }else{
            $rolesList='';
        }

        $output['recordsFiltered'] = $rolesCount;
        $output['data'] = $rolesList;
        echo json_encode( $output );exit;
	}
	
	public function addRoleAction() {
		
	}
	
	public function addRoleAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
		$name = $this->getRequest()->get('name');
        $name = Utils_FilterXss::filterXss($name);
		if (!$name) {
            $data['errorMsg'] = '角色名不能为空';
            echo json_encode($data);
            exit;
		}
        //检查name是否有重复
        $info = $this->_roleModel->nameExist($name);
        if(!empty($info)){
            $data['errorMsg'] = '角色名称重复';
            echo json_encode($data);exit;
        }
        $result = $this->_roleModel->add($name);
		if ($result) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "角色列表";
            $logInfo['option'] = "添加角色";
            $addDatas['id'] = $result;
            $addDatas['name'] = $name;
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

    public function editRoleAction() {
        $id = (int)$this->getRequest()->get('id');
        if ($id <= 0) {
            echo ('角色ID获取失败');
        }

        $info = $this->_roleModel->get($id);
        if (!$info) {
            echo ('角色信息获取失败');
        }

        $this->_tpl->assign('info', $info);
    }

    public function editRoleAjaxAction() {
        $id = (int)$this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if ($id <= 0) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
        if(!$data['name']){
            Utils_Output::errorResponse('角色名称为空');exit;
        }
        //获取修改前数据
        $oldData = $this->_roleModel->get($id);
        if ($this->_roleModel->mod($id, $data)) {
            //记录日志
            $newData = $this->_roleModel->get($id);
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "角色列表";
            $logInfo['option'] = "修改角色";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = Utils_Helper::arrayToString($newData);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('OK',0);exit;
        } else {
            Utils_Output::errorResponse('未做修改或其它错误');exit;
        }
        return FALSE;
    }

    public function deleteRoleAjaxAction() {
        $id = (int)$this->getRequest()->getPost('id');
        if ($id <= 0) {
            Utils_Output::errorResponse('参数缺失');exit;
        }

        //获取删除前数据
        $oldData = $this->_roleModel->get($id);
        if ($this->_roleModel->del($id)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "角色列表";
            $logInfo['option'] = "删除角色";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('OK',0);exit;
        } else {
            $error = 1;
            $errmsg = '删除失败';
        }
        Utils_Output::ajaxJsonReturn(array('error'=>$error, 'errmsg'=>$errmsg));
        return FALSE;
    }
	
	public function detailAction(){
		$role_id = (int)$this->getRequest()->get('id');
		if ($role_id <= 0) {
			die('access denied');
		}
		$role_users = $this->_roleModel->getRoleUsers($role_id);
		$uids = array_keys($role_users);
		$not_assign_users = $this->_roleModel->getNotAssignUsers($uids);
		$this->_tpl->assign('role_users', $role_users);
		$this->_tpl->assign('not_assign_users', $not_assign_users);
	}
	
	public function getUnassignUserListAjaxAction() {
		$users = $this->getRequest()->get('users');
		$word = $this->getRequest()->get('key');

		foreach ($users as $k=>$uid) {
			$uid = intval($uid);
			if ($uid <= 0) {
				unset($users[$k]);
			} else {
				$users[$k] = $uid;
			}
		}

		$not_assign_users = $this->_roleModel->getNotAssignUsers($users, $word);
		Utils_Output::ajaxJsonReturn(array('error'=>0, 'data'=>$not_assign_users));
		return FALSE;
	}
    public function getSearchroleAction(){
        $word = $this->getRequest()->get('key');
        $searchrole = $this->_roleModel->getSearchrole($word);
        Utils_Output::ajaxJsonReturn(array('error'=>0, 'data'=>$searchrole));
        return FALSE;
    }
	
	public function assignUsersAjaxAction() {
            $role_id = (int)$this->getRequest()->get('role_id');
            $users = $this->getRequest()->get('users');

            //日志记录读取修改用户
            $newusers = '';
            foreach ($users as $k=>$uid) {
                    $uid = intval($uid);
                    if ($uid <= 0) {
                            unset($users[$k]);
                    } else {
                            $users[$k] = $uid;
                    }
            }

            if ($role_id <= 0 || count($users) == 0) {
                Utils_Output::errorResponse('未分配用户');exit;
            }

            //获取修改前用户
            $old_role_users = $this->_roleModel->getRoleUsers($role_id);
            $oldusers = '';
            foreach($old_role_users as $user){
                $old_arr[] = $user['id'];
                $user = $user['realname'];
                $oldusers = $oldusers."|".$user;
            }
            
            //去除重复用户
            $users = array_unique($users);
            
            if ($this->_roleModel->setRoleUsers($role_id, $users)) {
                //记录日志
                //获取修改前用户
                $new_role_users = $this->_roleModel->getRoleUsers($role_id);
                $newusers = '';
                foreach($new_role_users as $user){
                    $new_arr[] = $user['id'];
                    $user = $user['realname'];
                    $newusers = $newusers."|".$user;
                }
                $userId = Session_AdminFengkong::instance()->getUid();
                $logInfo = array();
                $logInfo['user_id'] = $userId;
                $logInfo['model'] = "角色列表";
                $logInfo['option'] = "分配用户";
                $logInfo['old_data']  = "id:{$role_id},users:{$oldusers}";
                $logInfo['new_data'] = "id:{$role_id},users:{$newusers}";
                $this->_operationModel->add($logInfo);

                foreach($old_arr as $k=>$v){
                    if(!in_array($v, $new_arr)){
                        $del_arr[] = $v;
                    }
                }
                // echo "<pre><meta charset='utf-8'>";var_dump($old_arr,$new_arr,$del_arr);exit;
                if($del_arr){
                    $cdLinkModel = new Cd_LinkModel();
                    $res = $cdLinkModel->del_link($del_arr, $role_id);
                }

                Utils_Output::errorResponse('设置成功',0);exit;
            } 
            else{
                Utils_Output::errorResponse('设置失败');exit;
            }
            return FALSE;
	}
	
	public function roleMenuAction() {
		$role_id = (int) $this->getRequest()->get('role_id');
		if ($role_id <= 0) {
			echo ('没有权限');
		}
		$menus = $this->_roleModel->getRoleMenu($role_id);
		$menus_json = array();
		foreach ($menus as $menu_floder) {
			$floder = array(
				'id'=>$menu_floder['id'],
				'name'=>$menu_floder['name'],
				'checked'=>(isset($menu_floder['expand']) && $menu_floder['expand']) ? 'true' : 'false',
                'open'=>true
			);
			foreach ($menu_floder['children'] as $menu_child) {
				$floder['children'][] = array(
					'id'=>$menu_child['mid'],
					'name'=>$menu_child['mname'],
					'checked'=>$menu_child['assigned']?TRUE:FALSE,
				);
			}
			$menus_json[] = $floder;
		}
		$this->_tpl->assign('menus_json', json_encode($menus_json));
        return true;
	}
	
	public function assignMenusAjaxAction() {
		$role_id = (int)$this->getRequest()->get('role_id');
		$menus = $this->getRequest()->get('menus');
	
		foreach ($menus as $k=>$menu_id) {
			$menu_id = intval($menu_id);
			if ($menu_id <= 0) {
				unset($menus[$k]);
			} else {
				$menus[$k] = $menu_id;
			}
		}
	
		if ($role_id <= 0) {
            Utils_Output::errorResponse('参数缺失');exit;
		}

		if ($this->_roleModel->setRoleMenus($role_id, $menus)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "角色列表";
            $logInfo['option'] = "分配目录权限";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = "id:{$role_id},".Utils_Helper::arrayToString($menus);
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('保存成功',0);exit;
		} else {
            Utils_Output::errorResponse('保存失败');exit;
		}
		return FALSE;
	}

	public function rolePermissionAction() {
		$role_id = (int) $this->getRequest()->get('role_id');

		if ($role_id <= 0) {
			echo ('没有权限');
		}
	
		$per_list = $this->_roleModel->getRolePermission($role_id);
		$per_json = array();
		foreach ($per_list as $per_floder) {
			$floder = array(
				'id'=>$per_floder['id'],
				'name'=>$per_floder['name'],
				'checked'=>(isset($per_floder['expand']) && $per_floder['expand']) ? 'true' : 'false',
                'open'=>true
			);
			foreach ($per_floder['children'] as $per_child) {
				$floder['children'][] = array(
					'id'=>$per_child['mid'],
					'name'=>$per_child['mname'],
					'checked'=>$per_child['assigned']?TRUE:FALSE,
				);
			}
			$per_json[] = $floder;
		}
		$this->_tpl->assign('per_json', json_encode($per_json));
	}
	
	public function assignPerAjaxAction() {
		$role_id = (int)$this->getRequest()->get('role_id');
		$pers = $this->getRequest()->get('pers');
	
		foreach ($pers as $k=>$pid) {
			$pid = intval($pid);
			if ($pid <= 0) {
				unset($pers[$k]);
			} else {
				$pers[$k] = $pid;
			}
		}
	
		if ($role_id <= 0) {
            Utils_Output::errorResponse('参数缺失');exit;
		}
	
		if ($this->_roleModel->setRolePermission($role_id, $pers)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "角色列表";
            $logInfo['option'] = "分配权限";
//            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = "id:{$role_id},".Utils_Helper::arrayToString($pers);
            $this->_operationModel->add($logInfo);
            Utils_Output::errorResponse('保存成功',0);exit;
		} else {
            Utils_Output::errorResponse('未做修改或其它错误');exit;
		}
			return FALSE;
	}


	public function cddetailAction(){
		$role_id = (int)$this->getRequest()->get('id');
		if ($role_id <= 0) {
			die('access denied');
		}
		$role_users = $this->_roleModel->getCdRoleUsers($role_id);
		$uids = array_keys($role_users);
		$not_assign_users = $this->_roleModel->getNotAssignCdUsers($uids);
		$this->_tpl->assign('role_users', $role_users);
		$this->_tpl->assign('not_assign_users', $not_assign_users);
	}

	public function getUnassignCdUserListAjaxAction() {
		$users = $this->getRequest()->get('users');
		$word = $this->getRequest()->get('key');

		foreach ($users as $k=>$uid) {
			$uid = intval($uid);
			if ($uid <= 0) {
				unset($users[$k]);
			} else {
				$users[$k] = $uid;
			}
		}

		$not_assign_users = $this->_roleModel->getNotAssignCdUsers($users, $word);
		Utils_Output::ajaxJsonReturn(array('error'=>0, 'data'=>$not_assign_users));
		return FALSE;
	}

	public function assignCdUsersAjaxAction() {
		$role_id = (int)$this->getRequest()->get('role_id');
		$users = $this->getRequest()->get('users');

        //日志记录读取修改用户
        $newusers = '';
		foreach ($users as $k=>$uid) {
			$uid = intval($uid);
			if ($uid <= 0) {
				unset($users[$k]);
			} else {
				$users[$k] = $uid;
			}
		}

		if ($role_id <= 0 || count($users) == 0) {
            Utils_Output::errorResponse('未分配用户');exit;
		}

        //获取修改前用户
        $old_role_users = $this->_roleModel->getCdRoleUsers($role_id);
        $oldusers = '';
        foreach($old_role_users as $user){
            $user = $user['realname'];
            $oldusers = $oldusers."|".$user;
        }
		if ($this->_roleModel->setCdRoleUsers($role_id, $users)) {
            //记录日志
            //获取修改前用户
            $new_role_users = $this->_roleModel->getCdRoleUsers($role_id);
            $newusers = '';
            foreach($new_role_users as $user){
                $user = $user['realname'];
                $newusers = $newusers."|".$user;
            }
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "角色列表";
            $logInfo['option'] = "分配用户";
            $logInfo['old_data']  = "id:{$role_id},users:{$oldusers}";
            $logInfo['new_data'] = "id:{$role_id},users:{$newusers}";
            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('设置成功',0);exit;
		} else {
            Utils_Output::errorResponse('设置失败');exit;
		}
		return FALSE;
	}
}