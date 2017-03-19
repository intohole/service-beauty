<?php
class Cd_AuditInfoModel extends TK_M {
	protected $tableName = 'xmcd_cd_audit_info';
	
	/**
	 * 审核时添加审核记录
	 * @param int $appid 申请单id
	 * @param int $user 审核人员id
	 * @param int $flow 所处流程
	 * @param int $result 审核结果
	 * @param string $comment 审核意见
	 * @return int
	 */
	public function addInfo($appid, $user, $flow, $result, $comment='', $more_data) {
		return $this->data(array(
			'app_id'=>$appid,
			'user_id'=>$user,
			'flow'=>$flow,
			'result'=>$result,
			'comment'=>$comment,
			'agree_deadline'=>$more_data['agree_deadline'],
			'agree_amount'=>$more_data['agree_amount'],
			'agree_rate'=>$more_data['agree_rate'],
			'legal_id'=>$more_data['legal_id'],
			'created'=>time(),
		))->add();
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