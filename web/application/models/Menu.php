<?php
class MenuModel {

    private $_menuDb;


    public function __construct() {
        $this->_menuDb = new Role_MenuModel();
    }

    /**
     * 取出所有的一级菜单
     * @param $page
     * @param $pageSize
     * @return $lsit
     */
    public function getParentMenuList($page,$pageSize){
    $list = $this->_menuDb
        ->where(array('parent'=>0))
        ->limit($page,$pageSize)
        ->order('id desc')
        ->select();
    foreach ($list as $k=>$r) {
        $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);
    }
    return $list;
    }

    public function getParentMenuCount(){
        return $this->_menuDb->where(array('parent'=>0))->count();
    }

    /**
     * 取出所有的二级菜单
     * @param $page
     * @param $pageSize
     * @return $lsit
     */
    public function getMenuList($page,$pageSize,$menuId){
        if(!$menuId){
            return false;
        }
        $list = $this->_menuDb
            ->where(array('parent'=>$menuId))
            ->limit($page,$pageSize)
            ->order('id desc')
            ->select();
        foreach ($list as $k=>$r) {
            $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);
        }
        return $list;
    }

    public function getMenuCount($menuId){
        if(!$menuId){
            return false;
        }
        return $this->_menuDb->where(array('parent'=>$menuId))->count();
    }

    public function getParentMenuInfo($mid){
        $list = $this->_menuDb->where(array('id'=>$mid))->field('id,name')->find();
        return $list;
    }

    public function add($addDatas) {
        if(empty($addDatas)){
            return false;
        }
        $addDatas['created'] = time();
        return $this->_menuDb->data($addDatas)->add();
    }

    public function parentNameExist($name) {
        return $this->_menuDb->where(array('name'=>$name,'parent'=>0))->find();
    }

    public function nameExist($name) {
        return $this->_menuDb->where(array('name'=>$name))->where('parent > 0')->find();

    }

    /**
     * 查询所有菜单列表
     * @return $list
     */
    public function getMenuItem(){
        $list = $this->_menuDb->field('id,name')->select();
        return $list;
    }


    public function get($id) {
        return $this->_menuDb->where(array('id'=>$id))->find();
    }

    public function mod($id, $data) {
        return $this->_menuDb->where(array('id'=>$id))->save($data);
    }

    public function delParentMenu($id) {
        return  $this->_menuDb->where("id=%d or parent=%d",array($id,$id))->delete();
    }

    public function del($id) {
        return $this->_menuDb->where(array('id'=>$id))->delete();
    }

    //查询所有的二级目录
    public function getChildList(){
        return $this->_menuDb->field("id,name")->where('parent > 0')->select();
    }

}