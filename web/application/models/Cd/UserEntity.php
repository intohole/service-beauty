<?php

/******************************
 * @Model: Link
 * @Desc: 车贷角色关系模型
 * @Author: hgy 
 * @date:2016-03-15
******************************/
class Cd_UserEntityModel extends TK_M {
	
    protected $tableName = 'xmcd_cd_user_entity';
	
    //合同用方法  查询用户信息
    public function getUserInfo($data){
        $info = $this->where(array('value_id'=>$data['value_id'], 'field_name'=>$data['field_name']))->find();
// echo "<pre><meta charset='utf-8'>";var_dump($this->getLastSql());exit;
        return $info;
    }
	
}

