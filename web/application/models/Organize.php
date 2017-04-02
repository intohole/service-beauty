<?php

class OrganizeModel {

    private $_userRoleDb;
    private $_rolePermissionDb;
    private $_permissionDb;
    private $_roleDb;
    private $_organizeDb;
    private $_organizeFlowDb;
    private $_organizeFlowCdDb;
    

    public function __construct() {
        $this->_userRoleDb = new Role_UserRoleModel();
        $this->_rolePermissionDb = new Role_RolePermissionModel();
        $this->_permissionDb = new Role_PermissionModel();
        $this->_roleDb = new Role_RoleModel();
        $this->_organizeDb = new Role_OrganizeModel();
        $this->_organizeFlowDb = new OrganizeFlowModel();
        $this->_organizeFlowCdDb = new OrganizeFlowCdModel();
    }

    public function checkPriv($uid, $controller, $action) {
        $roles = $this->_userRoleDb->where(array('user_id' => $uid))->field('role_id')->select();
        //没有任何角色 直接返回失败
        if (!$roles) {
            return FALSE;
        }
        $role_id = array();
        foreach ($roles as $role) {
            $role_id[] = $role['role_id'];
        }
        $result = $this->_rolePermissionDb->join('xmcd_permission  on xmcd_permission.id = xmcd_rolepermission.permission_id ')->where(array('xmcd_rolepermission.role_id' => array('in', $role_id)))->where(array('xmcd_permission.ctrl' => $controller))->where(array('xmcd_permission.action' => $action))->find();
        return !!$result;
    }

    public function getOrganizeList($where,$page, $pageSize) {
		$selects = $this->_organizeDb;
        $selects->where(array('type'=>array('in', array(1,3))));
        if($where!=''){
            $list = $selects
                ->where($where)
                ->limit($page, $pageSize)
                ->order('id desc')
                ->select();
        }else{
            $list = $selects
                ->limit($page, $pageSize)
                ->order('id desc')
                ->select();
        }
		$afm = new AreaFlowModel();
        foreach ($list as $k => $r) {
            $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);
			switch($r['insti_attr']){
				case 1:
					$list[$k]['insti_name'] = '一般';
					break;
				case 2:
					$list[$k]['insti_name'] = '深度(初审下放)';
					break;
				case 3:
					$list[$k]['insti_name'] = '分公司';
					break;
				case 4:
					$list[$k]['insti_name'] = '总公司';
					break;
				case 5:
					$list[$k]['insti_name'] = '未分类';
					break;
				/* case 6:
					$list[$k]['insti_name'] = '个人';
					break;
				case 7:
					$list[$k]['insti_name'] = '深度合作(总公司初审)';
					break; */
				case 8:
					$list[$k]['insti_name'] = '深度';
					break;
				default:
					$list[$k]['insti_name'] = '';
					break;
			}
            //查询所属地区
            $info = $afm->get($r['area_id']);
            if(!empty($info)){
                $list[$k]['area_name'] = $info['area_name'];
                unset($info);
            }else{
                $list[$k]['area_name'] = '';
            }
        }
        return $list;
    }
    
    
    /**
    * 获取车贷机构列表 
    * @auth hgy
    * @date 2016-08-02
    * @return array
    */
    public function getOrganizeCdList($where,$page, $pageSize) {
        
        $fields = "xmcd_org.*, oa.name as attr_name, oar.name as area_name ";
        
        $this->_organizeDb->join("LEFT JOIN xmcd_org_attr oa on xmcd_org.cdinsti_attr=oa.id")
                          ->join("LEFT JOIN xmcd_org_area oar on xmcd_org.cdarea_id=oar.id");
                
        $this->_organizeDb->field($fields);
        
        if($where!=''){
            $this->_organizeDb->where($where);
        }
        
        $list = $this->_organizeDb->limit($page, $pageSize)->order('id desc')->select();
        
        //$afm = new AreaFlowModel();
        foreach ($list as $k => $r) {
            $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);        
        }
        
        return $list;
    }
    

    public function getOrganizeInfo($oid) {
        $list = $this->_organizeDb->where(array('id' => $oid))->field('id,insti_attr,name,own_clerk')->find();
        return $list;
    }

    public function infoExist($name) {
        $list = $this->_organizeDb->where(array('name' => $name))->field('id,name')->find();
        return $list;
    }

    public function getOrganizeCount($where) {
        $countNum = $this->_organizeDb->where(array('type'=>array('in', array(1,3))));
        
        if($where!=''){
            return $countNum->where($where)->count();
        }else{
            return $countNum->count();
        }

    }

    public function add($addDatas) {
        if (empty($addDatas)) {
            return false;
        }
        $addDatas['created'] = time();
        return $this->_organizeDb->data($addDatas)->add();
    }

    /**
     * 查询所有机构，供添加部门时选择机构
     * @return $list
     */
    public function getOrganizeItem() {
        $list = $this->_organizeDb->field('id,name')->select();
        return $list;
    }
    //根据条件搜索机构
    public function getSearchorg($key) {
        $list = $this->_organizeDb->field('id,name')->where(array('name'=>array('like','%'.$key.'%')))->select();
        return $list;
    }

    /**
     * 根据Oids查询机构信息
     * @return $list
     */
    public function getOrganizeListByOids($oids = array()) {
        $list = $this->_organizeDb->where(array('id' => array('in', $oids)))->field('id,name')->select();
        return $list;
    }
    
    
    /**
    * 获取机构权限列表总数
    * @author hgy
    * @since 2016-05-05
    * @return int
    */
    public function getOrgFlowCount($where) {
		$countNum = $this->_organizeDb->where(array('type'=>array('in', array(1,3))));
        if($where!=''){
            if($where['xmcd_org.name']!=''){
                return 1;
            }elseif($where['xmcd_org.insti_attr']!= ''){
                return $countNum->where(array('insti_attr'=>$where['xmcd_org.insti_attr']))->count();
            }else{
                return $countNum->count();
            }

//            $here['id'] = $oid['oid'];
//            return $this->_organizeFlowDb->where($here)->count();
        }else{
            return $countNum->count();
        }

    }
    
    /**
    * 获取机构权限列表
    * @author hgy
    * @since 2016-05-05
    * @param int $page , int $pageSize
    * @return array
    */
    public function getOrgFlowList($where,$page, $pageSize) {
		$select = $this->_organizeDb;
        $select->where(array('type'=>array('in', array(1,3))));
        if($where!=''){
            //$select = $this->_organizeDb;

            $fields = "xmcd_org.id, xmcd_org.name, xmcd_org.sorted, of.* ";

            $select->join("LEFT JOIN xmcd_org_flow of on xmcd_org.id=of.oid");

            $select->field($fields);

            $select->where($where);

            $select->limit($page, $pageSize)->order('sorted desc,id desc');

            $list = $select->select();
        }else{
            //$select = $this->_organizeDb;

            $fields = "xmcd_org.id, xmcd_org.name, xmcd_org.sorted, of.* ";

            $select->join("LEFT JOIN xmcd_org_flow of on xmcd_org.id=of.oid");

            $select->field($fields);

            $select->limit($page, $pageSize)->order('sorted desc,id desc');

            $list = $select->select();
        }

           
        foreach ($list as $k => $r) {
            $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);
        }
        return $list;
    }
    
    /**
    * 获取所有机构
    * @author hgy
    * @since 2016-05-05
    * @return array
    */
    public function getAllOrgs() {
        $select = $this->_organizeDb;
        
        $fields = "xmcd_org.id, xmcd_org.name,xmcd_org.insti_attr";
        
        $select->field($fields);
        
        $select->order('id asc');
        
        $list = $select->select();
        
        return $list;
    }
    
    /**
    * 查询机构配置详情 (房贷)
    * @author hgy
    * @since 2016-05-05
    * @param int $oid 
    * @return array
    */
    public function getOrgFlowInfo($oid) {        
        $select = $this->_organizeDb;
        
        $fields = "xmcd_org.id, xmcd_org.name, of.* ";
        
        $select->join("LEFT JOIN xmcd_org_flow of on xmcd_org.id=of.oid");
                
        $select->field($fields);
        
        $select->where(array('xmcd_org.id' => $oid));
        
        $info = $select->find();
        
        return $info;        
    }
    
    /**
    * 查询机构配置详情 (车贷)
    * @author hgy
    * @since 2016-08-04
    * @param int $oid 
    * @return array
    */
    public function getOrgFlowCdInfo($oid) {        
        $select = $this->_organizeDb;
        
        $fields = "xmcd_org.id, xmcd_org.name, of.* ";
        
        $select->join("LEFT JOIN xmcd_org_cdflow of on xmcd_org.id=of.oid");
                
        $select->field($fields);
        
        $select->where(array('xmcd_org.id' => $oid));
        
        $info = $select->find();
        
        return $info;        
    }
    
    /**
    * 根据节点与用户所在机构查询可审核机构 (房贷)
    * @author hgy
    * @since 2016-05-09
    * @param int $oid , string $node_ename
    * @return array
    */
    public function getAuthorityList($oid,$node_ename) {
        //查询字段是否存在于表中
        $sql = "desc `xmcd_org_flow` `".$node_ename."`";
        $is_exist = $this->_organizeFlowDb->query($sql);
        if(empty($is_exist)){
            return false;
        }
        
        
        $select = $this->_organizeFlowDb;
        
        $fields = "xmcd_org_flow.* ";
        
        //$where =  " 1 in (node_check_repeat) ";
        //查询字段名$node_ename中存在变量$oid的所有记录
        $where =  " find_in_set(" .$oid . "," . $node_ename . ") ";

        $select->field($fields);
        
        $select->order('oid asc');

        $select->where($where);

        $list = $select->select();

        //仅返回Oid列表即可
        $oids = null;
        foreach ($list as $k => $v){
            $oids[] = $v['oid'];
        }
       
        return $oids;
        
    }

    /**
    * 根据节点与用户所在机构查询可审核机构 (车贷)
    * @author hgy
    * @since 2016-05-09
    * @param int $oid , string $node_ename
    * @return array
    */
    public function getAuthorityCdList($oid,$node_ename) {
        //查询字段是否存在于表中
        $sql = "desc `xmcd_org_cdflow` `".$node_ename."`";
        $is_exist = $this->_organizeFlowCdDb->query($sql);
        if(empty($is_exist)){
            return false;
        }
        
        
        $select = $this->_organizeFlowCdDb;
        
        $fields = "xmcd_org_cdflow.* ";
        
        //$where =  " 1 in (node_check_repeat) ";
        //查询字段名$node_ename中存在变量$oid的所有记录
        $where =  " find_in_set(" .$oid . "," . $node_ename . ") ";

        $select->field($fields);
        
        $select->order('oid asc');

        $select->where($where);

        $list = $select->select();

        //仅返回Oid列表即可
        $oids = null;
        foreach ($list as $k => $v){
            $oids[] = $v['oid'];
        }
       
        return $oids;
        
    }
    

    /**
     * 根据节点与用户所在机构查询可审核机构
     * @author hgy
     * @since 2016-05-09
     * @param int $oid , string $node_ename
     * @return array
     */
    public function getSmsAuthorityList($oid,$node_ename) {
        //查询字段是否存在于表中
        $sql = "desc `xmcd_org_flow` `".$node_ename."`";
        $is_exist = $this->_organizeFlowDb->query($sql);
        if(empty($is_exist)){
            return false;
        }


        $select = $this->_organizeFlowDb;

        $fields = "xmcd_org_flow.{$node_ename} ";

        //查询对应机构的机构权限列表
        //$where =  " find_in_set(" .$oid . "," . $node_ename . ") ";
        $select->where(array('oid'=>$oid));

        $select->field($fields);

        $select->order('oid asc');

        $list = $select->select();
        $oids = array();
        if(!empty($list[0])){
            $oids = explode(',',$list[0][$node_ename]);
        }
        return $oids;

    }
	
	//通过机构
    public function getZkAuthorityList($oid,$node_ename) {
        //查询字段是否存在于表中
        $sql = "desc `xmcd_org_flow` `".$node_ename."`";
        $is_exist = $this->_organizeFlowDb->query($sql);
        if(empty($is_exist)){
            return false;
        }


        $select = $this->_organizeFlowDb;

        $fields = "xmcd_org_flow.{$node_ename} ";

        //查询对应机构的机构权限列表
        //$where =  " find_in_set(" .$oid . "," . $node_ename . ") ";
        $select->where(array('oid'=>array('in',$oid)));

        $select->field($fields);

        $select->order('oid asc');

        $list = $select->select();
		
        $oids = array();
		$oida = array();
		
        if(!empty($list)){
			foreach($list as $key=>$li){
				$oids[$key] = explode(',',$li[$node_ename]);
				$oida = array_merge($oids[$key],$oida);
			}
			return array_unique($oida);
		}
		
        return $oids;

    }

    public function get($id) {
        return $this->_organizeDb->where(array('id' => $id))->find();
    }

    public function mod($id, $data) {
        return $this->_organizeDb->where(array('id' => $id))->save($data);
    }

    public function del($id) {
        return $this->_organizeDb->where(array('id' => $id))->delete();
    }
	public function getname($id){
        return $this->_organizeDb->field('name')->where(array('id' => $id))->find();
    }
	//获取所有机构(脚本所用方法)
	public function getAllOrganizeItem() {
        $list = $this->_organizeDb->select();
        return $list;
    }
	
	//判断该机构是否属于北京地区
	public function checkOrgan($oid){
		$org_mess = $this->_organizeDb->where(array('id'=>$oid))->find();
		if($org_mess){
			if($org_mess['area_id'] == 1){
				//北京地区的报单
				return false;				
			}else{
				//不是北京地区的报单
				return true;
			}
		}else{
			return true;
		}
	}
    public function getidByname($name){
        $n = $this->_organizeDb->field('id')->where(array('name'=>$name))->find();
        $oid =  $n['id'];
        return $oid;
    }
    //根据area_ids获取机构列表
    public function getOidsByAreaIds($areaIds = array()){
        $lists = $this->_organizeDb->where(array('area_id'=>array('in',$areaIds)))->field('id')->select();
        //仅返回oids
        $oids = null;
        foreach ($lists as $k => $v){
            $oids[] = $v['id'];
        }
        return $oids;
    }
    public function getorgArea($org_id){
        return  $this->_organizeDb->field('area_id')->where(array('id'=>$org_id))->find();
    }

    //根据Oid获取所属地区
    public function getAreaIdByOid($oid){
        $info = $this->_organizeDb->where(array('id'=>$oid))->field('area_id')->select();
        return $info[0]['area_id'];
    }

    
    
    
    /**
    * 获取车贷机构权限列表总数
    * @author hgy
    * @since 2016-08-03
    * @return int
    */
    public function getOrgFlowCdCount($where) {
        
        $this->_organizeDb->join("LEFT JOIN xmcd_org_cdflow of on xmcd_org.id=of.oid");
        
        $this->_organizeDb->where(array('type'=>array('in', array(2,3))));
        
        return $this->_organizeDb->count();
        
    }
    
    /**
    * 获取车贷机构权限列表
    * @author hgy
    * @since 2016-08-03
    * @param int $page , int $pageSize
    * @return array
    */
    public function getOrgFlowCdList($where,$page, $pageSize) {
        $select = $this->_organizeDb;
        
        //只查出车贷机构与银巴克机构
        $select->where(array('type'=>array('in', array(2,3))));
        
        $fields = "xmcd_org.id, xmcd_org.name, of.*, oar.name as attr_name, oaa.name as area_name ";

        $select->join("LEFT JOIN xmcd_org_cdflow of on xmcd_org.id=of.oid")
                ->join("LEFT JOIN xmcd_org_area oaa on xmcd_org.cdarea_id=oaa.id")
                ->join("LEFT JOIN xmcd_org_attr oar on xmcd_org.cdinsti_attr=oar.id");

        $select->field($fields);

        if($where!=''){
            $select->where($where);
        }

        $select->limit($page, $pageSize)->order('id desc');

        $list = $select->select();
       
        foreach ($list as $k => $r) {
            $list[$k]['created'] = date('Y-m-d H:i:s', $r['created']);
        }
        return $list;
        
    }
    
    
    /**
    * 获取所有车贷机构
    * @author hgy
    * @since 2016-05-05
    * @return array
    */
    public function getAllCdOrgs($where) {
        $select = $this->_organizeDb;
        
        $fields = "xmcd_org.id, xmcd_org.name, xmcd_org.cdinsti_attr, xmcd_org.cdarea_id";
        
        $select->where(array('type'=>array('in', array(2,3))));
        
        //根据机构名模糊搜索
        if(!empty($where['orgname'])){
            $select->where(array('name'=>array("like",'%'.$where['orgname'].'%')));
        }
        
        //查出已配置的机构
        if(!empty($where['ids'])){
            $select->where(array('xmcd_org.id'=>array('in', $where['ids'])));
        }
        
        $select->field($fields);
        
        $select->order('id asc');
        
        $list = $select->select();
        
        return $list;
    }
    

    //模糊搜索机构名称
    public function getOrgName($key){
            if(!$key){
                    return "";
            }
            $info = $this->_organizeDb->where(array('name'=>array("like",'%'.$key.'%')))->field('id,name')->select();
    return $info;
    }
	
	//数组搜索
	public function getArrOrgName($id){
		if(!$id){
			return "";	
		}
		$info = $this->_organizeDb->where(array('id'=>array("in",$id)))->field('id,name')->select();
        return $info;
    }

    //查询车贷机构
    public function getCdOrgs(){
        $field = "id, name";
        $res = $this->_organizeDb->field($field)->where(array('type'=>array('in', array(2,3))))->select();
        return $res;
    }
	
	//获取指定name
	public function getOrgNames(){
		$info = $this->_organizeDb->where(array('sorted'=>array("eq",2)))->field('id,name')->select();
        return $info;
	}
	

}
