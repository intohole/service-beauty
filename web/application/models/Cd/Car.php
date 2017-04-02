<?php
class Cd_CarModel extends TK_M {
	protected $tableName = 'xmcd_cd_car';

	// public $photo_marks = array(
	// 	0=>'左前',
	// 	1=>'正前',
	// 	2=>'车身',
	// 	3=>'左前灯',
	// 	4=>'发动机',
	// 	5=>'侧面',
	// 	6=>'车门操控',
	// 	7=>'仪表盘',
	// 	8=>'内饰',
	// 	9=>'前排中控',
	// 	10=>'后尾灯',
	// 	11=>'后备箱'
	// );

    public $photo_marks = array(
        'chetou'=>'车头',
        'chewei'=>'车尾',
        'yibiaopan'=>'仪表盘',
        'zhongkong'=>'中控',
        'neishi'=>'内室',
        'zuoqian'=>'车头斜角左边',
        'youqian'=>'车头斜角右边',
        'zuohou'=>'车尾斜角左边',
        'youhou'=>'车尾斜角右边',
        'chemen'=>'车门操作',
        'fadongji'=>'发动机',
        'houbeixiang'=>'后备箱'
    );
	public $car_colors = array(
		1=>'黑色',
		2=>'白色',
		3=>'银色',
		4=>'咖啡色',
		5=>'红色',
		6=>'其他',
        7=>'灰色',
	);

	public $types = array(
		1=>'新车',
		2=>'二手车',
		3=>'报废车',
		);

    /**
     * 业务员插入车辆信息
     */
    public function addCarInfo($data){
        if(empty($data)){
            return false;
        }
        $new_id = $this->add($data);
        if($new_id)
            return $new_id;
        else
            return false;
    }

    /**
	 * 添加车辆照片
     */
    public function addCarPhoto($id, $data){
    	$where['id'] = $id;
    	$updata['photos'] = $data;
        $updata['status'] = 0;
    	$res = $this->where($where)->save($updata);
    	if($res)
    		return $res;
    	else
    		return false;
    }

    /**
	 * 编辑车辆信息
     */
    public function updatacarinfo($id, $data){
    	$where['id'] = $id;
    	$res = $this->where($where)->save($data);
    	if($res)
    		return $res;
    	else
    		return false;
    }

    /**
	 * 删除车辆信息
     */
    public function deleteCarInfo($id){
    	$where['id'] = $id;
    	$res = $this->where($where)->delete();
    	return $res;
    }

    /**
	 * 获取车辆列表
	 * @param int $ui
	 * @param int $tab
	 * @param int $page
	 * @param int $pagesize
     */
    public function getListByCreatorAndTab($uid, $tab, $page, $pagesize=20){
    	switch ($tab) {
			case 0:
				$estimate = 0;
                $status = 0;
				$field = "xmcd_cd_car.id, xmcd_cd_car.auto_id as frame, brand_name as brand, series_name as series, style_name as version, created as time";
				break;
			case 1:
				$estimate = 1;
                $status = 1;
				$field = "xmcd_cd_car.id, xmcd_cd_car.auto_id as frame, brand_name as brand, series_name as series, style_name as version, created as time, estimate_zh, estimate_total as value";
				break;
		}
		// $where['xmcd_cd_car.estimate'] = $estimate;
        $where['xmcd_cd_car.status'] = $status;
		$where['xmcd_cd_car.creator'] = $uid;
		$list = $this
            	->join("LEFT JOIN xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            	->join("LEFT JOIN xmcd_car_series cse on xmcd_cd_car.series=cse.series_id and xmcd_cd_car.brand=cse.brand_id")
            	->join("LEFT JOIN xmcd_car_style cst on xmcd_cd_car.item=cst.style_id and xmcd_cd_car.brand=cst.brand_id")
            	->field($field)
            	->where($where)
            	->order("id desc")
            	->limit(($page-1)*$pagesize, $pagesize)
            	->select();
        if($list){
        	foreach($list as $key=>$value){
	        	$list[$key]['time'] = date('Y.m.d',$value['time']);
	        	if(isset($list[$key]['estimate_zh'])){
	        		$list[$key]['result'] = mb_substr($value['estimate_zh'], 0, 10, 'utf-8').'...';
	        	}
	        }
        }
        return $list;
    }
    
    /**
	 * 获取指定业务员的车辆列表
	 * @param int $ui
	 * @param int $tab
	 * @param int $page
	 * @param int $pagesize
     */
    public function getListByCreator($uid, $tab, $status='', $page, $pagesize=20){
        
        //车辆发起状态
        if($status !== null)
            $where['status'] = $status;
        
       
        //是否需要查询指定业务员添加的车辆
        if(!empty($uid))
            $where['creator'] = $uid;
        
        if($page == 0){
            $page = 1;
        }
        
        
        $list = $this
        ->join("LEFT JOIN xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
        ->join("LEFT JOIN xmcd_car_series cse on xmcd_cd_car.series=cse.series_id and xmcd_cd_car.brand=cse.brand_id")
        ->join("LEFT JOIN xmcd_car_style cst on xmcd_cd_car.item=cst.style_id and xmcd_cd_car.brand=cst.brand_id")
        ->field("xmcd_cd_car.id,xmcd_cd_car.estimate_total, xmcd_cd_car.auto_id as frame, brand_name as brand, series_name as series, style_name as version, created ")             
        ->where($where)
        ->order("id desc")
        ->limit(($page-1)*$pagesize, $pagesize)
        ->select();
        
        if($list){
            foreach($list as $key=>$value){
                $list[$key]['created'] = date('Y.m.d',$value['created']);
            }
        }    	
        
        return $list;
    }

    //获取更多图片
    public function getCarPhotos($id){
    	$photos = $this->field("photos")->where(array('id'=>$id))->find();
        $photos = rtrim($photos['photos'], ',');
    	$photoarr = explode(',', $photos);
    	$car_photos = array();
    	$fileModel = new FilesModel();
        $photo_marks = $this->photo_marks;
        foreach($photoarr as $value){
            $temparr = explode(':', $value);

            $url = $fileModel->getFilePath($temparr[1]);
            $size = $this->getImageSize($url['url']);
            
            $car_photos[] = array('name'=>$photo_marks[$temparr[0]], 'id'=>$temparr[1], 'src'=>$url['url'], 'size'=>$size);
        }
    	return $car_photos;
    }
    // public function getCarPhotos($id){
    //     $photos = $this->field("photos")->where(array('id'=>$id))->find();
    //     $photo_arr = explode(',', $photos['photos']);
    //     $photo_marks = $this->photo_marks;
    //     $car_photos = array();
    //     $fileModel = new FilesModel();
    //     foreach($photo_arr as $k=>$v){
    //         foreach($photo_marks as $kk=>$vv){
    //             if($k == $kk){
    //                 $url = $fileModel->getFilePath($v);
    //                 $size = $this->getImageSize($url['url']);
    //                 $car_photos[] = array('name'=>$vv, 'src'=>$url['url'], 'id'=>$v, 'size'=>$size);
    //             }
    //         }
    //     }
    //     return $car_photos;
    // }
    

    /**
     * 未评估车辆列表
     */
    public function getNotEstimateCarList(){
        $where['status'] = 1;
        $where['estimate'] = 0;
        $where['estimate_time'] = array('eq', 0);
        $list = $this
            ->join("xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            ->join("xmcd_car_series cse on xmcd_cd_car.series=cse.series_id")
            ->join("xmcd_car_style cst on xmcd_cd_car.item=cst.style_id")
            ->field("brand_name, series_name, style_name, estimate_zh, estimate_time")
            ->where($where)
            ->order("estimate_time desc")
            ->select();
        if(empty($list)){
            return false;
        }
        return $list;
    }

    /**
     * 已评估车辆列表
     */
    public function getHasEstimateCarList(){
        $where['status'] = 1;
        $where['estimate'] = 1;
        $where['estimate_time'] = array('gt', 0);
        $list = $this
            ->join("xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            ->join("xmcd_car_series cse on xmcd_cd_car.series=cse.series_id")
            ->join("xmcd_car_style cst on xmcd_cd_car.item=cst.style_id")
            ->field("cd.brand_name, cse.series_name, cst.style_name, estimate_zh, estimate_time")
            ->where($where)
            ->order("estimate_time desc")
            ->select();
        if(empty($list)){
            return false;
        }
        return $list;
    }

    /**
	 * 获取车辆详细信息
	 * @param int $id
     */
    public function getInfo($id){
    	if(empty($id)) return false;
    	$where['xmcd_cd_car.id'] = $id;
    	$fields = "cb.brand_name, cse.series_name, cst.style_name, type, license, drive_record, invoice, registration, color, drive_record, sp_date, sl_date, frame_id, auto_id, photos, estimate,status, estimate_total as value, estimate_zh as result, mode, product_place, displacement, source_income, trans_amount, created";
    	$list = $this
            ->join("LEFT JOIN xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            ->join("LEFT JOIN xmcd_car_series cse on xmcd_cd_car.series=cse.series_id and xmcd_cd_car.brand=cse.brand_id")
            ->join("LEFT JOIN xmcd_car_style cst on xmcd_cd_car.item=cst.style_id")
            ->field($fields)
            ->where($where)
            ->find();
        $photo_marks = $this->photo_marks;
		$car_colors = $this->car_colors;
		$types = $this->types;
        $fileModel = new FilesModel();
        if($list['invoice']){
            $fileInfo = $fileModel->getFilePath($list['invoice']);
            $list['invoice'] = $fileInfo['url'];
            $list['invoicesize'] = $this->getImageSize($list['invoice']);
		}
		if($list['license']){
			$fileInfo = $fileModel->getFilePath($list['license']);
			$list['license'] = $fileInfo['url'];
            $list['licensesize'] = $this->getImageSize($list['license']);
		}
		if($list['registration']){
            if(strstr($list['registration'], ',')){
                $registration = explode(',', $list['registration']);
                foreach($registration as $k=>$v){
                    if($v){
                        $fileInfo = $fileModel->getFilePath($v);
                        $list['registration_info'][$k]['url'] = $fileInfo['url'];
                        $list['registration_info'][$k]['size'] = $this->getImageSize($fileInfo['url']);
                    }
                }
            }else{
    			$fileInfo = $fileModel->getFilePath($list['registration']);
    			$list['registration'] = $fileInfo['url'];
                $list['registrationsize'] = $this->getImageSize($list['registration']);
            }
		}
		if($list['photos']){
			$car_photos = explode(',', $list['photos']);
			foreach($car_photos as $k=>$v){
				if($k<3){
                    $temparr = explode(':', $v);
					$fileInfo = $fileModel->getFilePath($temparr[1]);
					$list['car_photos'][] = array(
						'src'=>$fileInfo['url'],
						'name'=>$photo_marks[$temparr[0]],
                        'size'=>$this->getImageSize($fileInfo['url']),
					);
				}
			}
		}
		if($list['color']){
			$list['color'] = $car_colors[$list['color']];
		}
		$list['type'] = $types[$list['type']];
        // if(strlen($list['result']) > 40){
        //     $list['result'] = mb_substr($list['result'], 0, 40, 'utf-8').'...';
        // }
		return $list;
    }

     /**
	 * 获取车辆详细信息
	 * @param int $id
     */
    public function getUpdateInfo($id){
    	if(empty($id)) return false;
    	$where['xmcd_cd_car.id'] = $id;
    	$fields = "cb.brand_name, cse.series_name, cst.style_name, brand, series, item, type, license, drive_record, invoice, registration, color, drive_record, sp_date, sl_date, frame_id, auto_id, photos, estimate, mode, product_place, displacement, source_income, trans_amount";
    	$list = $this
            ->join("LEFT JOIN xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            ->join("LEFT JOIN xmcd_car_series cse on xmcd_cd_car.series=cse.series_id and xmcd_cd_car.brand=cse.brand_id")
            ->join("LEFT JOIN xmcd_car_style cst on xmcd_cd_car.item=cst.style_id and xmcd_cd_car.brand=cst.brand_id")
            ->field($fields)
            ->where($where)
            ->find();
        $photo_marks = $this->photo_marks;
		$car_colors = $this->car_colors;
		$types = $this->types;
        $fileModel = new FilesModel();
        if($list['invoice']){
			$fileInfo = $fileModel->getFilePath($list['invoice']);
			$list['invoice_url'] = $fileInfo['url'];
		}
		if($list['license']){
			$fileInfo = $fileModel->getFilePath($list['license']);
			$list['license_url'] = $fileInfo['url'];
		}
		// if($list['registration']){
		// 	$fileInfo = $fileModel->getFilePath($list['registration']);
		// 	$list['registration_url'] = $fileInfo['url'];
		// }
        if($list['registration']){
            if(strstr($list['registration'], ',')){
                $registration = explode(',', $list['registration']);
                foreach($registration as $k=>$v){
                    if($v){
                        $fileInfo = $fileModel->getFilePath($v);
                        $list['registration_info'][$k]['url'] = $fileInfo['url'];
                        $list['registration_info'][$k]['id'] = $v;
                    }
                }
            }else{
                $fileInfo = $fileModel->getFilePath($list['registration']);
                $list['registration_url'] = $fileInfo['url'];
            }
        }
		if($list['photos']){
			$car_photos = explode(',', $list['photos']);
			foreach($car_photos as $k=>$v){
				if($k<3){
					$fileInfo = $fileModel->getFilePath($v);
					$list['car_photos'][] = array(
						'src'=>$fileInfo['url'],
						'name'=>$photo_marks[$k],
					);
				}
			}
		}
		if($list['color']){
			$list['color'] = $car_colors[$list['color']];
		}
		$list['type'] = $types[$list['type']];
// echo "<pre><meta charset='utf-8'>";var_dump($list);exit;
		return $list;
    }

    /**
     * 获取未评估车辆详细信息
     */
    public function infoNotEstimate($cd_car_id){
        if(empty($cd_car_id)) return false;

        $where['id'] = $cd_car_id;
        $car_info = $this
            ->join("xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            ->join("xmcd_car_series cse on xmcd_cd_car.series=cse.series_id")
            ->join("xmcd_car_style cst on xmcd_cd_car.item=cst.style_id")
            ->field("license, drive_record, color, sp_date, sl_date, frame_id, auto_id, photos, status, estimate")
            ->where($where)
            ->find();

        if(empty($car_info)) return false;

        $license_url = $this->_fileModel->getFilePath($car_info['license']);
        $car_info['license'] = "<a href=\"{$license_url['url']}\" target=\"_black\">点击查看图片</a>";

        if(strlen($car_info['photos'])>0){
            $photos = explode(',', $car_info['photos']);
            foreach($photos as $v){
                $photo_url = $this->_fileModel->getFilePath($v);
                $car_info['photos'][] = "<a href=\"{$photo_url['url']}\" target=\"_black\">点击查看图片</a>";
            }
        }

        return $car_info;
    }

    /**
     * 获取已评估车辆信息详情
     */
    public function infoHasEstimate($cd_car_id){
        if(empty($cd_car_id)) return false;

        $where['id'] = $cd_car_id;
        $car_info = $this
            ->join("xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            ->join("xmcd_car_series cse on xmcd_cd_car.series=cse.series_id")
            ->join("xmcd_car_style cst on xmcd_cd_car.item=cst.style_id")
            ->field("license, drive_record, color, sp_date, sl_date, frame_id, auto_id, photos, status, estimate")
            ->where($where)
            ->find();

        if(empty($car_info)) return false;

        $license_url = $this->_fileModel->getFilePath($car_info['license']);
        $car_info['license'] = "<a href=\"{$license_url['url']}\" target=\"_black\">点击查看图片</a>";

        if(strlen($car_info['photos'])>0){
            $photos = explode(',', $car_info['photos']);
            foreach($photos as $v){
                $photo_url = $this->_fileModel->getFilePath($v);
                $car_info['photos'][] = "<a href=\"{$photo_url['url']}\" target=\"_black\">点击查看图片</a>";
            }
        }
        
        return $car_info;
    }

    /**
     * 业务员发起已录入车辆信息列表
     */
    public function launchCarList(){
        $where['status'] = 0;
        $where['estimate'] = 0;
        $list = $this
            ->join("xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
            ->join("xmcd_car_series cse on xmcd_cd_car.series=cse.series_id")
            ->join("xmcd_car_style cst on xmcd_cd_car.item=cst.style_id")
            ->field("cd.brand_name, cse.series_name, cst.style_name, xmcd_cd_car.id, frame_id, created")
            ->where($where)
            ->order("created desc")
            ->select();
        return $list;
    }

    /**
     * 业务员发起已录入车辆
     */
    public function launchCar($ids){
        if(!count($ids)>0) return false;
        $res = true;
        $this->startTrans();
        foreach($ids as $v){
            $status = $this
                ->field("status")
                ->where("id={$v}")
                ->find();
            if(!$status){
                $updata['status'] = 1;
                $id = $this
                    ->where("id={$v}")
                    ->save($updata);
            }
            if(!$id){
                $this->rollback();
                $res = false;
            }
        }
        if($res){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }

    /**
	 * 评估师添加评估报告
	 * @param int $app_id
	 * @param array $updataData
	 */
	public function upAssessmentReport($app_id,$updataData,$creator,$flow){
            $where['id'] = $updataData['id'];
            $updataData['estimate'] = 1;
            $updataData['estimate_time'] = time();

            //事务开始
            $this->startTrans();

            //填写评估报告
            $res = $this->where($where)->save($updataData);

            if($res){
                //修改申请单流程
                $app_mod = new Cd_AppModel();
                //$a_data['flow'] = 3;
                $a_data['flow'] = ((int)$flow) + 1;
                $res = $app_mod->where(array('id'=>$app_id))->save($a_data);
            }

            //事务结束
            if($res){
                $this->commit();
                return $res;
            }else{
                $this->rollback();
                return FALSE;
            }
                
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

    //获取图片名称列表
    public function getPhotoNames(){
        return $this->photo_marks;
    }

    //更改已存车贷数据图片格式
    public function changeCarPhotos(){
        $photo_list = $this
                      ->field("id, creator, photos")
                      ->select();
        $photo_marks = $this->photo_marks;
        $photo_marks = array_keys($photo_marks);
        $arr = array(0=>1, 1=>10, 2=>7, 3=>9, 4=>8, 5=>0, 6=>3, 7=>5, 8=>2, 9=>6, 10=>4, 11=>11);
        $return = true;
        $this->startTrans();
        foreach($photo_list as $value){
            // if($value['id'] == 14){
                $photo = $value['photos'];
                $photo = explode(',', $photo);
                foreach($arr as $k=>$v){
                    $new_arr[] = array($photo_marks[$k], $photo[$v]);
                }
                foreach($new_arr as $kk=>$vv){
                    $new_photo_arr[] = implode(':', $vv);
                }
                $new_str = implode(',', $new_photo_arr);
                $new_str = $new_str.',';
                $updata['photos'] = $new_str;
                $res = $this->where(array('id'=>$value['id'], 'creator'=>$value['creator']))->save($updata);

                $new_arr = array();
                $new_photo_arr = array();

                if(!$res){
                    $return = false;
                    break;
                }
            // }
        }
        if($return){
            $this->commit();
            return $return;
        }else{
            $this->rollback();
            return false;
        }
    }
        
    
    /**
    * 获取车辆数量
    * @return int
    */
    public function getCarCount($where=null){
        $this->join("LEFT JOIN xmcd_cd_app ca on xmcd_cd_car.id=ca.car");
        
        if(!empty($where)){
            $this->where($where);
        }
        return $this->count();
    }
    
    
    /**
	 * 获取全部车辆列表
	 * @param int $page
	 * @param int $pagesize
     */
    public function getCarList($page, $pagesize, $where=null){
        
        $selcet = $this;
        
        $selcet->field("xmcd_cd_car.id,xmcd_cd_car.estimate_total, xmcd_cd_car.auto_id as frame, xmcd_cd_car.frame_id as auto, brand_name as brand, series_name as series, style_name as version, ca.oid, xmcd_cd_car.created ");
        // $selcet->field("xmcd_cd_car.id,xmcd_cd_car.estimate_total, xmcd_cd_car.auto_id as frame, ca.oid, xmcd_cd_car.created ");
        
        $selcet->join("LEFT JOIN xmcd_cd_app ca on xmcd_cd_car.id=ca.car")
             ->join("LEFT JOIN xmcd_car_brand cb on xmcd_cd_car.brand=cb.brand_id")
             ->join("LEFT JOIN xmcd_car_series cse on xmcd_cd_car.series=cse.series_id and xmcd_cd_car.brand=cse.brand_id")
             ->join("LEFT JOIN xmcd_car_style cst on xmcd_cd_car.item=cst.style_id and xmcd_cd_car.brand=cst.brand_id");
        
        if(!empty($where)){
            $selcet->where($where);
        }
                
        $list = $selcet->order("id desc")
             ->limit($page, $pagesize)
             ->select();
        
        if($list){
            foreach($list as $key=>$value){
                $list[$key]['created'] = date('Y.m.d',$value['created']);
            }
        }    	
        
        return $list;
    }

    /**
     * 将车辆信息设置为待发起状态
     * @param int $car
     * @param int $creator
     * @return boolean
     */
    public function setBack($creator, $car) {
        return $this->where(array('id'=>$car, 'creator'=>$creator))->save(array('status'=>0, 'estimate'=>0));
    }
        
}