<?php

/******************************
 * @Model: Link
 * @Desc: 车贷角色关系模型
 * @Author: hgy 
 * @date:2016-03-15
******************************/
class Cd_MenuLinkModel extends TK_M {
	
    protected $tableName = 'xmcd_cd_menu_link';

    /**
     * 添加用户与上级
     * @param int $user_id
     * @param int $pid
     * @return int newid
     */
    public function addMenuLink($menu_id, $pid){
        $ulink = $this->where(array('menu_id'=>$menu_id))->find();
        if($ulink){
            return $this->where(array('menu_id'=>$menu_id))->save(array('pid'=>$pid));
        }else{
            $data['menu_id'] = $menu_id;
            $data['pid'] = $pid;
            return $this->data($data)->add();
        }
    }

    //获取当前节点的上级
    public function getPid($menu_id){
        return $this->field('pid')->where(array('menu_id'=>$menu_id))->find();
    }

    /**
     * 获取当前角色有哪些节点
     * @param $role_id
     * @return array flows
     */
    public function getFlows($role_id){
        $flows = [];
        $flow_str = $this->field("flow")->where(array("role_id"=>$role_id))->find();
        if(strstr($flow_str, ',')){
            $flows = explode(',', $flow_str);
        }else{
            $flows[] = $flow_str;
        }
        return $flows;
    }

    /**
     * 获取当前节点对应的角色
     * @param $flow
     * @return int role_id
     */
    public function getRoleId($flow){
        $where = $flow." in (flow)";
        return $this->field('role_id')->where($where)->find();
    }
	
}

