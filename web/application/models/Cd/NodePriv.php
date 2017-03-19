<?php
class Cd_NodePrivModel extends TK_M {
	protected $tableName = 'xmcd_cd_node_priv';
	
	/**
	 * 为角色设置有权限的节点
	 * @param int $role_id
	 * @param array $nodes
	 * @return boolean
	 */
	public function setRoleNodes($role_id, $nodes) {
		$this->where(array('role_id'=>$role_id))->delete();
		foreach ($nodes as $node) {
			$this->data(array(
				'role_id'=>$role_id,
				'nid'=>$node,
				'created'=>time(),
			))->add();
		}
		return TRUE;
	}
	
	/**
	 * 判断角色是否有节点的权限
	 * @param array $role_id
	 * @param int $nid
	 * @return boolean
	 */
	public function checkRoleNodePriv($roles, $nid) {
		return !!$this->where(array(
			'role_id'=>array('in', $roles),
			'nid'=>$nid
		))->find();
	}
	
	/**
	 * 判断用户是否有指定节点权限
	 * @param int $user_id
	 * @param int $node
	 * @return boolean
	 */
	public function checkUserNodePriv($user_id, $node) {
		$roleModel = new RoleModel();
		$userRoles = $roleModel->getUserRoles($user_id);
		//没有任何角色 直接返回无权限
		if (!$userRoles) {
			return FALSE;
		}
	
		$roles = [];
		foreach ($userRoles as $r) {
			$roles[] = $r['id'];
		}
		return !!$this->where(array('role_id'=>array('in', $roles), 'nid'=>$node))->find();
	}
	
	/**
	 * 获取角色所有的节点权限
	 * @param unknown $role_id
	 * @return multitype:unknown
	 */
	public function getRoleNodes($role_id) {
		$result = $this->where(array('role_id'=>$role_id))->select();
		$ret = [];
		foreach ($result as $row) {
			$ret[$row['nid']] = $row;
		}
		return $ret;
	}
	
	/**
	 * 获取所有角色的节点权限设置
	 * @param array $roles
	 * @return array
	 */
	public function getNodePrivSetting($roles) {
		$all = $this->where(array('role_id'=>array('in', $roles)))->select();
		$ret = [];
		$nids = [];
		foreach ($all as $priv) {
			if (isset($ret[$priv['role_id']])){
				$ret[$priv['role_id']][] = $priv;
			} else {
				$ret[$priv['role_id']] = [$priv];
			}
		}
	
		return $ret;
	}
}