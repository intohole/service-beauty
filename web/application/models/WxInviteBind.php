<?php
class WxInviteBindModel extends TK_M {
	protected $tableName = 'fk_report_wx_invite_bind';
	
	public function setBind($openid, $code) {
		if ($this->where(array('openid'=>$openid))->find()) {
			return FALSE;
		}
		
		return !!$this->data(array(
			'openid'=>$openid,
			'code'=>$code,
			'created'=>time()
		))->add();
	}
	
	public function getCode($openid) {
		$row = $this->where(array('openid'=>$openid))->find();
		if ($row) {
			return $row['code'];
		} else {
			return FALSE;
		}
	}

    public function getLocation($openid) {
        $row = $this->where(array('openid'=>$openid))->find();
        if ($row) {
            return $row['location'];
        } else {
            return FALSE;
        }
    }

    public function getInfo($openid) {
        return $this->where(array('openid'=>$openid))->find();
    }


    public function addData($data){
        if(empty($data)){
            return false;
        }
        $data['created'] = time();
        return $this->data($data)->add();
    }


    public function modData($openid, $data) {
        return $this->where(array('openid'=>$openid))->save($data);
    }



}