<?php
class OperationModel {

    private $_operationDb;

    private $_userModel;

    public function __construct() {
        $this->_operationDb = new Admin_OperationModel();
        $this->_userModel = new AdminUserModel();
    }

    public function getOperationList($page,$pageSize){
        $list = $this->_operationDb
            ->limit($page,$pageSize)
            ->order('id desc')
            ->select();
        foreach ($list as $k=>$r) {
            $list[$k]['create_time'] = date('Y-m-d H:i:s', $r['create_time']);
            $userInfo = $this->_userModel->get($r['user_id']);
            if(!empty($userInfo)){
                $list[$k]['username'] = $userInfo['realname'];
            }else{
                $list[$k]['username'] = '';
            }
        }
        return $list;
    }

    public function getOperationInfo($oid){
        $list = $this->_operationDb->where(array('id'=>$oid))->field('id,name')->find();
        return $list;
    }

    public function getOperationCount(){
        return $this->_operationDb->count();
    }

    public function add($operationData) {
        if(empty($operationData)){
            return false;
        }
        $operationData['create_time'] = time();
        return $this->_operationDb->data($operationData)->add();
    }




}
