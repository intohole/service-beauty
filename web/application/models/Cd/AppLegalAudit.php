<?php
class Cd_AppLegalAuditModel extends TK_M {
	protected $tableName = 'xmcd_cd_app_legal_audit';
	
	/**ssssss
	 * 审核时添加审核记录
	 * @param int $appid 申请单id
	 * @param int $user 审核人员id
	 * @param int $flow 所处流程
	 * @param int $result 审核结果
	 * @param string $comment 审核意见
	 * @return int
	 */
	public function addInfo($data) {
		return $this->data($data)->add();
	}
	
	/**
	 * 获取一个申请单的所有审核记录
	 * @param int $legal_id
	 * @return array
	 */
	public function getInfo($legal_id) {
		$result = $this->field("*, illegal as illegal_id")->where(array('id'=>$legal_id))->find();
		unset($result['illegal']);
		$fileModel = new FilesModel();

		if($result['court_fileid']){
			if(strstr($result['court_fileid'], ',')){
				$court = explode(',', $result['court_fileid']);
				$result['courtcnt'] = count($court);
				foreach($court as $k=>$v){
					$fileInfo = $fileModel->getFilePath($v);
					$result['court'.$k] = $fileInfo['url'];
					$result['courtid'.$k] = $v;
		            $result['courtsize'.$k] = $this->getImageSize($result['court'.$k]);
				}
				
			}else{
				$fileInfo = $fileModel->getFilePath((int)$result['court_fileid']);
				$result['court'] = $fileInfo['url'];
	            $result['courtsize'] = $this->getImageSize($result['court']);
			}
		}

		if($result['assess_cost_fileids']){
			if(strstr($result['assess_cost_fileids'], ',')){
				$assess = explode(',', $result['assess_cost_fileids']);
				$result['assesscnt'] = count($assess);
				foreach($assess as $k=>$v){
					$fileInfo = $fileModel->getFilePath($v);
					$result['assess'.$k] = $fileInfo['url'];
					$result['assessid'.$k] = $v;
		            $result['assesssize'.$k] = $this->getImageSize($result['assess'.$k]);
				}
			}else{
				$fileInfo = $fileModel->getFilePath((int)$result['assess_cost_fileids']);
				$result['assess'] = $fileInfo['url'];
	            $result['assesssize'] = $this->getImageSize($result['assess']);
			}
				
		}

		if($result['bank_information_fileid']){
			if(strstr($result['bank_information_fileid'], ',')){
				$bank = explode(',', $result['bank_information_fileid']);
				$result['bankcnt'] = count($bank);
				foreach($bank as $k=>$v){
					$fileInfo = $fileModel->getFilePath($v);
					$result['bank'.$k] = $fileInfo['url'];
					$result['bankid'.$k] = $v;
		            $result['banksize'.$k] = $this->getImageSize($result['bank'.$k]);
				}
				
			}else{
				$fileInfo = $fileModel->getFilePath($result['bank_information_fileid']);
				$result['bank'] = $fileInfo['url'];
	            $result['banksize'] = $this->getImageSize($result['bank']);
	        }
		}

		if($result['credit_fileid']){
			if(strstr($result['credit_fileid'], ',')){
				$credit = explode(',', $result['credit_fileid']);
				$result['creditcnt'] = count($credit);
				foreach($credit as $k=>$v){
					$fileInfo = $fileModel->getFilePath($v);
					$result['credit'.$k] = $fileInfo['url'];
					$result['creditid'.$k] = $v;
		            $result['creditsize'.$k] = $this->getImageSize($result['credit'.$k]);
				}
				
			}else{
				$fileInfo = $fileModel->getFilePath($result['credit_fileid']);
				$result['credit'] = $fileInfo['url'];
	            $result['creditsize'] = $this->getImageSize($result['credit']);
	        }
		}
		if($result['illegal_id']){
			if(strstr($result['illegal_id'], ',')){
				$illegal = explode(',', $result['illegal_id']);
				$result['illegalcnt'] = count($illegal);
				foreach($illegal as $k=>$v){
					$fileInfo = $fileModel->getFilePath($v);
					$result['illegal'.$k] = $fileInfo['url'];
					$result['illegalid'.$k] = $v;
		            $result['illegalsize'.$k] = $this->getImageSize($result['illegal'.$k]);
				}
				
			}else{
				$fileInfo = $fileModel->getFilePath($result['illegal_id']);
				$result['illegal'] = $fileInfo['url'];
	            $result['illegalsize'] = $this->getImageSize($result['illegal']);
	        }
		}
		return $result;
	}

	//获取图片尺寸
    public function getImageSize($url){
        if(empty($url)){
            return false;
        }
        $imag=getimagesize($url)[3];
        $image = explode('"', $imag);

        return $image[1].'x'.$image[3];
    }
}

