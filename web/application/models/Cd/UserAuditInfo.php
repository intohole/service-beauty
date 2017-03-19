<?php
class Cd_UserAuditInfoModel extends TK_M {
    protected $tableName = 'xmcd_cd_user_audit_info';

    
    //删除
    public function del($id) {
        return $this->where(array('id'=>$id))->delete();
    }
    
    //根据订单ID进行删除
    public function delByAid($aid) {
        return $this->where(array('app_id'=>$aid))->delete();
    }

    /**
     * 车贷全国:
     * 添加用户审核记录,仅添加不存在记录，用于做用户审核的列表
     * @auth hgy
     * @date 2016-07-06
     * @param array $data
     * @return boolean
     */
    public function addInfoCdnation($data) {
        //查询是否在同一节点同一表单添加过记录
        $info = $this->where(array(
                'app_id'=>$data['app_id'], 
                'user_id'=>$data['user_id_cdnation'], 
                'flow'=>$data['flow'],
                'fid'=>$data['fid']
        ))->find();

        //如果有过记录直接返回
        if ($info) {
            return 0;
        }

        $insertId = $this->data(array(
                'app_id'=>$data['app_id'],
                'user_id'=>$data['user_id_cdnation'],
                'flow'=>$data['flow'],
                'fid'=>$data['fid'],
                'created'=>time()
        ))->add();

        return $insertId;
    }


    /**
     * 添加用户审核记录
     * 仅添加不存在记录，用于做用户审核的列表
     * @param int $appid
     * @param int $user
     * @param int $flow
     * @return boolean
     */
    public function addInfo($appid, $user, $flow) {
            $info = $this->where(array(
                    'app_id'=>$appid, 
                    'user_id'=>$user, 
                    'flow'=>$flow
            ))->find();
            if ($info) {
                    return 0;
            }

            return !!$this->data(array(
                    'app_id'=>$appid,
                    'user_id'=>$user,
                    'flow'=>$flow,
                    'created'=>time()
            ))->add();
    }

    /**
     * 获取用户在某个节点的审核记录数量
     * @param int $user
     * @param int $flow
     * @return int
     */
    public function getUserAuditCnt($user, $flow) {
            return $this->where(array(
                    'user_id'=>$user, 
                    'flow'=>$flow
            ))->count();
    }
    
    
    /**
     * 获取订单在某个节点的审核数量
     * @auth hgy
     * @param int $aid
     * @param int $flow
     * @param int $fid
     * @return int
     */
    public function getAuditByFlow($aid, $nid, $fid) {
        if(!empty($aid)){
            $this->where(array('app_id'=>$aid));
        }
        
        if(!empty($nid)){
            $this->where(array('flow'=>$nid));
        }
        
        if(!empty($fid)){
            $this->where(array('fid'=>$fid));
        }
        
        return $this->count();
    }

    /**
     * 获取用户在某个节点的审核记录
     * @param int $user
     * @param int $flow
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getUserAuditList($user, $flow, $page=1, $pagesize=20) {
            return $this->where(array(
                    'user_id'=>$user,
                    'flow'=>$flow
            ))->order('id desc')->page($page, $pagesize)->select();
    }


    /**
     * 获取申请单所有审核记录
     * @param int $id
     * @return array
     */
    public function getUserAudits($id) {
        $fields = "xmcd_cd_user_audit_info.id as aid, fu.realname,xmcd_cd_user_audit_info.created as auditcreated";

        $select = $this->join("LEFT JOIN xmcd_users fu on xmcd_cd_user_audit_info.user_id=fu.id");

        $this->field($fields);

        $select = $this->where(array('app_id'=>$id));

        $result = $select->order('aid desc')->select();

        return $result;
    }
    
}