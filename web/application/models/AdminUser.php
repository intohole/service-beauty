<?php
class AdminUserModel {

    private $_adminUserDb;

    public function __construct() {
        $this->_adminUserDb = new Admin_UserModel();
    }


    public function findUser($userName) {
        if(!$userName){
            return false;
        }
        $userInfo = $this->_adminUserDb->where(array('phone'=>$userName))->field('id,phone,passwd,salt,realname,status,oid,openid,login_status')->find();
        return $userInfo;
    }
	
	public function findUserByName($userName,$type="") {
        if(!$userName){
            return false;
        }
		if($type=="zk"){
			$where['realname']=array("like",'%'.$userName.'%');
			$userInfo = $this->_adminUserDb->where($where)->field('id,phone,passwd,salt,realname,status,oid')->select();	
		}else{
			$userInfo = $this->_adminUserDb->where(array('realname'=>$userName))->field('id,phone,passwd,salt,realname,status')->find();
        }
        return $userInfo;
    }

    public function getUserInfoByUid($userId) {
        if(!$userId){
            return false;
        }
        $userInfo = $this->_adminUserDb->where(array('id'=>$userId))->field('id,phone,passwd,salt,realname,status,own_clerk,recard_bank,recard_name,recard_num,openid,wx_avatar')->find();
        return $userInfo;
    }

    public function nameExist($phone) {
        if(!$phone){
            return false;
        }
        $userInfo = $this->_adminUserDb->where(array('phone'=>$phone))->field('id,phone,passwd,salt,realname,status')->find();
        return $userInfo;
    }

    public function getUserList($page,$pageSize,$where){
		if($where){
			$list = $this->_adminUserDb
            ->field('id,phone,realname,oid,did,created,modified,status')
			->where($where)
            ->limit($page,$pageSize)
            ->order('id desc')
            ->select();
		}else{
			$list = $this->_adminUserDb
            ->field('id,phone,realname,oid,did,created,modified,status')
            ->limit($page,$pageSize)
            ->order('id desc')
            ->select();
		}
        
        return $list;
    }
    
    
    public function add($addDatas) {
        if(empty($addDatas)){
            return false;
        }
        $rand = md5(time() . mt_rand(0,1000));
        $addDatas['salt']= substr($rand, 5,7);
        $addDatas['passwd'] = md5(md5($addDatas['passwd']).$addDatas['salt']);
        $addDatas['status'] = 2;//新注册用户锁定，需第一次登录后修改密码
        $addDatas['created'] = time();
        return $this->_adminUserDb->data($addDatas)->add();
    }

    /**
     * 根据Dids查询机构信息
     * @return $list
     */
    public function getUserListByDids($dids = array()){
        $list = $this->_adminUserDb->where(array('id'=>array('in',$dids)))->field('id,phone,realname')->select();
        return $list;
    }


    public function changePwd($userId,$pwd){
        if(!$pwd || !$userId) return false;
        $data = array();
        $rand = md5(time() . mt_rand(0,1000));
        $data['salt']= substr($rand, 5,7);
        $data['passwd'] = md5(md5($pwd).$data['salt']);
        $ret = $this->_adminUserDb
            ->where(array('id'=>$userId))
            ->save($data);
        return $ret;
    }
    
    public function get($id) {
        return $this->_adminUserDb->where(array('id'=>$id))->field('id,phone,realname,oid,did,status,created,modified,own_clerk,recard_bank,recard_name,recard_num,openid,login_status')->find();
    }

    public function mod($id, $data) {
        return $this->_adminUserDb->where(array('id'=>$id))->save($data);
    }

    public function del($id) {
        return $this->_adminUserDb->where(array('id'=>$id))->delete();
    }
    
    public function getInfoByPhone($phone) {
        return $this->_adminUserDb->where(array('phone'=>$phone))->field('id,phone,realname,oid,did,status,created,modified,own_clerk,recard_bank,recard_name,recard_num')->find();
    }
	
    //清空不是个人机构属性的用户四个字段
    public function changFour($id){
            $data['own_clerk'] = '';
            $data['recard_bank'] = '';
            $data['recard_name'] = '';
            $data['recard_num'] = '';
    return $this->_adminUserDb->where(array('id'=>$id))->save($data);
    }
	
    //查询对应人员
    public function selAllRev($review_ids){
            return $this->_adminUserDb->where(array('id'=>array('in',$review_ids)))->field('id,phone,realname,oid,did,status,created,modified')->select();
    }
	
	
    /**
     * 根据uids查询用户列表
     * @return $list
     */
    public function getUserListByUids($uids = array()){
        $list = $this->_adminUserDb->where(array('id'=>array('in',$uids)))->field('id,phone,realname')->select();
        return $list;
    }

}
