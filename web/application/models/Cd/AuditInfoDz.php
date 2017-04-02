<?php
class Cd_AuditInfoDzModel extends TK_M {
	protected $tableName = 'xmcd_cd_audit_info_dz';
	
	/**
	 * 添加记录
	 * @param int $data
	 * @return int
	 */
	public function addInfo($data) {
		return $this->data($data)->add();
	}
	
	/**
	 * 获取一个申请单的所有审核记录
	 * @param int $appid
	 * @return array
	 */
	public function getAppInfo($appid) {
		$result = $this->where(array('app_id'=>$appid))->order('created desc ')->select();
		return $result;
	}

	/**
	 * 获取申请单所有审核记录
	 * @param int $id
	 * @return array
	 */
	public function getUserAudits($id) {
            $fields = "a.id as aid, a.flow, a.result, a.comment, a.agree_deadline,"
                    . " a.agree_amount, a.agree_rate, fu.realname,a.created as auditcreated ";
            
            $this->alias('a');
            
            $select = $this->join("LEFT JOIN xmcd_users fu on a.user_id=fu.id");
            
            $this->field($fields);
            
            $select = $this->where(array('app_id'=>$id));
            
            $result = $select->order('aid asc')->select();
            
            return $result;
	}

	/**
	 * 获取最后一次添加的法律审核结果信息
	 */
	public function getAppLegalInfo($app_id, $user_id){
		// $where = 'app_id='.$app_id;
		// $where .= ' AND legal_id IS NOT NULL';
		$where['app_id'] = $app_id;
		$where['flow'] = 4;
		$legal_id = $this->field("legal_id")->where($where)->order("id desc")->limit(1)->find();
// echo "<pre><meta charset='utf-8'>";var_dump($legal_id);exit;

		$orwhere['app_id'] = $app_id;
		$orwhere['flow'] = 6;
		$orwhere['user_id'] = $user_id;
		$new_legal_id = $this->field("legal_id")->where($orwhere)->order('id desc')->limit(1)->find();
		$legalModel = new Cd_AppLegalAuditModel();
		$legal_info = $legalModel->getInfo($legal_id['legal_id']);
		$legal_info['leader'] = $legalModel->getInfo($new_legal_id['legal_id']);
		return $legal_info;
	}

	//获取操作者
	public function getUser($app_id, $flow){
		$field = "user_id";
		$where['app_id'] = $app_id;
		$where['flow'] = $flow;
		return $this->field($field)->where($where)->order("id desc")->limit(1)->find();
	}
}