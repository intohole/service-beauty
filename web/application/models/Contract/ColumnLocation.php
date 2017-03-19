<?php
class Contract_ColumnLocationModel extends TK_M {
	protected $tableName = 'xmcd_contract_column_location';

	public function addInfo($data){
		if(!$data)
			return false;
		return $this->data($data)->add($data);
	}

	public function getInfo($id){
		$info = $this->where(array('id'=>$id))->find();
		return $info;
	}

	public function mod($id, $data) {
        return $this->where(array('id'=>$id))->save($data);
    }
}
