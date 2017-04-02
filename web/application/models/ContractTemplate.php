<?php

/******************************
 * @Model: ContractTemplate
 * @Desc: 合同模板模型
 * @auth: hgy
******************************/
class ContractTemplateModel extends TK_M {
    
    protected $tableName = 'xmcd_contract_template';

    public function __construct() {
        parent::__construct();
        
    }
    
    /**
     * 获取合同模板总数
     * @auth hgy
     * @return int
     */
    public function getTemplateCount($where=null){
        
        //不查询已删除的数据
        $this->where("xmcd_contract_template.status !=4");
        
        //机构ID
        if(!empty($where['oid'])){
            $this->where(array('xmcd_contract_template.oid' => $where['oid']));
        }
       
        return $this->count();
    }
    
    /**
     * 获取合同模板列表
     * @auth hgy
     * @param number $page
     * @param number $pagesize
     * @return array
     */
    public function getTemplateList($page,$pageSize,$where=null){
        $select = $this;
        
        //首期还款日期和末期还款日期直接读xmcd_repayment中的字段就不在联表查了
        $fields = "xmcd_contract_template.*, o.name as org_name ";
                
        $select->join("LEFT JOIN xmcd_org o on xmcd_contract_template.oid=o.id");
        
        //不查询已删除的数据
        $this->where("xmcd_contract_template.status !=4");
        
        //机构ID
        if(!empty($where['oid'])){
            $this->where(array('xmcd_contract_template.oid' => $where['oid']));
        }
        
        $this->field($fields);
        
        $this->limit($page,$pageSize);
        
        $list = $select->order('id desc')->select();
        
        foreach ($list as $key => $val) {
            //模板是否已制作过
            if($val['status'] == 1){
                $list[$key]['edit'] = "<a href=/contract/templateMake?id=" .$val['id']. ">编辑</a>";
            }
            else{
                $list[$key]['edit'] = "<a href=/contract/templateMake?id=" .$val['id']. ">添加</a>";
            }
            
            $list[$key]['created_date'] = date('Y-m-d H:i:s', $val['created']);
        }
        
        return $list;
    }
    
    
    /**
     * 获取指定机构中type值最大的模板
     * @auth hgy
     * @return array
     */
    public function getMaxType($oid){
        
        $res = $this->where(array('oid'=>$oid))
             ->max('type');
       
        return $res;
    }
    
    
    /**
     * 添加模板
     * @auth hgy
     * @return int
     */
    public function addData($addDatas) {
        if(empty($addDatas)){
            return false;
        }
        $addDatas['created'] = time();
        $addDatas['modified'] = date('Y-m-d H:i:s', $addDatas['created']);
        return $this->data($addDatas)->add();
    }
    
    /**
     * 删除
     */
    public function delectColumn($id){
        $res = $this->where(array('id'=>$id))->delete();
        return $res;
    }

    /**
     * 修改
     */
    public function mod($id, $data) {
        $data['modified'] = date('Y-m-d H:i:s', time());
        return $this->where(array('id'=>$id))->save($data);
    }
    
    /**
     * 查询
     */
    public function get($id) {
        return $this->where(array('id'=>$id))->find();
    }

    /**
     * 获取合同模板列表
     * @auth jw
     * @return array
     */
    public function getTemplateContractList($app_id, $oid){
        $select = $this->alias('fct');
        
        //首期还款日期和末期还款日期直接读xmcd_repayment中的字段就不在联表查了
        $fields = "fct.name, fct.type, fct.id fctid, fct.path, fc.*";
                
        $select->join("LEFT JOIN xmcd_contract fc on fct.oid=fc.oid and fct.type=fc.contract_type and fc.appid=".$app_id);
        
        //不查询已删除的数据
        $this->where("fct.status=1");

        $this->where(array('fct.oid' => $oid, 'fct.status'=>1));
        
        $this->field($fields);
        
        $list = $select->order('fct.id asc')->select();
// echo "<pre><meta charset='utf-8'>";var_dump($this->getLastSql());exit;
        
        return $list;
    }

    public function getTemplateContractCount($app_id, $oid){
        $select = $this->alias('fct');
        
        //首期还款日期和末期还款日期直接读xmcd_repayment中的字段就不在联表查了
        $fields = "fct.name, fct.type, fct.id, fct.path, fc.*";
                
        $select->join("LEFT JOIN xmcd_contract fc on fct.oid=fc.oid and fct.type=fc.contract_type and fc.appid=".$app_id);
        
        //不查询已删除的数据
        $this->where("fct.status=1");

        $this->where(array('fct.oid' => $oid));
        
        $this->field($fields);
        
        $list = $select->order('fct.id asc')->count();
        
        return $list;
    }

    public function getInfo($id, $oid, $app_id){
        $fields = "fc.appid, fct.*";
        return $this->alias('fct')
               ->join("LEFT JOIN xmcd_contract fc on fct.oid=fc.oid and fct.type=fc.contract_type")
               ->field($fields)
               ->where(array('fct.type'=>$id, 'fct.oid'=>$oid, 'fc.appid'=>$app_id))
               ->find();
    }

    public function getInfos($ids, $oid, $app_id){
        $fields = "fct.*, fc.appid, fc.path cpath";
        // $fields = "fc.appid, fct.*";
        $where['fct.type'] = array('in', $ids);
        $where['fct.oid'] = $oid;
        $where['fct.status'] = 1;
        $result = $this->alias('fct')
                    ->join("LEFT JOIN xmcd_contract fc on fct.oid=fc.oid and fct.type=fc.contract_type and fc.appid=".$app_id)
                    ->field($fields)
                    ->where($where)
                    ->select();
        return $result;
    }

    public function getTemplateTypeList($app_id, $oid){
        $select = $this->alias('fct');
        
        //首期还款日期和末期还款日期直接读xmcd_repayment中的字段就不在联表查了
        $fields = "fct.type";
                
        $select->join("LEFT JOIN xmcd_contract fc on fct.oid=fc.oid and fct.type=fc.contract_type and fc.appid=".$app_id);
        
        //不查询已删除的数据
        $this->where("fct.status=1");

        $this->where(array('fct.oid' => $oid, 'fct.status'=>1));
        
        $this->field($fields);
        
        $list = $select->select();

        foreach($list as $k=>$v){
            $data[] = $v['type'];
        }
        $result = implode(',', $data);
        return $data;
    }

    //获取机构模板对应的lids
    public function getLids($oid){
        $where['oid'] = $oid;
        $where['lid'] = array('exp', 'is not null');
        $where['status'] = 1;
        $res = $this->where($where)->select();
        return $res;
    }

    public function getlistbylid($lid){
        $where['lid'] = $lid;
        $res = $this->where($where)->select();
        return $res;
    }
   
}