<?php
class Cd_UserModel extends TK_M {
	protected $tableName = 'xmcd_cd_user';
	
	/**
	 * status 字段  0 未完成提交 1 已提交，待发起 2 已发起，审核进行中 3 已完成 4 已终结
	 */
	
	public $field_arr = array(
		'type'=>array(
			'name'=>'业务类型',
			'type'=>'selector',
			'options'=>array(
				1=>'以租代购',
				2=>'车辆质押',
				3=>'车辆抵押',
			),
		),
		'name'=>array(
			'name'=>'姓名',
			'type'=>'text',
		),
		'idcard'=>array(
			'name'=>'身份证号',
			'type'=>'idcard',
		),
		'gender'=>array(
			'name'=>'业务类型',
			'type'=>'radio',
			'options'=>array(
				0=>'女',
				1=>'男',
			),
		),
		'marriage'=>array(
			'name'=>'婚姻状况',
			'type'=>'radio',
			'options'=>array(
				// 1=>'已婚有子女',
				// 2=>'已婚无子女',
				// 3=>'未婚',
				// 4=>'离婚',
				// 5=>'再婚'
				0=>'未婚',
				1=>'已婚',
			),
		),
		'phone'=>array(
			'name'=>'手机号码',
			'type'=>'text',
		),
		'email'=>array(
			'name'=>'常用邮箱',
			'type'=>'text',
		),
		'edu'=>array(
			'name'=>'最高学历',
			'type'=>'select',
			'options'=>array(
				0=>'其它',
				1=>'高中、中专',
				2=>'大专',
				3=>'本科',
				4=>'研究生以上'
			),
		),
		'hukou_type'=>array(
			'name'=>'户口性质',
			'type'=>'select',
			'options'=>array(
				1=>'本地城镇户口',
				2=>'本地农村户口',
				3=>'外地城镇户口',
				4=>'外地农村户口'
			),
		),
		'driving_years'=>array(
			'name'=>'驾龄',
			'type'=>'select',
			'options'=>array(
				0=>'0年',
				1=>'1年',
				2=>'1～3年',
				3=>'3～5年',
				4=>'5年以上',
			),
		),
		'work_type'=>array(
			'name'=>'行业类型',
			'type'=>'select',
			'options'=>array(
				0=>'其它',
				1=>'公务员',
				2=>'科研教育机构',
				3=>'金融电信电力',
				4=>'注册事务所',
				5=>'邮政交通公用',
				6=>'媒体文艺体育',
				7=>'工业商业贸易',
				
			),
		),
		'work_years'=>array(
			'name'=>'现单位工作年限',
			'type'=>'select',
			'options'=>array(
				1=>'一年以内',
				2=>'1～3年',
				3=>'3～5年',
				4=>'5年以上',
			),
		),
		'work_cate'=>array(
			'name'=>'单位性质',
			'type'=>'select',
			'options'=>array(
				0=>'其它',
				1=>'机关事业单位',
				2=>'团体企业单位',
				3=>'一般企业单位',
			),
		),
		'work_level'=>array(
			'name'=>'职位',
			'type'=>'relyselect',
			'relyon'=>'work_cate',
			'options'=>array(
				//其它
				0=>array(
					0=>'其它'
				),
				//机关事业单位
				1=>array(
					0=>'其它',
					1=>'厅局级以上',
					2=>'处级',
					3=>'科级',
					4=>'一般干部',
				),
				//团体企业单位
				2=>array(
					0=>'其它',
					1=>'正副总经理',
					2=>'部门经理',
					3=>'职员',
				),
				//一般企业单位
				3=>array(
					0=>'其它',
					1=>'正副总经理',
					2=>'部门经理',
					3=>'职员',
				),
			),
		),
		'income'=>array(
			'name'=>'个人月收入状况',
			'type'=>'select',
			'options'=>array(
				1=>'10000元以上',
				2=>'8000~10000元',
				3=>'5000~8000元',
				4=>'4000~5000元',
				5=>'3000~4000元',
				6=>'2000~3000元',
				7=>'1000~2000元',
			),
		),
		'house'=>array(
			'name'=>'个人住房情况',
			'type'=>'select',
			'options'=>array(
				1=>'完全产权房',
				2=>'按揭购房',
				3=>'经济适用房',
				4=>'租房'
			),
		),
		'isstaff'=>array(
			'name'=>'是否为我司员工',
			'type'=>'radio',
			'options'=>array(
				0=>'否',
				1=>'是',
			),
		),
		'isclient'=>array(
			'name'=>'是否为我司老客户',
			'type'=>'select',
			'options'=>array(
				0=>'非我司老客户',
				1=>'优质老客户',
				2=>'有逾期老客户',
				3=>'未结清老客户',
			),
		),
		'credit'=>array(
			'name'=>'信用记录',
			'type'=>'select',
			'options'=>array(
				
				1=>'无逾期',
				2=>'1次逾期',
				3=>'两次及以上逾期',
				0=>'无记录',
			),
		),
		'sue'=>array(
			'name'=>'诉讼执行记录',
			'type'=>'select',
			'options'=>array(
				0=>'无',
				1=>'涉执行',
				2=>'涉诉讼',
			),
		),
		'crime'=>array(
			'name'=>'犯罪记录',
			'type'=>'select',
			'options'=>array(
				0=>'无',
				1=>'涉犯罪',
			),
		),
		'interview'=>array(
			'name'=>'面谈主观印象',
			'type'=>'select',
			'options'=>array(
				0=>'0分',
				1=>'1分',
				2=>'2分',
				3=>'3分',
				4=>'4分',
				5=>'5分',
				6=>'6分',
			),
		),
	);
	
	public $score_fields = array(
		'age'=>array(
			
		),
		'gender'=>array(
			1=>1,	//男性 1分
			0=>2	//女性 2分
		),
		'marriage'=>array(
			//1=>8, //'已婚有子女',
			//2=>5, //'已婚无子女',
			//3=>3, //'未婚',
			//4=>4, //'离婚',
			//5=>5, //'再婚'
			0=>4,//未婚
			1=>8,//已婚
		),
		'edu'=>array(
			4=>5, //'研究生以上',
			3=>4, //'本科',
			2=>3, //'大专',
			1=>2, //'高中、中专',
			0=>1, //'其它'
		),
		'hukou_type'=>array(
			1=>5, //'本地城镇户口',
			2=>4, //'本地农村户口',
			3=>2, //'外地城镇户口',
			4=>1, //'外地农村户口'
		),
		'driving_years'=>array(
			4=>5, //'5年以上',
			3=>4, //'3～5年',
			2=>3, //'1～3年',
			1=>2, //'1年',
			0=>0, //'0年',
		),
		'work_type'=>array(
			1=>5, //'公务员',
			2=>4, //'科研教育机构',
			3=>4, //'金融电信电力',
			4=>3, //'注册事务所',
			5=>3, //'邮政交通公用',
			6=>3, //'媒体文艺体育',
			7=>2, //'工业商业贸易',
			0=>1, //'其它'
		),
		'work_years'=>array(
			4=>5, //'5年以上',
			3=>3, //'3～5年',
			2=>2, //'1～3年',
			1=>1, //'一年以内'
		),
		'work_level'=>array(
			//机关事业单位
			1=>array(
				1=>10, //'厅局级以上',
				2=>8, //'处级',
				3=>6, //'科级',
				4=>4, //'一般干部',
				0=>2, //'其它',
			),
			//团体企业单位
			2=>array(
				1=>10, //'正副总经理',
				2=>8, //'部门经理',
				3=>5, //'职员',
				0=>2, //'其它'
			),
			//一般企业单位
			3=>array(
				1=>10, //'正副总经理',
				2=>8, //'部门经理',
				3=>5, //'职员',
				0=>1, //'其它'
			),
			//其它
			0=>array(
				0=>1, //'其它'
			),
		),
		'house'=>array(	//@todo 待定
			1=>5, //'完全产权房',
			2=>4, //'按揭购房',
			3=>3, //'经济适用房',
			4=>2, //'租房'
		),
		'isstaff'=>array(
			1=>5,
			0=>1,
		),
		'isclient'=>array(
			1=>5, //'优质老客户',
			2=>2, //'有逾期老客户',
			3=>3, //'未结清老客户',
			0=>0, //'非我司老客户',
		),
		'credit'=>array(
			1=>4, //'无逾期',
			2=>2, //'1次逾期',
			3=>-1, //'两次及以上逾期',
			0=>0, //'无记录'
		),
		'sue'=>array(
			1=>-3, //'涉执行',
			2=>-2, //'涉诉讼',
			0=>5, //'无'
		),
		'crime'=>array(
			1=>-5, //'涉犯罪',
			0=>5, //'无'
		),
		'interview'=>array(
			6=>6, //'6分',
			5=>5, //'5分',
			4=>4, //'4分',
			3=>3, //'3分',
			2=>2, //'2分',
			1=>1, //'1分',
			0=>0, //'0分',
		)
	);
	
	public $form_fields = array(
		'basic'=>array(
			'type',	//业务类型
			'name',	//姓名
			'idcard',	//身份证号 @todo 原型中没有
			'gender',	//性别
			'birthday',	//生日
			'marriage',	//婚姻状况
			'phone',	//手机号码
			'email',	//常用邮箱
			'edu',	//最高学历
			'hukou_type',	//户口性质
			'driving_years',	//驾龄
		),
		'career'=>array(
			'work_type',	//行业类型
			'work_years',	//现单位工作年限
			'work_cate',	//现单位性质
			'work_level',	//现单位岗位
			'income',	//个人月收入状况
			'house',	//个人住房情况
		),
		'credit'=>array(
			'isstaff',	//是否为我司员工
			'isclient',	//是否为我司老客户
			'credit',	//信用记录
			'sue',	//诉讼执行记录
			'crime',	//犯罪记录
			'interview',	//面谈主观印象
		),
	);
	

	/**
	 * 添加用户
	 */
	public function addUserInfo($data) {

		$fields = $this->field_arr;
		foreach($data as $key=>$value){
			if(is_array($fields[$key]['options'])){
				if($fields[$key]['type']!='relyselect'){
					foreach($fields[$key]['options'] as $k=>$v){
						if($value == $v){
							$data[$key] = $k;
						}
					}
				}else{
					foreach($fields[$key]['options'] as $k=>$v){
						if($data['work_cate'] == $k){
							foreach($v as $kk=>$vv){
								if($value == $vv){
									$data[$key] = $kk;
								}
							}
						}
					}
				}
			}
		}
// echo "<pre><meta charset='utf-8'>";var_dump($data);exit;
		$id = $this->data($data)->add();
		return $id;
	}

	/**
	 * 修改用户信息
	 */
	public function saveUserInfo($uid, $data){
		$fields = $this->field_arr;
		foreach($data as $key=>$value){
			if(is_array($fields[$key]['options'])){
				if($fields[$key]['type']!='relyselect'){
					foreach($fields[$key]['options'] as $k=>$v){
						if($value == $v){
							$data[$key] = $k;
						}
					}
				}else{
					foreach($fields[$key]['options'] as $k=>$v){
						if($data['work_cate'] == $k){
							foreach($v as $kk=>$vv){
								if($value == $vv){
									$data[$key] = $kk;
								}
							}
						}
					}
				}
			}
		}
		$where['id'] = $uid;
		$id = $this->where($where)->save($data);
		if($id){
			return $id;
		}else{
			return false;
		}
	}
	
	/**
	 * 确认保存方法 计算用户信用分数、等级，修改用户记录状态
	 * @param int $id
	 * @return boolean
	 */
	public function confirmSave($id) {
		$user = $this->where(array('id'=>$id))->find();
		if (!$user) {
			return FALSE;
		}
		
		$score = 0;
		$rank = 0;
		foreach ($this->score_fields as $field=>$options) {
			
			switch ($field) {
				case 'age':
					$age = $this->getAge($user['birthday']);
					if ($age >= 18 && $age <= 22) {
						$score += 2;
					} else if ($age >= 23 && $age <= 34) {
						$score += 4;
					} else if ($age >= 35 && $age <= 40) {
						$score += 6;
					} else if ($age >= 41 && $age <= 60) {
						$score += 4;
					} else {
						$score += 2;
					}
					break;
				case 'work_level':
					if (isset($user[$field])) {
						$score += $options[$user['work_cate']][$user['work_level']];
					}
					break;
				default:
					if (isset($user[$field])) {
						$score += $options[$user[$field]];
					}
			}
		}
		//根据得分得出等级 A 90以上 B 80-89 C 70-79 D 60-69 E 50-59 F 50以下
		if ($score >= 90) {
			$rank = 6;
		} else if ($score >= 80 && $score <= 89) {
			$rank = 5;
		} else if ($score >= 70 && $score <= 79) {
			$rank = 4;
		} else if ($score >= 60 && $score <= 69) {
			$rank = 3;
		} else if ($score >= 50 && $score <= 59) {
			$rank = 2;
		} else {
			$rank = 1;
		}
		$data = array(
			'status'=>1,	//已提交 待发起
			'score'=>$score,
			'rank'=>$rank,
		);
		$new_id = $this->where(array('id'=>$id))->save($data);
		$data['new_id'] = $new_id;
		return $data;
	}
	
	/**
	 * 获取指定的用户记录
	 * @param int $id
	 * @return array
	 */
	public function getInfo($id, $fields='*') {
		$data = $this->where(array('id'=>$id))->field($fields)->find();
// Utils_Tool::fileLog(var_export($data,1));
		$work_cate = $data['work_cate'];
		$fields = $this->field_arr;
		//field_arr = ['isclient'=>[name=>'', options=[]]]
		$ret = [];
		foreach($fields as $key=>$value){
			if(is_array($value['options'])){
				if($value['type']!='relyselect'){
					foreach($value['options'] as $k=>$v){
						if($data[$key] == $k){
							$data[$key] = $v;
							// $ret[$key] = $v;
							break;
						}
					}
				}else{
					foreach($value['options'] as $kk=>$vv){
						if($work_cate == $kk){
							foreach($vv as $kkk=>$vvv){
								if($data[$key] == $kkk){
									$data[$key] = $vvv;
									// $ret[$key] = $vvv;
									break;
								}
							}
						}
					}
				}
			}
		}

		$fileModel = new FilesModel();
		$idcardfront = $fileModel->getFilePath($data['idcard_positive_fileid']);
		$idcardback = $fileModel->getFilePath($data['idcard_reverse_fileid']);
		$drivinglicence = $fileModel->getFilePath($data['drivinglicence_fileid']);
		$data['idcardfront'] = $idcardfront['url'];
		$data['idcardfrontsize'] = $this->getImageSize($data['idcardfront']);
		$data['idcardback'] = $idcardback['url'];
		$data['idcardbacksize'] = $this->getImageSize($data['idcardback']);
		$data['drivinglicence'] = $drivinglicence['url'];
		$data['drivinglicencesize'] = $this->getImageSize($data['drivinglicence']);
// Utils_Tool::fileLog(var_export($data,1));
		return $data;
		// return $ret;
	}
	
	/**
	 * 获取业务员创建的用户记录数量
	 * @param int $creator
	 * @param string $status
	 * @return number
	 */
	public function getCntByCreator($creator, $status='') {
		$select = $this->where(array('creator'=>$creator));
		if ($status) {
			if (is_array($status)) {
				$select->where(array('status'=>array('in', $status)));
			} else {
				$select->where(array('status'=>$status));
			}
		}
		return $select->count();
	}
	
	/**
	 * 获取业务员自己创建的用户列表
	 * @param int $creator
	 * @param int $page
	 * @param string $status
	 * @param int $pagesize
	 * @return array
	 */
	public function getListByCreator($creator, $page=1, $status='', $pagesize=20, $fields='*') {
            $select = $this;
            
            if(!empty($creator)){
                $select = $this->where(array('creator'=>$creator));
            }
            
            if(!empty($status)){
                if (is_array($status)) {
                        $select->where(array('status'=>array('in', $status)));
                } else {
                        $select->where(array('status'=>$status));
                }
            }
            
            return $select->field($fields)->order('id desc')->page($page, $pagesize)->select();
	}

	/**
	 * 获取业务员自己创建的用户列表 根据选项卡分类
	 * @param int $creator
	 * @param int $tab
	 * @param int $page
	 * @return array
	 */
	public function getListByCreatorAndTab($creator, $tab, $page=1, $pagesize=20) {
		switch ($tab) {
			case 0:
				$status = 1;
				break;
			case 1:
				$status = 2;
				break;
			case 2:
				$status = array(3,4);
				break;
		}
		$fields = "id, name, created as time, phone, marriage as marry, birthday";
		$select = $this->where(array('creator'=>$creator));
		if ($status) {
			if (is_array($status)) {
				$select->where(array('status'=>array('in', $status)));
			} else {
				$select->where(array('status'=>$status));
			}
		}
		$result = $select->field($fields)->order('id desc')->page($page, $pagesize)->select();
		$values = $this->field_arr;
		foreach($result as $key=>$value){
			foreach($values['marriage']['options'] as $k=>$v){
				if($value['marry'] == $k){
					$result[$key]['marry'] = $v;
				}
			}
		}

		return $result;
	}
	
	/**
	 * 根据生日计算年龄
	 * @param string $birthday
	 * @return number
	 */
	public function getAge($birthday) {

		$nowyear = date('Y');
		$nowmonth = date('m');
		$nowday = date('d');
		
		list ($year, $month, $day) = explode('-', $birthday);
		if ($nowyear < $year) {
			return FALSE;
		}
		
		$age = 0;
		if ($nowmonth > $month) {
			$age = $nowyear - $year;
		} else {
			if ($nowday > $day) {
				$age = $nowyear - $year;
			} else {
				$age = $nowyear - $year - 1;
			}
		}
		return $age;
	}
	
	/**
	 * 将用户设置为已经发起状态
	 * @param int $user
	 * @return boolean
	 */
	public function setApp($creator, $user) {
		return $this->where(array('id'=>$user, 'creator'=>$creator, 'status'=>1))->save(array('status'=>2));
	}

	/**
	 * 将用户设置为终结状态
	 * @param int $user
	 * @return boolean
	 */
	public function setOver($creator, $user) {
		return $this->where(array('id'=>$user, 'creator'=>$creator))->save(array('status'=>4));
	}

	/**
	 * 将用户设置为未发起状态
	 * @param int $user
	 * @return boolean
	 */
	public function setBack($creator, $user) {
		return $this->where(array('id'=>$user, 'creator'=>$creator))->save(array('status'=>1));
	}

	/**
	 * 将用户设置为完成状态
	 * @param int $user
	 * @return boolean
	 */
	public function setOk($creator, $user) {
		return $this->where(array('id'=>$user, 'creator'=>$creator))->save(array('status'=>3));
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
    
        /**
        * 获取客户数量
        * @return int
        */
        public function getUserCount($where=null){
            $this->join("LEFT JOIN xmcd_cd_app ca on xmcd_cd_user.id=ca.customer");
        
            if(!empty($where)){
                $this->where($where);
            }
            
            return $this->count();
        }


        /**
        * 获取用户列表
        * @param int $page
        * @param int $pagesize
        * @return array
        */
        public function getUserList($page, $pagesize, $fields='*',$where=null) {
            $select = $this;

            $select->field("xmcd_cd_user.*");
            
            //$select->join("LEFT JOIN xmcd_cd_app ca on xmcd_cd_user.id=ca.customer");
            
            if(!empty($where)){
                $select->where($where);
            }
            
            $result = $select->order('xmcd_cd_user.id desc')->limit($page,$pagesize)->select();

            return $result;
            
        }

        //添加客户信息
        public function addUser($data){
        	if(!$data){
        		return false;
        	}

        	return $this->data($data)->add();
        }

        /**
		 * 删除用户信息
	     */
	    public function deleteUserInfo($id){
	    	$where['id'] = $id;
	    	$res = $this->where($where)->delete();
	    	return $res;
	    }

	    /**
	 	 * 编辑客户信息
	     */
	    public function editUser($data, $user_id){
	    	if(!$data || !$user_id){
	    		return false;
	    	}
	    	$where['id'] = $user_id;
	    	$res = $this->where($where)->save($data);
	    	return $res;
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
    
}