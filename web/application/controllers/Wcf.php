<?php 
class WcfController extends Yaf_Controller_Abstract {
	private $_tpl;
    private $_contractModel;
	
	
	public function init() {
		//快速评房
		
		$this->_req = $this->getRequest();
		$this->_tpl = $this->getView();
        $this->_contractModel = new ContractModel();
		$this->_quickgethouse = new QuickGetHouseModel();
		$this->_quickgetarea = new QuickGetAreaModel();
		$this->_operationModel = new OperationModel();
		$this->session=Yaf_Session::getInstance();
		$this->redis = new Utils_Redis();
	}
	//是否世联评估人员
	public function assessmentRole(){
		$userId = Session_AdminFengkong::instance()->getUid();
		$rm = new RoleModel();
        $roles = $rm->getUserRoles($userId);
		
        if($roles){
            $rods = array();
            foreach($roles as $k=>$v){
                $rid = $v['id'];
                $rods[] = $rid;
            }
            if($_SERVER['HTTP_HOST'] != "fk.yianjinrong.com" && $_SERVER['HTTP_HOST'] != "prefk.yianjinrong.com" && $_SERVER
                ['HTTP_HOST'] != "yad.yianjinrong.com"){
                $roleId = '49';//世联评估
            }else{
                $roleId = '45';//世联评估
			}
			$trileRoleid = '10';//初审
            if(in_array($roleId,$rods) || in_array($trileRoleid,$rods)){
                return true;
            }
        }
        return false;
    }
	
	
	
	//查询用户是否有管理员权限
    private function _adminRole(){
        $user = Session_AdminFengkong::instance();
        $rm = new RoleModel();
        $roles = $rm->getUserRoles($user->uid);
        if ($roles) {
            $rods = array();
            foreach ($roles as $k => $v) {
                $rid = $v['id'];
                $rods[] = $rid;
            }
            $roleId = '1'; //管理员角色ID
            if (in_array($roleId, $rods)) {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
	
	public function indexAction() {
		
		$id = $this->assessmentRole();
		if(!$id){
			die('access die');
		}
	}
	
	//查询城市
	public function getCityajaxAction(){
		$data['error'] = 1;
		$data['errorMsg'] = '';
		$arr = $this->_quickgetarea->getCityall();
		$data['errorMsg'] = $arr;
		if(empty($arr)){
			$data['error'] = 0;
			$data['errorMsg'] = '暂无可查询城市！';
		}
		
		echo json_encode($data);
		exit;
	}
	
	//查询城市地区
	public function getCityAreaajaxAction(){
		$data['error'] = 1;
		$data['errorMsg'] = '';
		$city = (int)$this->getRequest()->getPost('city', 0);
		$arr = $this->_quickgetarea->getArea($city);
		$data['errorMsg'] = $arr;
		if(empty($arr)){
			$data['error'] = 0;
			$data['errorMsg'] = '暂无可查询城市！';
		}
		
		echo json_encode($data);
		exit;
	}
	
	
	public function gethouseAjaxAction(){
		$userId = Session_AdminFengkong::instance()->getUid();
		$id = $this->assessmentRole();
		if(!$id){
			Utils_Output::ajaxReturn(array(
				'error' => 2,
				'errorMsg' => "参数错误"
			));
			exit;
		}
		/* $all = $this->_limitSameUserMinuteFrequency();
		if($all){
			Utils_Output::ajaxReturn(array(
				'error' => 2,
				'errorMsg' => "查询次数过多！"
			));
			exit;
		} */
		$allname = htmlspecialchars($this->getRequest()->getPost('allname', ''));
		$city_id = (int)$this->getRequest()->getPost('city', 0);
		$construction_id = (int)$this->getRequest()->getPost('construction_id', 0);
		$build_id = (int)$this->getRequest()->getPost('build_id', 0);
		$house_id = (int)$this->getRequest()->getPost('house_id', 0);
		$measure_area = (float)$this->getRequest()->getPost('measure_area', 0);
		
		
		//参数是否为空
		if(!$city_id  || !$construction_id || !$build_id || !$house_id || !$measure_area){
			Utils_Output::ajaxReturn(array(
				'error' => 2,
				'errorMsg' => "参数错误"
			));
			exit;
		}
		$status = $userId.$house_id.$measure_area;
		if($this->redis->get($status)){
			$arr = $this->redis->get($status);
		}else{
			$arr = $this->_quickgethouse->getAutoPrice($city_id,$construction_id,$build_id,$house_id,$measure_area);//city,c,b,h,
			if(empty($arr)){
				$data['error'] = 0;
				$data['errorMsg'] = '暂无可查询城市！';
				echo json_encode($data);
				exit;
			}
			$arr_house = $arr;
			$arr_house['user_id'] =  $userId;
			$arr_house['provinceid'] =  0;
			$arr_house['city_id'] = $city_id;
			$arr_house['service_id'] = 0;
			$arr_house['type'] = 0;
			$arr_house['area_id'] = 0;
			$arr_house['construction_id'] = $construction_id;
			$arr_house['build_id'] = $build_id;
			$arr_house['house_id'] = $house_id;
			$arr_house['measure_area'] = $measure_area;
			$arr_house['property_type'] = 0;
			$arr_house['planning_purposes'] = 0;
			$arr_house['service_network'] = 0;
			$arr_house['housename'] = $allname;
			$arr_house['order_id'] = "YAD".date('YmdHis',time()).rand(99,999);
			$arr_house['type_status'] = 1;
			$arr_house['created'] = time();
			$aa = $this->_quickgetarea->saveGetHouse($arr_house,$faile);
			$arr['allname'] = $allname;
		
			$arr['getreport_id'] = $status;
			$this->redis->set($status,$arr,30*24*3600);
		}
		
		
		
		$data['error'] = 1;
		$data['errorMsg'] = $arr;
		$msg = "userId:".$userId;
		foreach($arr as $key=>$kaa){
			$msg .= "; $key:$kaa";
		}
		Utils_Tool::log('wcf',$msg);
		echo json_encode($data);
		exit;
	}
	
	//评估报告
	public function evaluatunitedAction(){
		$getreport_id = $this->getRequest()->get('getreport_id', '');
		//$getreport_id = 11551463167.98;
		if(!$getreport_id){
			die("access");
		}
		//$result = $this->_quickgetarea->gethousebyone(140);
		$result = $this->redis->get($getreport_id);
		if(!$result){
			die("access");
		}
		//print_r($result);
		$this->_tpl->assign('result', $result);
		$this->_tpl->assign('getreport_id', $getreport_id);
	}
	
	public function getevaluatunitedajaxAction(){
		$getreport_id = htmlspecialchars($this->getRequest()->getPost('getreport_id', ""));
	}
	
	/**
     * _limitSameIpFrequency
     * 限制用户提交次数
     * @access private
     * @return void
     */
    private function _limitSameUserFrequency() {
        $userId = Session_AdminReport::instance()->getUid();
		//$ip = Util_Tool::getRealIP();
        $key = "qh".$userId . date("Ymd", time());
        $cache = new Cache_Memcache(20);
        //$cache->remove($key);
        $count = $cache->load($key);
        if (empty($count)) {
            $count = 0;
        }
        $count++;
		$cache->save($key, $count);
        if ($count > 3) {
            return $count;
        }
        return 0;
    }
	//限制24小时内只能提交50次
	private function _limitSameUserMinuteFrequency() {
		//$userId = 2;
        $userId = Session_AdminReport::instance()->getUid();
		//$ip = Util_Tool::getRealIP();
        $key = "qhminute".$userId . date("Ymd", time());
        $cache = new Cache_Memcache(24*3600);
        //$cache->remove($key);
       $count = $cache->load($key);
		
        if (empty($count)) {
            $count = 0;
        }
        $count++;
		$cache->save($key, $count);
        if ($count > 50) {
           return $count;
        }
		return 0;
    }
	
	
	public function getProvinceajaxAction(){
		$ip = 19;
		//$this->redis->set($ip,1,10);
		echo $c = $this->redis->get($ip);
		echo "<br/>";
		$a = $this->_limitSameUserFrequency();
		
		/* if($a==4){
			$this->redis->set($ip,1,10);
		} */
		echo $a;
		echo "<br/>";
		$b = $this->_limitSameUserMinuteFrequency();
		echo $b ;
		echo "<br/>";
		echo 2222;die;
		$arr = $this->_quickgetarea->getProvince();
		echo json_encode($arr);
		return false;
	}
	
	public function getCityajaxaaAction(){
		$province_id = (int) $this->_req->getPost('province_id', 0);
		$arr = $this->_quickgetarea->getCity($province_id);
		echo json_encode($arr);
		return false;
	}
	
	public function getAreaajaxAction(){
		
		$arr = $this->_quickgetarea->getArea(3);
		 echo json_encode($arr);
		
		 return false;
	}
	
	public function testzkaction(){

	}
	
	public function testajaxaction(){
			$img = $this->getRequest()->getPost('img', "");
			$config = Yaf_Application::app()->getConfig()->imgupload->toArray();
            $dir = 'fk/wcf/' . date('Ymd');
			$upload = new Utils_Upload($dir, $config['root']);
			
			// requires php5
			// define('UPLOAD_DIR', 'images/');
			$img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$dir = $config['root'].$dir;
			$file = $dir.'/' . uniqid() . '.jpg';
			$success = file_put_contents($file, $data);
			
			if($success){
				$path = str_replace('\\', '/', $file);
				
				$filemodel = new FilesModel();
				$id = $filemodel->addFileThumb($path, '');
				if($id){
					$sdata['error'] = 1;
					$sdata['errorMsg'] = $id;
					echo json_encode($sdata);
					exit;
					
				}else{
					$sdata['error'] = 2;
					$sdata['errorMsg'] = '操作失败';
					echo json_encode($sdata);
					exit;
				}
				
			}else{
				$sdata['error'] = 0;
				$sdata['errorMsg'] = '操作失败';
				echo json_encode($sdata);
				exit;
			}
			
			
			 //print $success ? $file : 'Unable to save the file.';
			 return false;
	}
	
	
	public function saveimgAction(){
		$id = (int)$this->getRequest()->get('id', 0);
		if(!$id){
			die();
		}
		//echo $id;die;
		$filemodel = new FilesModel();
		$rusult = $filemodel->getFilePath($id);
		if($rusult){
			
			$this->downloadFile($rusult['path']);
		}
		return false;
	}
	
	
	
	public function downloadFile( $fullPath ){ 
		file_exists($fullPath);

	  // Must be fresh start 
	  if( headers_sent() ) 
		die('Headers Sent'); 

	  // Required for some browsers 
	  if(ini_get('zlib.output_compression')) 
		ini_set('zlib.output_compression', 'Off'); 

	  // File Exists? 
	  if( file_exists($fullPath) ){ 
		
		// Parse Info / Get Extension 
		$fsize = filesize($fullPath); 
		$path_parts = pathinfo($fullPath); 
		$ext = strtolower($path_parts["extension"]); 
		
		// Determine Content Type 
		switch ($ext) { 
		  case "pdf": $ctype="application/pdf"; break; 
		  case "exe": $ctype="application/octet-stream"; break; 
		  case "zip": $ctype="application/zip"; break; 
		  case "doc": $ctype="application/msword"; break; 
		  case "xls": $ctype="application/vnd.ms-excel"; break; 
		  case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
		  case "gif": $ctype="image/gif"; break; 
		  case "png": $ctype="image/png"; break; 
		  case "jpeg": 
		  case "jpg": $ctype="image/jpg"; break; 
		  default: $ctype="application/force-download"; 
		} 

		header("Pragma: public"); // required 
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Cache-Control: private",false); // required for certain browsers 
		header("Content-Type: $ctype"); 
		header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" ); 
		header("Content-Transfer-Encoding: binary"); 
		header("Content-Length: ".$fsize); 
		ob_clean(); 
		flush(); 
		readfile( $fullPath ); 

	  } else 
		die('File Not Found'); 

	}
	
	public function reportlistAction(){
		
	}
	
	
	 public function getreportListAction()
    {
		$start = (int)$this->_req->get('start', 0);
        $offset = (int)$this->_req->get('length', 0);

        //搜索
        $user_name = htmlspecialchars(trim($this->_req->get('user_name', '')));
        $user_addr = htmlspecialchars(trim($this->_req->get('user_addr', '')));
        $startTime = htmlspecialchars(trim($this->_req->get('start_time', '')));
        $endTime = htmlspecialchars(trim($this->_req->get('end_time', '')));
		$type_status = (int)trim($this->_req->get('type_status', 0));

        $where = array();
        if ($startTime) {
            $startTime = $startTime . ' 00:00:00';
            $where['start_time'] = (int)strtotime($startTime);
        }
        if ($endTime) {
            $endTime = $endTime . '23:59:59';
            $where['end_time'] = (int)strtotime($endTime);
        }

        if (!empty($user_name)) {
            $where['username'] =array('like',"%".$user_name."%");
        }

        if (!empty($user_addr)) {
            $where['housename'] = array('like',"%".$user_addr."%");
        }
		
	
		$where['type_status'] = $type_status;
		

        if ($offset == 0)
            $offset = 10;


       
		
        //管理员可以看到所有订单，其他机构的风控leader只能看到配置中有权限看到的机构的单子
        if ($this->_adminRole()) {
			
        } else {
           $output = array(
				'draw' => $this->_req->get('draw') ? intval($this->_req->get('draw')) : 0,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => "",
			);

			echo json_encode($output);
			exit;
        }
        $total = $this->_quickgetarea->getreportlist($start, $offset, $where, 1);
        $apps =  $this->_quickgetarea->getreportlist($start, $offset, $where,"");
		$output = array(
            'draw' => $this->_req->get('draw') ? intval($this->_req->get('draw')) : 0,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $apps,
        );

        echo json_encode($output);
        return FALSE;
    }
	
	
	//导出世联评估列表excel
    public function exportreportListAction()
    {
       $user_name = htmlspecialchars(trim($this->_req->get('user_name', '')));
        $user_addr = htmlspecialchars(trim($this->_req->get('user_addr', '')));
        $startTime = htmlspecialchars(trim($this->_req->get('start_time', '')));
        $endTime = htmlspecialchars(trim($this->_req->get('end_time', '')));
		$type_status = (int)trim($this->_req->get('type_status', 0));
        //添加导出日志
        $userId = Session_AdminFengkong::instance()->getUid();
        $logInfo = array();
        $logInfo['user_id'] = $userId;
        $logInfo['model'] = "世联评估";
        $logInfo['option'] = "导出excel";
        $logInfo['old_data'] = '';
        $this->_operationModel->add($logInfo);
        $where = array();
        if ($startTime) {
            $startTime = $startTime . ' 00:00:00';
            $where['start_time'] = (int)strtotime($startTime);
        }
        if ($endTime) {
            $endTime = $endTime . '23:59:59';
            $where['end_time'] = (int)strtotime($endTime);
        }

        if (!empty($user_name)) {
            $where['username'] =array('like',"%".$user_name."%");
        }

        if (!empty($user_addr)) {
            $where['housename'] = array('like',"%".$user_addr."%");
        }
		
		$where['type_status'] = $type_status;
		
        //开始excel导出
        $title = array('序号', '评房人', '评房电话', '面积(m2)', '单价(元)', '总价(元)', '地址','来源', '评房时间');
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=世联接口列表.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        //先导出Excel表头
        if (!empty($title)) {
            foreach ($title as $k => $v) {
                $title[$k] = iconv("UTF-8", "GBK", $v);
            }
            $title = implode("\t", $title);
            echo "$title\n";
        }
		

        //大数据分页导出
        $pageNum = 1000;
        $startPage = 0;
        $riskListtotal = $this->_quickgetarea->getreportlist($start, $offset, $where, 1);
        $totalPage = ceil($riskListtotal / $pageNum); //上舍，取整
        $startNum = 1;


        while ($startNum <= $totalPage) {
            if ($riskListtotal > 0) {
                $riskList = $this->_quickgetarea->getreportlist($start, $offset, $where, "");
                $startNum++;
                $startPage += 1000;

                $exportData = array();
                foreach ($riskList as $v) {
					if($v['type_status'] == 0){
						$v['type_status'] = "微信评房";
					}else{
						$v['type_status'] = "pc世联";
					}
					$exportData[] = array(
                        'id' => $v['id'],
                        'username' => $v['username'],
                        'phone' => $v['phone'],

                        'measure_area' => $v['measure_area'],
                        'unitprice' => $v['unitprice'],
                        'amount' => $v['amount'],

                        'housename' => $v['housename'],
						'type_status' => $v['type_status'],
                        'created' => $v['created']
                    );
                }

                if (!empty($exportData)) {
                    foreach ($exportData as $key => $val) {
                        foreach ($val as $ck => $cv) {
                            $exportData[$key][$ck] = iconv("UTF-8", "GBK", $cv);
                        }
                        $exportData[$key] = implode("\t", $exportData[$key]);
                    }
                    echo implode("\n", $exportData);
                }
                echo "\n";
            }
        }
        exit;
    }
	
	/* public function getredisAction(){
		$this->redis->set('aa',1111);
		$this->redis->set('1111',222);
		$redis = $this->redis->redis();
		$id = $redis->keys('*');
		foreach($id as $ke){
			echo $this->redis->get($ke);
			echo "<br/>";
		}
		print_r($id);
		exit;
	} */

}