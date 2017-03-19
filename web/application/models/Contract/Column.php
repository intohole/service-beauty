<?php
class Contract_ColumnModel extends TK_M {
	protected $tableName = 'xmcd_contract_column';

	public function getInfo($id){
		return $this->alias('fcc')
			   ->join("LEFT JOIN xmcd_contract_column_location fccl on fcc.location_id=fccl.id")
			   ->field("fcc.item_type,fccl.*")
			   ->where(array('fcc.id'=>$id))
			   ->find();
	}

}
