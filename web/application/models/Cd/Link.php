<?php

/******************************
 * @Model: Link
 * @Desc: 车贷角色关系模型
 * @Author: hgy 
 * @date:2016-03-15
******************************/
class Cd_LinkModel extends TK_M {
	
    protected $tableName = 'xmcd_cd_link';
	
    
    /**
    *
    * 递归获取指定用户的所有下属用户
    * @author hgy
    * @since 2016-03-16
    * @param int $user_id , int or array $role_id
    * @return array
    */
    public function getChilds($user_id, $role_id=null) {
        //用做存储该用户所有子节点用户的userid
        $arr_user_ids = array();
        
        $select = $this;

        $fields = "xmcd_cd_link.* ";

        if (is_array($user_id)) {
            $select->where(array('xmcd_cd_link.pid'=>array('in', $user_id)));
        } else {
            $select->where(array('xmcd_cd_link.pid'=>$user_id));
        }
        
        //每次递归根据role_id查出的都是角色小于上一次查询的用户
        if($role_id){
            $select->where(array("xmcd_cd_link.role_id"=>array('lt',$role_id)));
        }
        
        //按角色从上到下排序
        $select->order('xmcd_cd_link.role_id desc'); 

        $result = $select->field($fields)->select();

        //如果结果不为空,取出所有用户id,作为下次递归的查询条件
        if($result){
            //判断user_id是否等于pid,避免陷入死循环
            foreach($result as $k => $v){
                if(!is_array($user_id) && $v['user_id'] != $user_id){
                    $arr_user_ids[] = $v['user_id'];
                }
                if(is_array($user_id) && !in_array($v['user_id'], $user_id) ){
                    $arr_user_ids[] = $v['user_id'];
                }
            }
            
            //递归进行查询，并且只查询小于当前结果集中最大角色的数据,避免发生死循环
            if(!empty($arr_user_ids)){
                $arr = $select->getChilds($arr_user_ids,$result[0]['role_id']);
                
                //合并查询结果
                $result = array_merge($result, $arr);
            }
        }
        return $result;
    }
    
    
    /**
    *
    * 递归获取指定用户的上级用户,每次只获取一个
    * @author hgy
    * @since 2016-03-18
    * @param int $user_id 
    * @return array
    */
    public function getPids($user_id, $role_id=null, $no_role_id=0) {
        //用做存储该用户所有父节点用户
        $arr_user_ids = array();
        
        $select = $this;
        
        $fields = "xmcd_cd_link.*, u.realname, u.phone, r.name";
        
        $select->join("LEFT JOIN xmcd_users u on xmcd_cd_link.user_id=u.id")
               ->join("LEFT JOIN xmcd_role r on xmcd_cd_link.role_id=r.id");
        
        $select->where(array('xmcd_cd_link.user_id'=>$user_id));
        
        //如果存在角色id
        if($role_id)
            $select->where(array("xmcd_cd_link.role_id"=>$role_id));
        
        //这个条件是为了只查出自己上级角色,避免查出重复数据,陷入死循环，
        if($no_role_id)
            $select->where(array("xmcd_cd_link.role_id"=>array('gt',$no_role_id)));
        
        //按角色从小到大排序
        $select->order('xmcd_cd_link.role_id asc');      
        
        $row = $select->field($fields)->select();
       
        //用过用户不存在,或者用户的父ID等于自己，直接返回
        if(!$row){
            return $arr_user_ids;
        }
        else if($row[0]['pid'] == $user_id){
            //return $arr_user_ids;
        }
        
        //取出当前查询结果中最小的角色
        $arr_user_ids[] = $row[0];
      
        //递归查出所有上级
        $arr = $select->getPids($row[0]['pid'], '', $row[0]['role_id']);
       
        //合并上级用户
        if($arr){
            $arr_user_ids = array_merge($arr_user_ids, $arr);
        }
       
        return $arr_user_ids;
    }
    
    

    /**
     * 添加用户与上级
     * @param int $user_id
     * @param int $pid
     * @return int newid
     */
    public function addlink($user_id, $pid, $role_id){
        $ulink = $this->where(array('user_id'=>$user_id, 'role_id'=>$role_id))->find();

        if($ulink){
            return $this->where(array('user_id'=>$user_id, 'role_id'=>$role_id))->save(array('pid'=>$pid, 'created'=>time()));

        }else{

            $data['user_id'] = $user_id;
            $data['pid'] = $pid;
            $data['role_id'] = $role_id;
            $data['created'] = time();

            return $this->data($data)->add();
        }

    }

    /**
     * 获取user列表里每个人的直属上级
     * @param array $user_list
     * @return array $user_leader_list
     */
    public function getLeaderListByUsers($user_list){

        foreach($user_list as $key=>$value){

            $real_name = $this
                         ->join("LEFT JOIN xmcd_users u on xmcd_cd_link.pid=u.id")
                         ->field("xmcd_cd_link.*, u.realname")
                         ->where(array('user_id'=>$value['id']))
                         ->find();

            if($real_name && $real_name['realname'])
                $user_list[$key]['pname'] = $real_name['realname'];
            else
                $user_list[$key]['pname'] = '暂无';
            
        }

        return $user_list;
    }

    /**
     * 获取当前用户与上级对应列表
     */
    public function getUserLinkList($user_id,$role=null){
        $this->field("xmcd_cd_link.*, u.phone, u.realname as real_name, r.name as role_name");
        
        $this->join("LEFT JOIN xmcd_users u on xmcd_cd_link.pid=u.id")
            ->join("LEFT JOIN xmcd_role r on xmcd_cd_link.role_id=r.id");
            
        $this->where(array('user_id'=>$user_id));
        
        if(!empty($role) && is_numeric($role)){
            $this->where(array('role_id'=>$role));
        }
        
         $list = $this->select();
        foreach($list as $key=>$value){
            if($list[$key]['real_name'] == NULL){
                $list[$key]['real_name'] = '暂无';
            }
        }
        return $list;
    }

    /**
     * 查询直属上级信息 
     * @param $uid
     * @param $role_id
     * @return array
     */
    public function getLeaderInfo($user_id, $role_id){
        return $this
               ->join("LEFT JOIN xmcd_users au on xmcd_cd_link.user_id=au.id")
               ->join("LEFT JOIN xmcd_users bu on xmcd_cd_link.pid=bu.id")
               ->field("au.realname, au.phone, bu.realname leader_name, bu.phone leader_phone")
               ->where(array('xmcd_cd_link.user_id'=>$user_id, 'xmcd_cd_link.role_id'=>$role_id))
               ->find();
    }

    /**
     * 查询直属下级信息
     */
    public function getChildInfo($user_id, $role_id){
        return $this
               ->join("LEFT JOIN xmcd_users au on xmcd_cd_link.user_id=au.id")
               ->join("LEFT JOIN xmcd_users bu on xmcd_cd_link.pid=bu.id")
               ->field("au.realname, au.phone, bu.realname leader_name, bu.phone leader_phone, xmcd_cd_user.user_id")
               ->where(array('xmcd_cd_link.pid_id'=>$user_id, 'xmcd_cd_link.role_id'=>($role_id-1)))
               ->find();
    }
    
    
    /**
    *
    * 递归获取指定用户的所有上级用户 (已废弃)
    * @author hgy
    * @since 2016-03-18
    * @param int $user_id 
    * @return array
    */
    public function getPids2($user_id) {
        //用做存储该用户所有父节点用户
        $arr_user_ids = array();
        
        $select = $this;
        
        $fields = "xmcd_cd_link.*, u.realname, u.phone, ur.role_id, r.name";
        
        $select->join("LEFT JOIN xmcd_users u on xmcd_cd_link.user_id=u.id")
               ->join("LEFT JOIN xmcd_user_role ur on xmcd_cd_link.user_id=ur.user_id")
               ->join("LEFT JOIN xmcd_role r on ur.role_id=r.id");
        
        $select->where(array('xmcd_cd_link.user_id'=>$user_id));
        
        $row = $select->field($fields)->find();
        
        //用过用户不存在,或者用户的父ID等于自己，直接返回
        if(!$row){
            return $arr_user_ids;
        }
        else if($row['pid'] == $user_id){
            return $arr_user_ids;
        }
        
        $arr_user_ids[] = $row;
       
        //递归查出所有上级
        $arr = $select->getPids($row['pid']);
       
        //合并上级用户
        if($arr){
            $arr_user_ids = array_merge($arr_user_ids, $arr);
        }
       
        return $arr_user_ids;
    }
    
    
    /**
    *
    * 递归获取指定用户的所有下属用户 (已废弃)
    * @author hgy
    * @since 2016-03-16
    * @param int $user_id 
    * @return array
    */
    public function getChilds2($user_id) {
        //用做存储该用户所有子节点用户的userid
        $arr_user_ids = array();
        
        $select = $this;

        $fields = "xmcd_cd_link.* ";

        if (is_array($user_id)) {
            $select->where(array('xmcd_cd_link.pid'=>array('in', $user_id)));
        } else {
            $select->where(array('xmcd_cd_link.pid'=>$user_id));
        }

        $result = $select->field($fields)->select();

        //如果结果不为空,取出所有用户id,作为下次递归的查询条件
        if($result){
            //判断user_id是否等于pid,避免陷入死循环
            foreach($result as $k => $v){
                if(!is_array($user_id) && $v['user_id'] != $user_id){
                    $arr_user_ids[] = $v['user_id'];
                }
                if(is_array($user_id) && !in_array($v['user_id'], $user_id) ){
                    $arr_user_ids[] = $v['user_id'];
                }
            }
            
            if($no_role_id)
            $select->where(array("xmcd_cd_link.role_id"=>array('gt',$no_role_id)));
            
            //递归进行查询
            if(!empty($arr_user_ids)){
                $arr = $select->getChilds($arr_user_ids);
                
                //合并查询结果
                $result = array_merge($result, $arr);
            }
            
            //合并查询结果
            //$arr_user_ids = array_merge($arr_user_ids, $arr); 
        }

        //去掉重复的user_id
//        if($result)
//            $result = array_unique($result);

        //return $arr_user_ids;
        return $result;
    }

    /**
     * 删除对应关系
     * @author jw
     * @since 20160628
     */
    public function del_link($users, $role_id){
        if(!$users || !$role_id){
            return false;
        }
        $where['user_id'] = array('in', $users);
        $where['role_id'] = $role_id;
        $res = $this->where($where)->delete();
        return $res;
    }
	
}

