<?php
class Cd_AppModel extends TK_M {
	/*
	 * status : 1.进行中  2.已终结  3.已完成
	*/
	protected $tableName = 'xmcd_cd_app';
	
	public $status = array(
		1=>'进行中',
		2=>'已终结',
		3=>'已完成',
	);
        
    public $type = array(
		1=>'以租代购',
		2=>'车辆质押',
		3=>'车辆抵押',
	);

	public $photo_marks = array(
		0=>'左前',
		1=>'正前',
		2=>'车身',
		3=>'左前灯',
		4=>'发动机',
		5=>'侧面',
		6=>'车门操控',
		7=>'仪表盘',
		8=>'内饰',
		9=>'前排中控',
		10=>'后尾灯',
		11=>'后备箱'
	);

	public $car_colors = array(
		1=>'黑色',
		2=>'白色',
		3=>'银色',
		4=>'咖啡色',
		5=>'红色',
        6=>'其他',
        7=>'灰色'
	);
	
	/**
	 * 创建申请单
         * @mod hgy
	 * @param array $data
	 * @return array
	 */
	public function addApp($data) {
		$y = date('Y', time());
		$m = date('m', time());
		$d = date('d', time());
		$start_time = mktime(00,00,00,$m,$d,$y);
		//获取当前机构id
		$userModel = new AdminUserModel();
		$oid = $userModel->get($data['creator'])['oid'];
		//获取当前机构当天插入最大值 如果有则+1  没有设置为1
		$where['oid'] = array("eq", $oid);
		$where['created'] = array('gt', $start_time);
		$has_sort = $this->field('sort')->where($where)->order("id desc")->limit(1)->find();
		if($has_sort['sort']){
			$sort = $has_sort['sort'] + 1;
		}else{
			$sort = 1;
		}
		$app = array(
			'creator'=>$data['creator'],
			'customer'=>$data['customer'],
			'car'=>$data['car'],
			'amount'=>$data['amount'],
			'rate'=>$data['rate'],
            'deadline'=>$data['deadline'],
            'oid'=>$data['oid'],
            'appraiser_id'=>$data['appraiser_id'],
            'creator_comment'=>$data['creator_comment'],
			'status'=>1,	//进行中
			'flow'=>2,	//创建完之后 默认进入下一流程 即评估师评估
			'sort'=>$sort,
			'created'=>time(),
			'launch'=>$data['launch'],
		);
                
                //事务开始
                $this->startTrans();
                
                //添加申请单
		$id = $this->data($app)->add();
		if (!$id) {
                    return FALSE;
		}
                
                //如果添加成功生成订单号
		// $org_info = $this
		// 			->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
		// 			->join("LEFT JOIN xmcd_org o on u.oid=o.id")
		// 			->field("o.first_letter")
		// 			->where(array('id'=>$id))
		// 			->find();

		// $app['no'] = 'CD'.date('ymdHi').sprintf('%02d', substr($id, -2));
		// $app['no'] = $org_info['first_letter'].date('ymdHi').sprintf('%02d', substr($id, -2));
		// $this->where(array('id'=>$id))->save(array('no'=>$app['no']));
                
                //修改客户状态
                $user_mod = new Cd_UserModel();
                $u_data['status'] = 2;
                $affect = $user_mod->where(array('id'=>$data['customer']))->save($u_data);
                
                //修改车辆状态
                if($affect){
                    $car_mod = new Cd_CarModel();
                    $c_data['status'] = 1;
                    $affect = $car_mod->where(array('id'=>$data['car']))->save($c_data);
                }
                
                //事务结束
                if($affect){
                    $this->commit();
                }else{
                    $this->rollback();
                    return FALSE;
                }
               
		return $app;
	}
	
	public function getApp($appid) {
		return $this->where(array('id'=>$appid))->find();
	}
	
	/**
	 * 获取申请单数量
	 * @param string $flow
	 * @param string $status
	 * @return int
	 */
	public function getAppCnt($flow=NULL, $status=NUll, $where=null) {
            $select = $this;
            if ($flow) {
                if (is_array($flow)) {
                    $select->where(array('flow'=>array('in', $flow)));
                } else {
                    $select->where(array('flow'=>$flow));
                }
            }
            if ($status) {
                $select->where(array('status'=>$status));
            }

            if(!empty($where)){
                $select->where($where);
            }
            $select->join("LEFT JOIN xmcd_cd_user u on xmcd_cd_app.customer=u.id");
            $select->join("LEFT JOIN xmcd_cd_car c on xmcd_cd_app.car=c.id");
            return $select->count();
	}
	
	/**
	 * 获取业务员的申请单列表
     * @mod hgy
	 * @param int $creator
	 * @param number $page
	 * @param string $flow
	 * @param string $status
	 * @param number $pagesize
	 * @return array
	 */
	public function getAppList($page=1, $flow=NULL, $status=NULL, $creator, $pagesize=20) {
		$select = $this;
                
                //$subQuery = $select->field('id as a_id,app_id,agree_amount,agree_rate,created')->table('xmcd_cd_audit_info')->where("result = 1 and created in (select max(created) from xmcd_cd_audit_info group by app_id)")->select(false);
                
                $fields = "xmcd_cd_app.*, u.name as realname,from_unixtime(xmcd_cd_app.created, '%Y.%m.%d') as createdtime ";
                
                if(!empty($creator))
                    $select->where(array('xmcd_cd_app.creator'=>$creator));
                
		if ($flow) {
                    if (is_array($flow)) {
                        $select->where(array('flow'=>array('in', $flow)));
                    } else {
                        $select->where(array('flow'=>$flow));
                    }
		}
                
                //选择进行中时查出进行中的订单,选择已完成是查出已完成和已终结的
                if($status !== null){
                    if ($status == 1) {
                        $select->where(array('xmcd_cd_app.status'=>$status));
                    }
                    else{
                        $select->where("xmcd_cd_app.status != 1");
                    }
                }
                
                $select->join("LEFT JOIN xmcd_cd_user u on xmcd_cd_app.customer=u.id");
                
                $this->field($fields);
                
		return $select->order('id desc')->page($page, $pagesize)->select();
	}

	/**
	 * 获取业务员的申请单列表
	 * @param int $creator
	 * @param number $page
	 * @param string $flow
	 * @param string $status
	 * @param number $pagesize
	 * @return array
	 */
	public function getManagerAppListByTab($tab=0, $flow=NULL, $page=1, $type, $ids, $pagesize=20) {
		
		$select = $this;
        $fields = "xmcd_cd_app.*, u.realname as name, cu.type, cu.name as customer_name, o.name as area, from_unixtime(xmcd_cd_app.created) as createdtime, cc.estimate_zh as result ";
                
		if($tab == 1){
			if($flow == 7){
				$select->where(array('flow'=>$flow, 'xmcd_cd_app.status'=>3));
			}else{
				$select->where(array('flow'=>$flow+1, 'xmcd_cd_app.status'=>1));
			}
		}else{
			$select->where(array('flow'=>$flow, 'xmcd_cd_app.status'=>1));
		}
		$select->where(array('cu.type'=>$type));
		$select->where(array("xmcd_cd_app.creator"=>array('in',$ids)));
                
        $select->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
        	   ->join("LEFT JOIN xmcd_org o on u.oid=o.id")
        	   ->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id")
        	   ->join("LEFT JOIN xmcd_cd_car cc on xmcd_cd_app.car=cc.id")
        	   ->field($fields);
        $result = $select->order('id desc')->page($page, $pagesize)->select();
        foreach($result as $key=>$value){
        	$result[$key]['area'] = (mb_substr($value['area'], 0, 5, 'utf-8')).'...';
        	$result[$key]['result'] = (mb_substr($value['result'], 0, 5, 'utf-8')).'...';
        	$result[$key]['type'] = $this->type[$value['type']];
        }
		return $result;
	}
	
	/**
	 * 获取业务员的申请单列表记录数
	 * @param int $creator
	 * @param string $flow
	 * @param string $status
	 * @return number
	 */
	public function getAppCntByCreator($creator, $status=NULL, $flow=NULL) {
		$select = $this->where(array('creator'=>$creator));
		if ($flow) {
			if (is_array($flow)) {
				$select->where(array('flow'=>array('in', $flow)));
			} else {
				$select->where(array('flow'=>$flow));
			}
		}
		if ($status) {
			$select->where(array('status'=>$status));
		}
		return $select->count();
	}
	
	/**
	 * 获取业务员的申请单列表
	 * @param int $creator
	 * @param number $page
	 * @param string $flow
	 * @param string $status
	 * @param number $pagesize
	 * @return array
	 */
	public function getAppListByCreator($creator, $page=1, $status=NULL, $flow=NULL, $pagesize=20) {
		$select = $this->where(array('creator'=>$creator));
		if ($flow) {
			if (is_array($flow)) {
				$select->where(array('flow'=>array('in', $flow)));
			} else {
				$select->where(array('flow'=>$flow));
			}
		}
		if ($status) {
			$select->where(array('status'=>$status));
		}
		return $select->order('id desc')->page($page, $pagesize)->select();
	}

	/**
	 * 获取业务员的申请单详情
	 * @param int $app_id
         * @mod hgy
	 */
	public function getAppInfo($app_id) {
		$fields = "xmcd_cd_app.*,fcc.brand, fcc.id as car_id, fcc.series, fcc.item, fcc.frame_id, fcc.auto_id, fcc.created as car_created, fcu.name, fcu.marriage, fcu.phone, fcu.driving_years, fcu.created, fcu.birthday, fu.realname as appraiser_name, fu2.realname as creator_name";
		$select = $this->join("LEFT JOIN xmcd_cd_car fcc on xmcd_cd_app.car=fcc.id")
                       ->join("LEFT JOIN xmcd_cd_user fcu on xmcd_cd_app.customer=fcu.id")
                       ->join("LEFT JOIN xmcd_users fu on xmcd_cd_app.appraiser_id=fu.id")
                       ->join("LEFT JOIN xmcd_users fu2 on xmcd_cd_app.creator=fu2.id");
		$this->field($fields);
		$select = $this->where(array('xmcd_cd_app.id'=>$app_id));
		$result = $select->order('xmcd_cd_app.id desc')->select();

		$carBrandModel = new CarBrandModel();
		foreach($result as $k=>$v){
			$car_info = $carBrandModel->getCarInfo($v['brand'], $v['series'], $v['item']);
			$result[$k]['brand'] = $car_info['brand_name'];
			$result[$k]['series'] = $car_info['series_name'];
			$result[$k]['item'] = $car_info['style_name'];
			if($v['launch']==2){
				$result[$k]['marriage'] = 2;
			}
		}
          // echo "<pre><meta charset='utf-8'>";var_dump($result);exit;      
		return $result;
	}

	/**
	 * 经理级页面详情
	 * @param int $app_id
	 */
	public function getInfo($app_id){
		$field = "xmcd_cd_app.*, u.realname as name, cu.type, cu.name as cu_name, cu.birthday, o.name as area, cc.estimate_zh as result, cc.estimate_total as total,cc.brand, cc.id as car_id, cc.series, cc.item, cc.frame_id, cc.auto_id, cc.created as car_created, cu.marriage, cu.phone, cu.driving_years, cu.created";
		$where['xmcd_cd_app.id'] = $app_id;
		$data = $this
			   ->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
			   ->join("LEFT JOIN xmcd_org o on u.oid=o.id")
			   ->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id")
			   ->join("LEFT JOIN xmcd_cd_car cc on xmcd_cd_app.car=cc.id")
			   ->field($field)
			   ->where($where)
			   ->find();
		$carBrandModel = new CarBrandModel();
		$car_info = $carBrandModel->getCarInfo($data['brand'], $data['series'], $data['item']);
		$data['brand'] = $car_info['brand_name'];
		$data['series'] = $car_info['series_name'];
		$data['item'] = $car_info['style_name'];

        if($data){
            $data['type'] = $this->type[$data['type']];
        }

		return $data;
	}

	/**
	 * 获取评估师要评估的申请单列表
         * @Author: hgy 
	 */
	public function getUnAssessList($flow=NULL, $status=NULL, $uid, $page=1, $pagesize=20){
            $select = $this;

            $fields = "xmcd_cd_app.*, u.realname, u.oid, cu.name as customer_name, cu.type ";

            //只能查看用户所属公司的订单
            $select->where('u.oid = o.id');

            //flow=2是待评估，大于2是已评估,这里这样写是因为前端传过来的值是0或1
            if($flow == 0) {
                $select->where(array('flow'=>2, 'appraiser_id'=>$uid, 'cc.estimate'=>0));
            }
            else{
            	$where = "flow > 2 AND cc.estimate=1 AND (appraiser_id=".$uid." or appraiser_id IS NULL)";
                // $select->where('flow > 2');
                // $select->where("appraiser_id=".$uid." or appraiser_id IS NULL");
                $select->where($where);
            }

            if ($status) {
                if (is_array($status)) {
                    $select->where(array('xmcd_cd_app.status'=>array('in', $status)));
                } else {
                    $select->where(array('xmcd_cd_app.status'=>$status));
                }
            }

            $select->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id");
            $select->join("LEFT JOIN xmcd_cd_car cc on xmcd_cd_app.car=cc.id");
            $select->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id");
            //$select->join("LEFT JOIN xmcd_org o on u.oid=o.id");

            $select->field($fields);

            return $select->order('id desc')->page($page, $pagesize)->select();

	}

	/**
	 * 获取业务员的申请单列表
	 * @param int $app_id
	 */
	public function getAssessInfo($app_id) {
		$fields = "xmcd_cd_app.*, fcc.brand, fcc.series, fcc.item, fcc.frame_id, fcc.invoice, fcc.license, fcc.registration, fcc.photos, fcc.created as car_created, fcu.name, fcu.marriage, fcu.phone, fcu.driving_years, fcu.created";
		$select = $this->join("LEFT JOIN xmcd_cd_car fcc on xmcd_cd_app.car=fcc.id")
					   ->join("LEFT JOIN xmcd_cd_user fcu on xmcd_cd_app.customer=fcu.id");
		$this->field($fields);
		$select = $this->where(array('xmcd_cd_app.id'=>$app_id));
		$result = $select->order('xmcd_cd_app.id desc')->find();
		$carBrandModel = new CarBrandModel();
		$car_info = $carBrandModel->getCarInfo($result['brand'], $result['series'], $result['item']);
		$result['brand'] = $car_info['brand_name'];
		$result['series'] = $car_info['series_name'];
		$result['item'] = $car_info['style_name'];

		$photo_marks = $this->photo_marks;
		$fileModel = new FilesModel();

		if($result['invoice']){
			$fileInfo = $fileModel->getFilePath($result['license']);
			$result['certificates'][] = "<a href=\"{$fileInfo['url']}\" target=\"_black\">购车发票</a>";
		}
		if($result['license']){
			$fileInfo = $fileModel->getFilePath($result['license']);
			$result['certificates'][] = "<a href=\"{$fileInfo['url']}\" target=\"_black\">行驶证</a>";
		}
		if($result['registration']){
			$fileInfo = $fileModel->getFilePath($result['registration']);
			$result['certificates'][] = "<a href=\"{$fileInfo['url']}\" target=\"_black\">登记证</a>";
		}

		if($result['photos']){
			$car_photos = explode(',', $result['photos']);
			foreach($car_photos as $k=>$v){
				$fileInfo = $fileModel->getFilePath($v);
				$result['car_photos'][] = "<a href=\"{$fileInfo['url']}\" target=\"_black\">{$photo_marks[$k]}</a>";
			}
		}
		return $result;
	}

	/**
	 * 获取customer、car
	 * @param int $app_id
	 */
	public function getCustomerCar($app_id){
		$where['id'] = $app_id;
		return $this->field("customer,car,creator")->where($where)->find();
	}

	/**
	 * 获取客户信息
	 */
	public function getCustomerInfo($app_id){
		return  $this
				->join("LEFT JOIN xmcd_cd_user fcu on xmcd_cd_app.customer=fcu.id")
				->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
				->field("fcu.name, xmcd_cd_app.amount, u.phone")
				->where(array("xmcd_cd_app.id"=>$app_id))
				->find();
	}

	/**
	 * 获取未评估详细信息
	 */
	public function UnAssessInfo($app_id){
            $where['xmcd_cd_app.id'] = $app_id;
            $fields = "xmcd_cd_app.creator, xmcd_cd_app.id, fcc.brand, fcc.series, fcc.item, fcc.created, fcc.frame_id, fcc.color, fcc.sp_date, fcc.sl_date, fcc.auto_id, fcc.drive_record, fcc.license, fcc.registration, fcc.invoice, fcc.photos, cb.brand_name, cse.series_name, cst.style_name";
            $list = $this
                    ->join("LEFT JOIN xmcd_cd_car fcc on xmcd_cd_app.car=fcc.id")
                    ->join("LEFT JOIN xmcd_car_brand cb on fcc.brand=cb.brand_id")
                    ->join("LEFT JOIN xmcd_car_series cse on fcc.series=cse.series_id")
                    ->join("LEFT JOIN xmcd_car_style cst on fcc.item=cst.style_id")
                    ->field($fields)
                    ->where($where)
                    ->find();

            $photo_marks = $this->photo_marks;
            $fileModel = new FilesModel();
            if($list['license']){
                $license_url = $fileModel->getFilePath($list['license']);
                $list['license'] = "<a href=\"{$license_url['url']}\" target=\"_black\">行驶证</a>";
            }
            if($list['registration']){
                    $license_url = $fileModel->getFilePath($list['registration']);
            $list['registration'] = "<a href=\"{$license_url['url']}\" target=\"_black\">登记证</a>";
            }
            if($list['invoice']){
                    $license_url = $fileModel->getFilePath($list['invoice']);
            $list['invoice'] = "<a href=\"{$license_url['url']}\" target=\"_black\">购车发票</a>";
            }
            if(strlen($list['photos'])>0){
        $photos = explode(',', $list['photos']);
        foreach($photos as $k=>$v){
            $photo_url = $fileModel->getFilePath($v);
            $list['photo_button'][] = "<a href=\"{$photo_url['url']}\" target=\"_black\">{$photo_marks[$k]}</a>";
        }
    }
echo "<pre><meta charset='utf-8'>";var_dump($list);exit;
            if($list){
                    return $list;
            }else{
                    return false;
            }
	}

	/**
	 * 获取已评估详细信息
	 */
	public function AssessInfo($id,$flow=2){
            $where['xmcd_cd_app.id'] = $id;
            
            //根据评估状态查询不同信息
            if($flow == 2) {
                $where['flow'] = $flow;
                $where['fcc.estimate'] = 0;
               
                $fields = "xmcd_cd_app.id as app_id, xmcd_cd_app.creator, xmcd_cd_app.customer, xmcd_cd_app.flow, "
                        . "u.realname, cu.type as u_type, fcc.*, cb.brand_name, cse.series_name, cst.style_name ";
                        //. "ff1.path as license_path, ff2.path as registration_path, ff3.path as invoice_path ";

                $list = $this
                        ->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
                        ->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id")
                        ->join("LEFT JOIN xmcd_cd_car fcc on xmcd_cd_app.car=fcc.id")
                        ->join("LEFT JOIN xmcd_car_brand cb on fcc.brand=cb.brand_id")
                        ->join("LEFT JOIN xmcd_car_series cse on fcc.series=cse.series_id and fcc.brand=cse.brand_id")
                        ->join("LEFT JOIN xmcd_car_style cst on fcc.item=cst.style_id")               
                        //->join("LEFT JOIN xmcd_files ff1 on fcc.license=ff1.id")
                        //->join("LEFT JOIN xmcd_files ff2 on fcc.registration=ff2.id")
                        //->join("LEFT JOIN xmcd_files ff3 on fcc.invoice=ff3.id")                
                        ->field($fields)
                        ->where($where)
                        ->find();
            }
            else{
                $where['flow'] = array('gt',2);
                $where['fcc.estimate'] = 1;
                
                $fields = "xmcd_cd_app.id as app_id, xmcd_cd_app.creator, xmcd_cd_app.customer, xmcd_cd_app.flow, "
                        . "u.realname, cu.type as u_type, fcc.*, cb.brand_name, cse.series_name, cst.style_name ";

                $list = $this
                        ->join("LEFT JOIN xmcd_users u on xmcd_cd_app.creator=u.id")
                        ->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id")
                        ->join("LEFT JOIN xmcd_cd_car fcc on xmcd_cd_app.car=fcc.id")
                        ->join("LEFT JOIN xmcd_car_brand cb on fcc.brand=cb.brand_id")
                        ->join("LEFT JOIN xmcd_car_series cse on fcc.series=cse.series_id and fcc.brand=cse.brand_id")
                        ->join("LEFT JOIN xmcd_car_style cst on fcc.item=cst.style_id")                
                        ->field($fields)
                        ->where($where)
                        ->find();
                
            }
            
            if($list && $flow == 2){
                //车辆颜色
                $list['color_name'] = $this->car_colors[$list['color']];
                
                //查出证件照片
                $fileModel = new FilesModel();
                if($list['invoice']){
			$fileInfo = $fileModel->getFilePath($list['invoice']);
			$list['invoice_path'] = $fileInfo['url'];
			$list['invoice_path_size'] = $this->getImageSize($list['invoice_path']);
		}
		if($list['license']){
			$fileInfo = $fileModel->getFilePath($list['license']);
			$list['license_path'] = $fileInfo['url'];
			$list['license_path_size'] = $this->getImageSize($list['license_path']);
		}
		if($list['registration']){
			$fileInfo = $fileModel->getFilePath($list['registration']);
			$list['registration_path'] = $fileInfo['url'];
			$list['registration_path_size'] = $this->getImageSize($list['registration_path']);
		}
                //查出车辆照片
		// if($list['photos']){
  //           $car_mod = new Cd_CarModel;
  //           $car_photos = explode(',', $list['photos']);
  //           foreach($car_photos as $v){
  //           	$new_arr[] = explode(':', $v)[1];
  //           }
  //           $list['photos'] = array();
            
  //           $fileInfo = $fileModel->getFiles($new_arr);
  //           $photo_marks = $car_mod->photo_marks;
  //           $photo_marks = array_values($photo_marks);
  //           $num = 0;
  //           foreach($fileInfo as $val){
  //               $list['photos'][$num]['path'] = $val['url'];
  //               $list['photos'][$num]['name'] = $photo_marks[$num];
  //               $list['photos'][$num]['path_size'] = $this->getImageSize($val['url']);
  //               $num++;
  //           }
            
  //           //评估师详情页只显示前三张车辆照片
  //           if(count($list['photos']) > 3){
  //               $list['photos'] = array_slice($list['photos'],0,3);
  //           }
		// }
		$photo_marks = $this->photo_marks;
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
                return $list;
            }
            else if($list && $flow != 2){
                return $list;
            }
            else{
                return false;
            }
            
	}

	//更改app流程
	public function changeFlow($app_id, $flow){
		return $this->where(array('id'=>$app_id))->save(array('flow'=>$flow));
	}

	//更改app status
	public function changeStatus($app_id){
		return $this->where(array('id'=>$app_id))->save(array('status'=>2));
	}

	//经理同意操作
	public function appAgree($app_id, $uid, $flow){
        if($flow < 7){
        	$updata['flow'] = $flow + 1;
        }else{
        	$updata['status'] = 3;
        }
        $res = $this->where(array('id'=>$app_id))->save($updata);
        return $res;
	}
        

    /**
     * 获取业务员的申请单列表
     * @mod hgy
     * @param number $page
     * @param number $pagesize
     * @return array
     */
    public function getAllList($page=0,$pagesize=20,$where=null) {
        $select = $this;

        $fields = "xmcd_cd_app.*, u.name as realname,from_unixtime(xmcd_cd_app.created, '%Y.%m.%d') as createdtime, u.phone, c.auto_id, c.frame_id";

        $select->join("LEFT JOIN xmcd_cd_user u on xmcd_cd_app.customer=u.id");
        $select->join("LEFT JOIN xmcd_cd_car c on xmcd_cd_app.car=c.id");

        $select->field($fields);

        if(!empty($where)){
            $select->where($where);
        }

        return $select->order('id desc')->limit($page, $pagesize)->select();
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

    //获取评估师完成发送短信详细信息
    public function getSMSInfo($app_id){
    	$result = $this
    			  ->join("LEFT JOIN xmcd_users fu on xmcd_cd_app.creator=fu.id")
    			  ->join("LEFT JOIN xmcd_cd_user fcu on xmcd_cd_app.customer=fcu.id")
    			  ->field("fu.realname,fu.phone,xmcd_cd_app.*,fcu.name as customer_name")
    			  ->where("xmcd_cd_app.id=".$app_id)
    			  ->find();
    	return $result;
    }

    /**
	 * 根据评估师分类 获取已完成的订单
	 * @param int $appraiser_id
	 * @return array
     */
    public function getListFinish($appraiser_id, $tab, $flow){
    	$field = "fca.*, cb.brand_name, cse.series_name, cst.style_name, cu.name as customer_name, cu.phone";
    	$where = "fca.status=3 and flow=7 and (appraiser_id=".$appraiser_id." or appraiser_id is null)";
    	if($tab==0){
    		$where .= " and flow_dz=0";
    	}else{
    		$where .= " and flow_dz>".$flow;
    	}
    	$result = $this->alias('fca')
                  ->join("LEFT JOIN xmcd_cd_user cu on fca.customer=cu.id")
                  ->join("LEFT JOIN xmcd_cd_car fcc on fca.car=fcc.id")
                  ->join("LEFT JOIN xmcd_car_brand cb on fcc.brand=cb.brand_id")
                  ->join("LEFT JOIN xmcd_car_series cse on fcc.series=cse.series_id and fcc.brand=cse.brand_id")
                  ->join("LEFT JOIN xmcd_car_style cst on fcc.item=cst.style_id and fcc.brand=cst.brand_id")
                  ->field($field)
                  ->where($where)
                  ->select();
// echo "<pre><meta charset='utf-8'>";var_dump($this->getLastSql());exit;
        return $result;
    }



    //修改贷中节点方法
    public function setFlowDz($app_id, $flow_dz){
    	$data['flow_dz'] = $flow_dz;
    	return $this->where(array('id'=>$app_id))->save($data);
    }

    public function getCount($tab,$rods=null,$oid=null){
    	$field = "fca.*, cb.brand_name, cse.series_name, cst.style_name, cu.name as customer_name, cu.phone";
    	
        	//如果是管理员能看到全部订单
	        if (in_array(1,$rods)) {
	            $where = "fca.status=3 and fca.column_make=1";
	        }
	        //否则只能看到自己机构的订单
	        else{
	            $where = "fca.status=3 and fca.column_make=1 and fca.oid=".$oid;
	        }
        
    	
    	if($tab == 1){
    		$where .= " and fca.is_print=0";
    	}elseif($tab == 2){
    		$where .= " and fca.is_print=1";
    	}
    	$result = $this->alias('fca')
                  ->join("LEFT JOIN xmcd_cd_user cu on fca.customer=cu.id")
                  ->join("LEFT JOIN xmcd_cd_car fcc on fca.car=fcc.id")
                  // ->join("LEFT JOIN xmcd_cd_audit_info_dz fcaid on fca.id=fcaid.app_id")
                  ->field($field)
                  ->where($where)
                  ->count();
        return $result;
    }

    public function getList($start,$length,$tab,$rods=null,$oid=null){
    	$field = "fca.*, cu.name as customer_name, cu.phone, fcco.signcreated, fcco.loan as daizhong_loan, fcco.deadline as daizhong_deadline, fcco.rate as daizhong_rate";
        
        	//如果是管理员能看到全部订单
	        if (in_array(1,$rods)) {
	            // $where = "fca.status=3 and fca.column_make=1";
	            $where = "fca.column_make=1";
	        }
	        //否则只能看到自己机构的订单
	        else{
	            // $where = "fca.status=3 and fca.column_make=1 and fca.oid=".$oid;
	            $where = "fca.column_make=1 and fca.oid=".$oid;
	        }
       
    	if($tab == 1){
    		$where .= " and fca.is_print=0";
    	}elseif($tab == 2){
    		$where .= " and fca.is_print=1";
    	}
    	$result = $this->alias('fca')
                  ->join("LEFT JOIN xmcd_cd_user cu on fca.customer=cu.id")
                  ->join("LEFT JOIN xmcd_cd_contract_order fcco on fca.id=fcco.aid")
                  // ->join("LEFT JOIN xmcd_cd_audit_info_dz fcaid on fca.id=fcaid.app_id")
                  ->field($field)
                  ->where($where)
                  ->order("fca.id desc")
                  ->limit($start, $length)
                  ->select();
// echo "<pre><meta charset='utf-8'>";var_dump($this->getLastSql());exit;
        return $result;
    }

    public function getOid($id){
    	return $this->alias('fca')
    		   //->join("LEFT JOIN xmcd_users fu on fca.creator=fu.id")
    		   ->field("fca.oid")
    		   ->where(array('fca.id'=>$id))
    		   ->find();
    }

    public function setColumnUnMake($id){
    	$res = $this->where(array('id'=>$id))->save(array('column_make'=>2));
    	return $res;
    }

    public function setHasPrint($id){
    	$res = $this->where(array('id'=>$id))->save(array('is_print'=>1));
    	return $res;
    }

    /**
	 * 全国车贷添加报单功能
     */
    public function addInfo($data){
    	//事务开始
        $this->startTrans();
        
        //添加申请单
		$id = $this->data($data)->add();
		if (!$id) {
            return FALSE;
		}
        
        //修改客户状态
        $user_mod = new Cd_UserModel();
        $u_data['status'] = 2;
        $affect = $user_mod->where(array('id'=>$data['customer']))->save($u_data);
        
        //修改车辆状态
        if($affect){
            $car_mod = new Cd_CarModel();
            $c_data['status'] = 1;
            $affect = $car_mod->where(array('id'=>$data['car']))->save($c_data);
        }
        
        //事务结束
        if($affect){
            $this->commit();
        }else{
            $this->rollback();
            return FALSE;
        }	
               
		return $id;
    }

    public function getUserInfoNation($app_id, $creator) {
		$fields = "fcu.*";
		$select = $this->join("LEFT JOIN xmcd_cd_user fcu on xmcd_cd_app.customer=fcu.id");
		$this->field($fields);
		$select = $this->where(array('xmcd_cd_app.id'=>$app_id));
		$select = $this->where(array('xmcd_cd_app.creator'=>$creator));
		$result = $select->find();

		return $result;
	}

	public function getReportInfo($app_id) {
		$fields = "cb.brand_name, cse.series_name, cst.style_name, c.brand, c.series, c.item, u.name, u.idcard_positive_fileid, u.idcard_reverse_fileid, u.drivinglicence_fileid, c.license, u.illegal, u.litigation_car, a.amount, a.rate, a.deadline, a.creator_comment";
		$where['a.id'] = $app_id;
    	$data = $this->alias('a')
    		->join("LEFT JOIN xmcd_cd_car c on a.car=c.id")
    		->join("LEFT JOIN xmcd_cd_user u on a.customer=u.id")
            ->join("LEFT JOIN xmcd_car_brand cb on c.brand=cb.brand_id")
            ->join("LEFT JOIN xmcd_car_series cse on c.series=cse.series_id and c.brand=cse.brand_id")
            ->join("LEFT JOIN xmcd_car_style cst on c.item=cst.style_id and c.brand=cst.brand_id")
            ->field($fields)
            ->where($where)
            ->find();

        $fileModel = new FilesModel();
		$idcardfront = $fileModel->getFilePath($data['idcard_positive_fileid']);
		$idcardback = $fileModel->getFilePath($data['idcard_reverse_fileid']);
		$license = $fileModel->getFilePath($data['drivinglicence_fileid']);
		$data['idcardfront'] = $idcardfront['url'];
		$data['idcardfrontsize'] = $this->getImageSize($data['idcardfront']);
		$data['idcardback'] = $idcardback['url'];
		$data['idcardbacksize'] = $this->getImageSize($data['idcardback']);
		$data['drivinglicence'] = $license['url'];
		$data['drivinglicencesize'] = $this->getImageSize($data['drivinglicence']);
		return $data;
	}

	public function saveInfo($data, $id){
		if(!$data || !$id){
			return false;
		}
		$where['id'] = $id;
    	$res = $this->where($where)->save($data);
    	if($res)
    		return $res;
    	else
    		return false;
	}

	public function getCarEst($id){
		return $this->alias("a")
				->join("LEFT JOIN xmcd_cd_car c on a.car=c.id")
				->join("LEFT JOIN xmcd_cd_contract_order cco on cco.aid=a.id")
				->field('c.estimate_total, cco.loan as daizhong_loan, cco.rate as daizhong_rate')
				->where("a.id=".$id)
				->find();
	}

	public function getContractCustomerInfo($app_id){
		$field = "xmcd_cd_app.*, cu.*, cc.estimate_zh as result, cc.estimate_total as total,cc.brand, cc.id as car_id, cc.series, cc.item, cc.frame_id, cc.auto_id, cc.created as car_created, cco.loan as daizhong_loan, cco.rate as daizhong_rate";
		$where['xmcd_cd_app.id'] = $app_id;
		$data = $this
			   ->join("LEFT JOIN xmcd_cd_user cu on xmcd_cd_app.customer=cu.id")
			   ->join("LEFT JOIN xmcd_cd_car cc on xmcd_cd_app.car=cc.id")
			   ->join("LEFT JOIN xmcd_cd_contract_order cco on xmcd_cd_app.id=cco.aid")
			   ->field($field)
			   ->where($where)
			   ->find();
		$carBrandModel = new CarBrandModel();
		$car_info = $carBrandModel->getCarInfo($data['brand'], $data['series'], $data['item']);
		$data['brand'] = $car_info['brand_name'];
		$data['series'] = $car_info['series_name'];
		$data['item'] = $car_info['style_name'];


		return $data;
	}

	public function getNewCustomerList($where=1, $page, $pagesize=10){
		//获取图片文件根目录
        $conf = Yaf_Registry::get("config")->get('imgupload');
        if ($conf) 
            $imguploadconf = $conf->toArray();
        else 
            $imguploadconf = [];

		$field = "a.*, u.name, u.phone, ff.path, cb.brand_name, cse.series_name, cst.style_name";

		$res = $this->alias('a')
			   ->join("xmcd_cd_user u on a.customer=u.id")
			   ->join("LEFT JOIN xmcd_files ff on u.idcard_positive_fileid=ff.id")
			   ->join("LEFT JOIN xmcd_cd_car c on a.car=c.id")
               ->join("LEFT JOIN xmcd_car_brand cb on c.brand=cb.brand_id")
               ->join("LEFT JOIN xmcd_car_series cse on c.series=cse.series_id and c.brand=cse.brand_id")
               ->join("LEFT JOIN xmcd_car_style cst on c.item=cst.style_id and c.brand=cst.brand_id")
			   ->field($field)
			   ->where($where)
			   ->page($page, $pagesize)
			   ->order("id desc")
			   ->select();
		if($res){
			foreach($res as $k=>$v){
				$res[$k]['create_time'] = date("Y-m-d h:i:s", $v['created']);
				$res[$k]['url'] = $imguploadconf['host'].$v['path'];
			}
			return $res;
		}else{
			return false;
		}
	}
    
}