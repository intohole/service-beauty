<?php
/**
 * Created by PhpStorm.
 * User: cxw
 * Date: 16-6-14
 * Time: 下午2:55
 */
class OverviewModel {
    private $application;

    public function __construct() {
        $this->application = new App_ApplicationModel();
    }
//    public function getOverviewList($start,$offset,$where,$status='',$type=''){
//        $select = $this->application;
//        $allwhere = array();
//        $a = $select->join("xmcd_org as a on xmcd_application.org_id = a.id")->where()->select();
////        print_r($a);exit;
//    }
    public function getOverviewList($startPage,$pageNum,$where,$status="",$type='') {
        $fields = "xmcd_application.* ";
        $select = $this->application;
//var_dump($where);exit;
        $simpwhere=array();
        //满足一个条件
        if (isset($where['assigner'])) {//初审分配条件-1级
            $simpwhere['assigner']=$where['assigner'];
        }

        if(isset($where['risker'])){//下户分配条件-1级
            $simpwhere['risker']=$where['risker'];
        }
//        if(isset($where['org_id'])){//机构条件-1级
//            //$simpwhere['org_id']=$where['org_id'];
//            $simpwhere['org_id']=array('in',$where['org_id']);
//        }

        if(isset($where['borrower'])){
            $simpwhere['borrower']=array("like",'%'.$where['borrower'].'%');
        }
        if(isset($where['org_id'])){
            $simpwhere['org_id'] = $where['org_id'];
        }
		if(isset($where['area_id'])){
            $simpwhere['area_id'] = $where['area_id'];
        }
		if(isset($where['flow'])){
            $simpwhere['flow'] = $where['flow'];
        }
        if(isset($where['id'])){
            $simpwhere['id'] = $where['id'];
//            var_dump($simpwhere);exit;
        }
        if($where['start_time'] && $where['end_time']){
            $simpwhere['created'] = array(array('gt',$where['start_time']),array('lt',$where['end_time']));
        }elseif($where['start_time'] && !$where['end_time']){
            $simpwhere['created'] = array('gt',$where['start_time']);
        }elseif(!$where['start_time'] && $where['end_time']){
            $simpwhere['created'] = array('lt',$where['end_time']);
        }
        /* if(isset($where['created'])){
                $simpwhere['created']=$where['created'];
        } */
        if(isset($where['report_user_name'])){
//            $select->field('xmcd_application.*')->table('xmcd_application');

            if(isset($where['creator'])){//后台报单
                $simpwhere['report_id']=array('exp','is NULL');
                $simpwhere['creator']=$where['creator'];
                if($type==0){
                    $select->where($simpwhere);
                    $type = 1;
                }else{
                    $select->union(array('field'=>'xmcd_application.*','table'=>'xmcd_application','where'=>$simpwhere));
                }
                unset($simpwhere['creator']);
                unset($simpwhere['report_id']);
            }

            if(isset($where['report'])){//微信房抵
                $simpwhere['report_id']=$where['report'];
                $simpwhere['type']=array('exp','is NULL');
                if($type==0){
                    $select->where($simpwhere);
                    $type = 1;
                }else{
                    $select->union(array('field'=>'xmcd_application.*','table'=>'xmcd_application','where'=>$simpwhere));
                }
                unset($simpwhere['report_id']);
                unset($simpwhere['type']);
            }

            if(isset($where['funded'])){//微信垫资
                $simpwhere['report_id']=$where['funded'];
                $simpwhere['type']=2;
                if($type==0){
                    $select->where($simpwhere);
                    $type = 1;
                }else{
                    $select->union(array('field'=>'xmcd_application.*','table'=>'xmcd_application','where'=>$simpwhere));
                }
                unset($simpwhere['report_id']);
                unset($simpwhere['type']);
            }
            $subQuery = $select->buildSql();
//var_dump($subQuery);exit;
            if($type==0){
                //搜索报单人且无此报单人
                if($status==1){
                    $apps = 0;
                }else{
                    $apps = "";
                }
                return $apps;

            }else{
                if($status==1){
                    $apps = $select
                        ->table($subQuery.' a')
                        //->where($simpwhere)
                        ->count();
                }else{
                    //$select->join("LEFT JOIN xmcd_org o on xmcd_application.org_id=o.id");
                    $apps = $select->table($subQuery.' a')
                        ->field('a.*')
                        //->where($simpwhere)
//                            ->join("LEFT JOIN xmcd_org o on a.org_id=o.id")
                        ->order('id desc')
                        ->limit($startPage,$pageNum)
                        ->select();
                }
//                var_dump($apps);exit;
                return $this->getAppsInfo($apps);
            }
        }

        $select = $this->application->field('xmcd_application.*')->where($simpwhere)->order('id desc');
//
        if($status==1){
            $apps =  $select->count();
//            echo $apps;exit;
//            echo '000';
//            print_r($this->application->getLastSql());exit;
        }else{
            //echo $apps = $select->join("LEFT JOIN xmcd_org o on a.org_id=o.id")->limit($startPage,$pageNum)->buildSql();
                $apps = $select->limit($startPage,$pageNum)->select();

        }

        return $this->getAppsInfo($apps);
    }
    private function getAppsInfo($apps, $node='') {
        $wf = new WorkflowModel();
        //获取列表的银行尾款、批贷金额、批贷期限信息
//        $entities = $wf->getEntitiesByEnames([
//            'E_NODE2_F1_RATE'	//约定利率
//        ]);
        $user = new AdminUserModel();
        $bd_user = new ReportUserModel();
        $org = new OrganizeModel();
        $area = new AddressrateModel();
		$areaflow = new AreaFlowModel();
        $users = [];
        foreach ($apps as $k=>$app) {

            //每次循环前将报单用户信息置空,防止之后的机构查询条件不正确
            $reportUserInfo = null;

            $apps[$k]['flow_name'] = $wf->getNode($app['flow'])['name'];
            $apps[$k]['created_date'] = date('Y-m-d H:i:s', $app['created']);
            if(!empty($app['report_id'])){
                //垫资报单从垫资表中查
                if($app['type'] == 2){
                    $fun = new FundedRecordModel();
                    $funInfo = $fun->get($app['report_id']);
                    $reportUserInfo = $bd_user->getUserInfoByUid($funInfo['report_user_id']);
                    //$area_name = $area->getAreaName($funInfo['area_id']);
                    //$apps[$k]['area_name'] = $area_name;
                    $apps[$k]['repeat_status'] = $funInfo['repeat_status'];
                    $apps[$k]['creator_name'] = $reportUserInfo['username'];
                    $apps[$k]['creator_phone'] = $reportUserInfo['phone'];
                }
                //房贷报单从房贷表中查
                else{
                    $rpcm = new ReportRecordModel();
                    $reportInfo = $rpcm->get($app['report_id']);
//                    var_dump($reportInfo);exit;
                    $reportUserInfo = $bd_user->getUserInfoByUid($reportInfo['report_user_id']);
                    //$area_name = $area->getAreaName($reportInfo['area_id']);
                    //$apps[$k]['area_name'] = $area_name;
                    $apps[$k]['repeat_status'] = $reportInfo['repeat_status'];

                    $apps[$k]['creator_name'] = $reportUserInfo['username'];
                    $apps[$k]['creator_phone'] = $reportUserInfo['phone'];
                }
            }else{
                $userInfo = $user->getUserInfoByUid($app['creator']);
                //$area_name = $area->getAreaName($app['area_id']);
                //$apps[$k]['area_name'] = $area_name;
                $apps[$k]['creator_name'] = $userInfo['realname'];
                $apps[$k]['creator_phone'] = $userInfo['phone'];
            }
			
			if ($app['area_id']) {
                $area_name = $areaflow->getUserbyareaid($app['area_id']);
                $apps[$k]['area_name'] = $area_name['area_name'];
            } else {
                $apps[$k]['area_name'] = '';
            }

            if ($app['assigner'] > 0) {
                $userInfo = $user->getUserInfoByUid($app['assigner']);
                $apps[$k]['assigner_name'] = $userInfo['realname'];
            } else {
                $apps[$k]['assigner_name'] = '未分配';
            }

            if ($app['risker'] > 0) {
                $userInfo = $user->getUserInfoByUid($app['risker']);
                $apps[$k]['risker_name'] = $userInfo['realname'];
            } else {
                $apps[$k]['risker_name'] = '未分配';
            }

            if($app['order_status'] == 1){
                $apps[$k]['order_status'] = "是";
            }else{
                $apps[$k]['order_status'] = '否';
            }
            if($app['notarizer'] > 0 ){
                $notarizer_name = $user->getUserInfoByUid($app['notarizer']);
                $apps[$k]['notarizer_name'] = $notarizer_name['realname'];
            }else{
                $apps[$k]['notarizer_name'] = '未分配';
            }
            $apps[$k]['deal_rate'] = $app[$k]['seller_rate'];
            if (!isset($apps[$k]['deal_rate'])) {
                $apps[$k]['deal_rate'] = '';
            }

            //查询业务员所属机构
            $oid = 0;
            $report_oid = 0;

            //如果有报单人直接用报单人的机构
            if(!empty($reportUserInfo) && !empty($reportUserInfo['oid'])){
                $report_oid = $reportUserInfo['oid'];
            }
            //如果此订单无报单人,先按订单中的org_id字段查询,如果这个也没有,就用录单人的机构
            else{
                if($app['org_id']){
                    $oid = $app['org_id'];
                }
                else if(empty($app['org_id']) && !empty($app['creator'])){
                    $oid = $user->getOidByUid($app['creator']);
                }
            }

            //根据不同情况填写订单所属机构
            if(!empty($report_oid)){
				$org_info = $org->getOrganizeInfo($report_oid);
				$apps[$k]['clerk'] = $org_info['own_clerk'];
                $apps[$k]['org_name'] = $reportUserInfo['o_name'];
				if($reportUserInfo['o_insti'] != ''){
                    $apps[$k]['o_insti'] = $reportUserInfo['o_insti'];
                }else{
                    $apps[$k]['o_insti'] = '';
                }
            }
            else if(empty($report_oid) && !empty($oid)){
                $orgInfo = $org->getOrganizeInfo($oid);
				$apps[$k]['clerk'] = $orgInfo['own_clerk'];
                $apps[$k]['org_name'] = $orgInfo['name'];
				if($orgInfo['insti_attr']!= ''){
                    $apps[$k]['o_insti'] = $orgInfo['insti_attr'];
                }else{
                    $apps[$k]['o_insti'] = '';
                }
            }
            else{
                $apps[$k]['org_name'] = '';
				$apps[$k]['org_name'] = '';
				$apps[$k]['clerk'] = '';
            }
            // 查询报单地区


//            if($app['creator']){
//                $oid = $user->getOidByUid($app['creator']);
//                if($oid){
//                    $orgInfo = $org->getOrganizeInfo($oid);
//                    $apps[$k]['org_name'] = $orgInfo['name'];
//                }else{
//                    $apps[$k]['org_name'] = '';
//                }
//            }else{
//                $apps[$k]['org_name'] = '';
//            }

			switch($apps[$k]['o_insti']){
                case 1:
                    $apps[$k]['o_insti'] = '一般';
                    break;
                case 2:
                    $apps[$k]['o_insti'] = '深度(初审下放)';
                    break;
                case 3:
                    $apps[$k]['o_insti'] = '分公司';
                    break;
                case 4:
                    $apps[$k]['o_insti'] = '总公司';
                    break;
                case 5:
                    $apps[$k]['o_insti'] = '未分类';
                    break;
                /* case 6:
                    $apps[$k]['o_insti'] = '个人';
                    break;
                case 7:
                    $apps[$k]['o_insti'] = '深度合作(总公司初审)';
                    break; */
                case 8:
                    $apps[$k]['o_insti'] = '深度';
                    break;
                default:
                    $apps[$k]['o_insti'] = '';
                    break;
            }

        }
        //print_r($apps);exit;
        return $apps;
    }
	
	//根据条件搜索机构
    public function getSearchorg($key,$oid='') {
		$orgModel = new Role_OrganizeModel();
		if($oid){
			$list = $orgModel->field('id,name')->where(array('name'=>array('like','%'.$key.'%'),'id'=>$oid,'type'=>array('in','1,3')))->select();
		}else{
			$list = $orgModel->field('id,name')->where(array('name'=>array('like','%'.$key.'%'),'type'=>array('in','1,3')))->select();
		}

        return $list;
    }

}
