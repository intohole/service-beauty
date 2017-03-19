<?php

/******************************
 * @Model: ContractReal
 * @Desc: 真实合同模型
 * @auth: hgy
******************************/
class ContractRealModel extends TK_M {
    
    protected $tableName = 'xmcd_contract';

    public function __construct() {
        parent::__construct();
        
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
     * 查询出指定机构中已替换完成待打印的合同
     */
    public function getWaitPrints($appid,$oid) {
        if(empty($appid) || empty($oid)){
            return false;
        }
        
        $select = $this;
                
        $fields = "xmcd_contract.* ";

        $select->where(array('xmcd_contract.appid'=>$appid));
        $select->where(array('xmcd_contract.oid'=>$oid));
        $select->where(array('xmcd_contract.status'=>1));

        $this->field($fields);

        return $select->order('id asc')->select();
    }
    
    
    public function deleteContract($ids, $app_id){
        $where['appid'] = $app_id;
        $where['contract_type'] = array('in', $ids);
        $res = $this->where($where)->delete();
// echo "<pre><meta charset='utf-8'>";var_dump($this->getLastSql());exit;
        return $res;
    }

    public function deleteContractByAppId($id){
        $where['appid'] = $id;
        $res = $this->where($where)->delete();
        return $res;
    }

    public function getInfo($ids, $app_id, $oid){
        $where['contract_type'] = array('in', $ids);
        $where['appid'] = $app_id;
        $where['oid'] = $oid;
        return $this->where($where)->count();
    }

    public function getByAppId($id){
        return $this->where(array('appid'=>$id))->find();
    }
   
}