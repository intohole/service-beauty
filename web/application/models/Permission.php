<?php
class PermissionModel {

    private $_userRoleDb;

    private $_rolePermissionDb;

    private $_permissionDb;

    private $_roleDb;

    public function __construct() {
        $this->_userRoleDb = new Role_UserRoleModel();
        $this->_rolePermissionDb = new Role_RolePermissionModel();
        $this->_permissionDb = new Role_PermissionModel();
        $this->_roleDb = new Role_RoleModel();

    }

    public function checkPriv($uid, $controller, $action) {
        $roles = $this->_userRoleDb->where(array('user_id'=>$uid))->field('role_id')->select();
        //没有任何角色 直接返回失败
        if (!$roles) {
            return FALSE;
        }
        $role_id = array();
        foreach ($roles as $role) {
            $role_id[] = $role['role_id'];
        }
        $result = $this->_rolePermissionDb->join('xmcd_permission  on xmcd_permission.id = xmcd_rolepermission.permission_id ')->where(array('xmcd_rolepermission.role_id'=>array('in',$role_id)))->where(array('xmcd_permission.ctrl'=>$controller))->where(array('xmcd_permission.action'=>$action))->find();
        return !!$result;
    }

    public function getPermissionList($page,$pageSize){
    $list = $this->_permissionDb
        ->where(array('parent'=>0))
        ->limit($page,$pageSize)
        ->order('id desc')
        ->select();
    foreach ($list as $k=>$r) {
        $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);
    }
    return $list;
    }

    public function getPermissionInfo($id){
        $list = $this->_permissionDb->where(array('id'=>$id))->find();
        return $list;
    }

    public function nameExist($name){
        $list = $this->_permissionDb->where(array('name'=>$name))->find();
        return $list;
    }

    public function getPermissionInfoList($id){
        $list = $this->_permissionDb->where(array('parent'=>$id))->select();
        return $list;
    }

    public function modifyPermission($id, $data) {
        return $this->_permissionDb->where(array('id'=>$id))->save($data);
    }

    public function getPermissionCount(){
        return $this->_permissionDb->where(array('parent'=>0))->count();
    }

    public function add($addDatas) {
        if(empty($addDatas)){
            return false;
        }
        $addDatas['created'] = time();
        return $this->_permissionDb->data($addDatas)->add();
    }

    /**
     * 查询所有机构，供添加部门时选择机构
     * @return $list
     */
    public function getPermissionItem(){
        $list = $this->_permissionDb->field('id,name')->select();
        return $list;
    }

    /**
     * 根据Oids查询机构信息
     * @return $list
     */
    public function getPermissionListByOids($oids = array()){
        $list = $this->_permissionDb->where(array('id'=>array('in',$oids)))->field('id,name')->select();
        return $list;
    }

    public function get($id) {
        return $this->_permissionDb->where(array('id'=>$id))->find();
    }

    public function mod($id, $data) {
        return $this->_permissionDb->where(array('id'=>$id))->save($data);
    }

    public function del($id) {
        return $this->_permissionDb->where(array('id'=>$id))->delete();
    }

}