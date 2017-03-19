<?php
class RoleModel{

    private $_userRoleDb;

    private $_roleDb;

    private $_userDb;



    public function __construct() {
        $this->_userRoleDb = new Role_UserRoleModel();
        $this->_roleDb = new Role_RoleModel();
        $this->_userDb = new Admin_UserModel();
    }
    
    public function getRoleInfo($role_id) {
    	return $this->_roleDb->where(array('id'=>$role_id))->find();
    }

    public function nameExist($name) {
        return $this->_roleDb->where(array('name'=>$name))->find();
    }

    public function checkPriv($uid, $controller, $action) {
        $roles = $this->_userRoleDb
            ->where(array('user_id'=>$uid))
            ->field('role_id')
            ->select();
        //没有任何角色 直接返回失败
        if (!$roles) {
            return FALSE;
        }
        $role_id = array();
        foreach ($roles as $role) {
            $role_id[] = $role['role_id'];
        }
        $result = $this->_rolePermissionDb
            ->join('xmcd_permission  on xmcd_permission.id = xmcd_rolepermission.permission_id ')
            ->where(array('xmcd_rolepermission.role_id'=>array('in',$role_id)))
            ->where(array('xmcd_permission.ctrl'=>$controller))
            ->where(array('xmcd_permission.action'=>$action))
            ->find();
        return !!$result;
    }

    public function getRoleList($where,$page,$pageSize){
        if($where!=''){
            $list = $this->_roleDb
                ->where($where)
                ->limit($page,$pageSize)
                ->select();
        }else{
            $list = $this->_roleDb
                ->limit($page,$pageSize)
                ->select();
        }

    foreach ($list as $k=>$r) {
        $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);
    }
    return $list;
    }
    
    /**
     * 获取所有的角色列表
     * @return array
     */
    public function getAllRoleList() {
    	$roles = $this->_roleDb->field('id,name')->order('id asc')->select();
    	$ret = [];
    	foreach ($roles as $role) {
    		$ret[$role['id']] = $role['name'];
    	}
    	return $ret;
    }

    public function getRoleCount($where){
        if($where!=''){
            return $this->_roleDb->where($where)->count();
        }else{
            return $this->_roleDb->count();
        }
    }

    public function add($name) {
        if(!$name){
            return false;
        }
        return $this->_roleDb->data(array(
            'name'=>$name,
            'created'=>time(),
        ))->add();
    }

    /**
    SELECT `ur`.`user_id`, `u`.`admin_user_name` AS `name`, `u`.`admin_user_true_name` AS `true_name`, `u`.* FROM `yn_bg_user_role` AS `ur`
    INNER JOIN `yn_admin_user` AS `u` ON ur.user_id=u.admin_user_id WHERE (role_id=1)
     */
    public function getRoleUsers($roleId) {
        if(!$roleId){
            return false;
        }
        $users = $this->_userRoleDb
            ->join('xmcd_users  on xmcd_user_role.user_id = xmcd_users.id ')
            ->where(array('xmcd_user_role.role_id'=>$roleId))
            ->field('xmcd_users.id,xmcd_users.realname,xmcd_users.phone,xmcd_users.*')
            ->select();
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }

    public function getRoleUsersByRoleIds($roleIds = array()) {
        if(!$roleIds){
            return false;
        }
        $users = $this->_userRoleDb
            ->join('xmcd_users  on xmcd_user_role.user_id = xmcd_users.id ')
            ->where(array('xmcd_user_role.role_id'=>array('in',$roleIds)))
            ->field('xmcd_users.id,xmcd_users.realname,xmcd_users.phone,xmcd_users.*')
            ->select();
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }

    /**
    SELECT `yn_admin_user`.`admin_user_id` AS `user_id`, `yn_admin_user`.`admin_user_name` AS `name`, `yn_admin_user`.`admin_user_true_name` AS `true_name` FROM `yn_admin_user` WHERE (admin_user_id not in (4, 5, 8, 9, 10, 12, 20, 21, 23, 24, 25, 26, 28, 30, 35, 36, 39))
     */
    public function getNotAssignUsers($users, $key='') {
        if($users){
            $users = $this->_userDb->where(array('id'=>array('not in',$users),'realname'=>array('like',"%".$key."%")))->field('id,realname,phone')->select();
        }else if($key){
            $users = $this->_userDb->where(array('realname'=>array('like',"%".$key."%")))->field('id,realname,phone')->select();
        }else{
            $users = $this->_userDb->field('id,realname,phone')->select();
        }
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }

    public function setRoleUsers($id, $users) {
        //先删除xmcd_user_role表中role_id=$id的记录
        $this->_userRoleDb->where(array('role_id'=>$id))->delete();
        
        foreach ($users as $uid) {           
            $ret = $this->_userRoleDb->data(array(
                'user_id'=>$uid,
                'role_id'=>$id,
                'created'=>time()
            ))->add();        
        }
        return TRUE;
    }


    /**
    SELECT `m`.`id` AS `mid`, `m`.`parent` AS `pid`, `m`.`name` AS `mname`, `m`.`sort`, `p`.`name` AS `pname`, `p`.* FROM `yn_bg_menu` AS `m`
    INNER JOIN `yn_bg_menu` AS `p` ON m.parent=p.id
    SELECT `yn_bg_rolemenu`.* FROM `yn_bg_rolemenu` WHERE (role_id=1)
     */
    public function getRoleMenu($role_id) {
        if(!$role_id){
            return false;
        }

        $menuDb = new Role_MenuModel();
        $menu_list = $menuDb->alias('m')->
            join('xmcd_menu as p on m.parent = p.id')
            ->field(array('m.id'=>'mid','m.parent'=>'pid','m.name'=>'mname','p.name'=>'pname','p.*'))
            ->select();

        $rmDb = new Role_RoleMenuModel();
        $menus = $rmDb->where(array('role_id'=>$role_id))->select();

        $role_menus = array();
        foreach ($menus as $m) {
            $role_menus[$m['menu_id']] = TRUE;
        }

        $all_menu = array();
        foreach ($menu_list as $m) {
            if (!isset($all_menu[$m['pid']])) {
                $all_menu[$m['pid']] = array(
                    'id'=>$m['pid'],
                    'name'=>$m['pname'],
                    'children'=>array()
                );
            }
            if ($role_menus[$m['mid']]) {
                $m['assigned'] = TRUE;
                $all_menu[$m['pid']]['expand'] = TRUE;
            } else {
                $m['assigned'] = FALSE;
            }
            $all_menu[$m['pid']]['children'][] = $m;
        }
        return $all_menu;
    }


    public function setRoleMenus($id, $menus) {
        if(!$id){
          return false;
        }
        //先删除xmcd_rolemenu表中role_id=$id的记录
        $this->_roleMenuDb->where(array('role_id'=>$id))->delete();

        foreach ($menus as $menu_id) {
            $this->_roleMenuDb->data(array(
                'role_id'=>$id,
                'menu_id'=>$menu_id,
                'created'=>time()
            ))->add();
        }

        return TRUE;
    }



    /**
    SELECT `m`.`id` AS `mid`, `m`.`parent` AS `pid`, `m`.`name` AS `mname`, `p`.`name` AS `pname`, `p`.* FROM `yn_bg_permission` AS `m`
    INNER JOIN `yn_bg_permission` AS `p` ON m.parent=p.id
    SELECT `yn_bg_rolepermission`.* FROM `yn_bg_rolepermission` WHERE (role_id=1)
     */
    public function getRolePermission($role_id) {
        if(!$role_id){
            return false;
        }

        $per_list = $this->_permissionDb
            ->alias('m')
            ->join('xmcd_permission as p on m.parent = p.id')
            ->field(array('m.id'=>'mid','m.parent'=>'pid','m.name'=>'mname','p.name'=>'pname','p.*'))
            ->select();

        $permissions = $this->_rolePermissionDb->where(array('role_id'=>$role_id))->select();
        $role_permissions = array();
        foreach ($permissions as $p) {
            $role_permissions[$p['permission_id']] = TRUE;
        }

        $all_permission = array();
        foreach ($per_list as $p) {
            if (!isset($all_permission[$p['pid']])) {
                $all_permission[$p['pid']] = array(
                    'id'=>$p['pid'],
                    'name'=>$p['pname'],
                    'children'=>array()
                );
            }
            if ($role_permissions[$p['mid']]) {
                $p['assigned'] = TRUE;
                $all_permission[$p['pid']]['expand'] = TRUE;
            } else {
                $p['assigned'] = FALSE;
            }
            $all_permission[$p['pid']]['children'][] = $p;
        }
        return $all_permission;
    }

    public function setRolePermission($id, $pers) {
        if(!$id){
            return false;
        }
        //先删除xmcd_rolemenu表中role_id=$id的记录
        $this->_rolePermissionDb->where(array('role_id'=>$id))->delete();

        foreach ($pers as $per_id) {
            $this->_rolePermissionDb->data(array(
                'role_id'=>$id,
                'permission_id'=>$per_id,
                'created'=>time()
            ))->add();
        }

        return TRUE;
    }


    public function getUserMenus($uid) {

        if(!$uid){
            return false;
        }

        $roles = $this->_userRoleDb->where(array('user_id'=>$uid))->field('role_id')->select();
        //SELECT `yn_bg_user_role`.`role_id` FROM `yn_bg_user_role` WHERE (user_id='12')
        if (!$roles) {
            return array();
        }

        $role_id = array();
        foreach ($roles as $r) {
            $role_id[] = $r['role_id'];
        }


        $menus = $this->_roleMenuDb->distinct(true)->where(array('role_id'=>array('in',$role_id)))->field('menu_id')->select();
        //SELECT distinct(menu_id) AS `mid` FROM `yn_bg_rolemenu` WHERE (role_id in ('1'))    
        if (!$menus) {
            return array();
        }
        $menu_id = array();
        foreach ($menus as $m) {
            $menu_id[] = $m['menu_id'];
        }

        $menu_list = $this->_menuDb->alias('m')
            ->join('xmcd_menu as p on m.parent = p.id')
            ->where(array('m.id'=>array('in',$menu_id)))
            ->field(array('m.id'=>'mid','m.parent'=>'pid','m.name'=>'mname','m.url'=>'murl','m.sort','p.name'=>'pname','p.*'))
            ->order('p.sort asc,m.sort asc')
            ->select();
        //SELECT `m`.`id` AS `mid`, `m`.`parent` AS `pid`, `m`.`name` AS `mname`, `m`.`url` AS `murl`, `m`.`sort`, `p`.`name` AS `pname`, `p`.* FROM `yn_bg_menu` AS `m`
        //INNER JOIN `yn_bg_menu` AS `p` ON m.parent=p.id WHERE (m.id in ('17', '18', '19', '20'))
        //ORDER BY `p`.`sort` ASC, `m`.`sort` ASC

        $all_menu = array();
        foreach ($menu_list as $m) {
            if (!isset($all_menu[$m['pid']])) {
                $all_menu[$m['pid']] = array(
                    'id'=>$m['pid'],
                    'name'=>$m['pname'],
                    'children'=>array()
                );
            }
            $all_menu[$m['pid']]['children'][] = $m;
        }
        
        return $all_menu;
    }


    /**
    select * from xmcd_role r join xmcd_user_role fur on fur.role_id = r.id where fur.user_id = 1;
     */
    public function getUserRoles($userId) {
        if(!$userId) return false;
        $roles = $this->_roleDb
            ->join('xmcd_user_role on xmcd_user_role.role_id = xmcd_role.id ')
            ->where(array('xmcd_user_role.user_id'=>$userId))
            ->field('xmcd_role.id,xmcd_role.name')
            ->select();
        $ret = array();
        foreach ($roles as $role) {
        	$ret[] = array(
        		'id'=>$role['id'],
        		'name'=>$role['name'],
        	);
        }
        return $ret;
    }

    /**
     * 获取指定角色的所有用户
     * @param  $roles
     * @return array
     */
    public function getRolesUsers($roles) {
    	$users = [];
    	$roleUsers = (new Role_UserRoleModel())->where(array('role_id'=>array('in', $roles)))->field('distinct(user_id) uid')->select();
    	if (!$roleUsers) {
    		return $users;
    	}
    	foreach ($roleUsers as $u) {
    		$users[] = $u['uid'];
    	}
    	return $users;
    }


    public function get($id) {
        return $this->_roleDb->where(array('id'=>$id))->find();
    }

    public function mod($id, $data) {
        return $this->_roleDb->where(array('id'=>$id))->save($data);
    }

    public function del($id) {
        return $this->_roleDb->where(array('id'=>$id))->delete();
    }

    /**
     * 获取用户所有角色 根据角色id排序
     */
    public function getUserRolesOrderByRole($userId) {
        if(!$userId) return false;
        $roles = $this->_roleDb
            ->join('xmcd_user_role on xmcd_user_role.role_id = xmcd_role.id ')
            ->where(array('xmcd_user_role.user_id'=>$userId))
            ->field('xmcd_role.id,xmcd_role.name')
            ->order("xmcd_user_role.role_id")
            ->select();
        $ret = array();
        foreach ($roles as $role) {
            $ret[] = array(
                'id'=>$role['id'],
                'name'=>$role['name'],
            );
        }
        return $ret;
    }
    
   
    /**
    * 获取车贷用户所有角色 根据角色id排序
    * @author hgy
    * @param int $userId int $type
    * @return array
    */
    public function getCdUserRolesOrderByRole($userId,$type=null) {
        if(!$userId) 
            return false;
        
//        $roles = $this->_roleDb
//            ->join('xmcd_usercd_role on xmcd_usercd_role.role_id = xmcd_role.id ')
//            ->where(array('xmcd_usercd_role.cd_user_id'=>$userId))
//            ->field('xmcd_role.id,xmcd_role.name')
//            ->order("xmcd_usercd_role.role_id")
//            ->select();
        
        $this->_roleDb->field('xmcd_role.id,xmcd_role.name,rc.nid as nid')->order("xmcd_user_role.role_id");
        $this->_roleDb->join('xmcd_user_role on xmcd_user_role.role_id = xmcd_role.id ');
        $this->_roleDb->join('xmcd_role_cdnode rc on xmcd_role.id=rc.rid');
        $this->_roleDb->where(array('xmcd_user_role.user_id'=>$userId));
        if(!empty($type))
            $this->_roleDb->where(array('xmcd_role.type'=>$type));
        
        $roles = $this->_roleDb->select();
        
        $ret = array();
        foreach ($roles as $role) {
            $ret[] = array(
                'id'=>$role['id'],
                'name'=>$role['name'],
                'nid'=>$role['nid'],
            );
        }
        return $ret;
    }

    //获取某机构下的某角色用户
    public function getOidRoleUsers($roleId,$oid) {
        if(!$roleId || !$oid){
            return false;
        }
        $users = $this->_userRoleDb
            ->join('xmcd_users  on xmcd_user_role.user_id = xmcd_users.id ')
            ->where(array('xmcd_user_role.role_id'=>$roleId,'xmcd_users.oid'=>$oid))
            ->field('xmcd_users.id,xmcd_users.realname,xmcd_users.phone,xmcd_users.*')
            ->select();
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }

    //获取某机构下的某角色用户
    public function getOidRoleUsers_zongbu($roleId,$oid) {
        if(!$roleId || !$oid){
            return false;
        }
        $users = $this->_userRoleDb
            ->join('xmcd_users  on xmcd_user_role.user_id = xmcd_users.id ')
            // ->where(array('xmcd_user_role.role_id'=>$roleId,'xmcd_users.oid'=>$oid))
            ->where(array('xmcd_user_role.role_id'=>$roleId))
            ->field('xmcd_users.id,xmcd_users.realname,xmcd_users.phone,xmcd_users.*')
            ->select();
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }

    //获取多个机构下的某角色用户
    public function getOidsRoleUsers($roleId,$oids=array()) {
        if(!$roleId || !$oids){
            return false;
        }
        $users = $this->_userRoleDb
            ->join('xmcd_users  on xmcd_user_role.user_id = xmcd_users.id ')
            ->where(array('xmcd_user_role.role_id'=>$roleId,'xmcd_users.oid'=>array('in',$oids)))
            ->field('xmcd_users.id,xmcd_users.realname,xmcd_users.phone,xmcd_users.*')
            ->select();
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }
	
	//获取除去该机构下其他机构角色用户
	 public function getNotOidRoleUsers($roleId,$oid) {
        if(!$roleId || !$oid){
            return false;
        }
        $users = $this->_userRoleDb
            ->join('xmcd_users  on xmcd_user_role.user_id = xmcd_users.id ')
            ->where(array('xmcd_user_role.role_id'=>$roleId,'xmcd_users.oid'=>array('neq',$oid)))
            ->field('xmcd_users.id,xmcd_users.realname,xmcd_users.phone,xmcd_users.*')
            ->select();
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }


    public function getCdRoleUsers($roleId) {
        if(!$roleId){
            return false;
        }
        $users = $this->_userCdRoleDb
            ->join('xmcd_cd_admin  on xmcd_usercd_role.cd_user_id = xmcd_cd_admin.id ')
            ->where(array('xmcd_usercd_role.role_id'=>$roleId))
            ->field('xmcd_cd_admin.id,xmcd_cd_admin.realname,xmcd_cd_admin.phone,xmcd_cd_admin.*')
            ->select();
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }

    public function getNotAssignCdUsers($users, $key='') {
        $cduserDb = new Cdnationwide_ChedaiAdminModel();
        if($users){
            $users = $cduserDb->where(array('id'=>array('not in',$users),'realname'=>array('like',"%".$key."%")))->field('id,realname,phone')->select();
        }else if($key){
            $users = $cduserDb->where(array('realname'=>array('like',"%".$key."%")))->field('id,realname,phone')->select();
        }else{
            $users = $cduserDb->field('id,realname,phone')->select();
        }
        $ret = array();
        foreach ($users as $user) {
            $ret[$user['id']] = $user;
        }
        return $ret;
    }
    public function getSearchrole($key){
            $list = $this->_roleDb->field('id,name')->where(array('name'=>array('like','%'.$key.'%')))->select();
            return $list;
    }
    public function setCdRoleUsers($id, $users) {
        //先删除xmcd_user_role表中role_id=$id的记录
        $this->_userCdRoleDb->where(array('role_id'=>$id))->delete();
        foreach ($users as $uid) {
            $ret = $this->_userCdRoleDb->data(array(
                'cd_user_id'=>$uid,
                'role_id'=>$id,
                'created'=>time()
            ))->add();
        }
        return TRUE;
    }
    
    
    /**
    * 检查用户是否有对应角色
    * @auth hgy
    * @date 2016-06-27
    * @param int $user_id : 用户ID
    * @param int $role_id : 角色ID
    * @return array
    */
    public function checkUserByRole($user_id,$role_id) {
        if(!$role_id || !$user_id){
            return false;
        }
        
//        $res = $this->_userCdRoleDb
//            ->where(array('xmcd_usercd_role.role_id'=>$role_id))
//            ->where(array('xmcd_usercd_role.cd_user_id'=>$user_id))
//            ->field('xmcd_usercd_role.*')
//            ->find();
        
        $res = $this->_userRoleDb
            ->where(array('xmcd_user_role.role_id'=>$role_id))
            ->where(array('xmcd_user_role.user_id'=>$user_id))
            ->field('xmcd_user_role.*')
            ->find();
        
        return $res;
    }
    public function getUserRole($uid){
        return $this->_userRoleDb->field('role_id')->where(array('user_id'=>$uid))->select();
    }
    public function getidByname($name){
        $rid = $this->_roleDb->field('id')->where(array('name'=>$name))->find();
        $role_id = $rid['id'];
        return $role_id;
    }
    public function getuidByroleid($role_id){
        $users =  $this->_userRoleDb->field('user_id')->where(array('role_id'=>$role_id))->select();
        if($users!=''){
            foreach($users as $k=>$v){
                $u[] = $v['user_id'] ;
            }
            return $u;
        }else{
            return false;
        }

    }
	//添加用户时添加角色
	public function setusrtrole($users,$roles){
		$this->_userRoleDb->where(array('user_id'=>$users))->delete();
		foreach ($roles as $rid) {
			$ret = $this->_userRoleDb->data(array(
                'user_id'=>$users,
                'role_id'=>$rid,
                'created'=>time()
            ))->add();        
        }
		return true;
	}
	//模糊搜索房贷角色
	public function getlikerole($key){
			//$list = $this->_roleDb->field('id,name')->where(array('name'=>array('like','%'.$key.'%'),'type'=>array('EXP','IS NULL')))->select();
			//$listtwo = $this->_roleDb->field('id,name')->where(array('name'=>array('like','%'.$key.'%'),'type'=>1))->select();
			//$list = array_merge($list,$listtwo);
			$list = $this->_roleDb->field('id,name')->where(array('name'=>array('like','%'.$key.'%')))->select();
            return $list;
    }
	//根据id 查询数据
	public function getallidrole($id){
			$list = $this->_roleDb->field('id,name')->where(array('id'=>array('in',$id)))->select();
            return $list;
    }
}
