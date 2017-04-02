<?php 
class ContractController extends Yaf_Controller_Abstract {
    private $_tpl;
    private $_req;
    private $_contractModel;
	
    public function init() {
        $this->_req = $this->getRequest();
        $this->_tpl = $this->getView();
        $this->_contractModel = new ContractModel();
        $this->_contractColumnLocationModel = new Contract_ColumnLocationModel();
        $this->_contractRealModel = new ContractRealModel();
		$this->redis = new Utils_Redis();		
        // $this->_contractModel = new ContractColumnLocationModel();
    }

    public function indexAction() {

    }

    public function getContractColumnListAjaxAction() {
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" =>'',
            "data" => array()
        );

        $column_name = trim($this->getRequest()->get('column_name', ''));
        $where = array();
        if(!empty($column_name)){
            $where = "column_name like '%".$column_name."%'";
        }

        $columnCount = $this->_contractModel->getColumnCount($where);
        if($columnCount > 0 ){
            $columnList = $this->_contractModel->getColumnList($_POST['start'],$_POST['length'],$where);
            // echo "<pre><meta charset='utf-8'>";var_dump($columnList);exit;
            foreach($columnList as $k=>$v){
                $columnList[$k]['item_type'] = $v['item_type']==1 ? '以租代购' : '房贷';
                switch ($v['location_type']) {
                    case 1:
                        $columnList[$k]['location_type'] = '贷前字段';
                        break;
                    case 2:
                        $columnList[$k]['location_type'] = '贷中字段';
                        break;
                    case 3:
                        $columnList[$k]['location_type'] = '贷后字段';
                        break;
                    case 4:
                        $columnList[$k]['location_type'] = '计算公式';
                        break;
                }
            }
            $output['recordsFiltered'] = $columnCount;
            $output['data'] = $columnList;
        }else{
            $output['recordsFiltered'] = $columnCount;
            $output['data'] = 0;
        }
        echo json_encode( $output );exit;
        
    }

    public function addColumnAction() {

    }

    public function addColumnAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        
        $addDatas = $this->getRequest()->getPost();
        $addDatas = Utils_FilterXss::xssarrfilter($addDatas);
        
        if(!$addDatas['column_name']){
            $data['errorMsg'] = '字段名不能为空';
            echo json_encode($data);
            exit;
        }
        if(!$addDatas['item_type']){
            $data['errorMsg'] = '请选择所属业务';
            echo json_encode($data);
            exit;
        }
        if(!$addDatas['location_type']){
            $data['errorMsg'] = '请选择字段类型';
            echo json_encode($data);
            exit;
        }
        
        $result = $this->_contractModel->add($addDatas);
        if ($result) {
            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            echo json_encode($data);exit;
        } else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);exit;
        }
    }

    public function editColumnAction(){
        $id = (int)$this->getRequest()->get("id");
        $info = $this->_contractModel->getInfo($id);
        $this->_tpl->assign('info', $info);
    }

    public function editColumnAjaxAction(){
        $id = (int) $this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::xssarrfilter($data);
        if(!$data['column_name']){
            $error['error'] = 100;
            $error['errormsg'] = '字段名不能为空';
            echo json_encode($error);
            exit;
        }
        if(!$data['item_type']){
            $error['error'] = 100;
            $error['errormsg'] = '请选择所属业务';
            echo json_encode($error);
            exit;
        }
        if(!$data['location_type']){
            $error['error'] = 100;
            $error['errormsg'] = '请选择字段类型';
            echo json_encode($error);
            exit;
        }
        if ($id <= 0) {
            $error['error'] = 100;
            Utils_Output::errorResponse('参数缺失');
            exit;
        }
        if ($this->_contractModel->mod($id, $data)) {
            $data['error'] = 0;
            $data['errormsg'] = '添加成功';
            echo json_encode($data);exit;
        } else {
            $data['errormsg'] = '添加失败';
            echo json_encode($data);exit;
        }
    }

    public function deleteColumnAjaxAction(){
        $id = (int)$this->getRequest()->get("id");
        $res = $this->_contractModel->delectColumn($id);
        if($res){
            $error['error'] = 0;
            $error['errorMsg'] = '删除成功';
            echo json_encode($error);exit;
        }else{
            $error['errorMsg'] = '删除失败';
            echo json_encode($error);exit;
        }
    }

    public function columnLocationAction(){
        $id = (int)$this->getRequest()->get("id");
        $location_id = $this->_contractModel->getInfo($id)['location_id'];
        $info = $this->_contractColumnLocationModel->getInfo($location_id);
        $this->_tpl->assign('info', $info);
        $this->_tpl->assign('column_id', $id);
    }

    public function columnLocationAjaxAction(){

        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::xssarrfilter($data);
        if(!$data['location_id']){
            $res = $this->_contractColumnLocationModel->addInfo($data);
            if($res && $data['column_id']){
                $update['location_id'] = $res;
                $up_res = $this->_contractModel->mod($data['column_id'], $update);
                if($up_res){
                    $data['error'] = 0;
                    $data['errormsg'] = '添加成功';
                    echo json_encode($data);exit;
                }else{
                    $error['error'] = 100;
                    $error['errormsg'] = '修改字段表失败';
                    echo json_encode($error);
                    exit;
                }
            }else{
                $error['error'] = 100;
                $error['errormsg'] = '失败';
                echo json_encode($error);
                exit;
            }
        }else{
            $res = $this->_contractColumnLocationModel->mod($data['location_id'], $data);
            if($res){
                $data['error'] = 0;
                $data['errormsg'] = '修改成功';
                echo json_encode($data);exit;
            }else{
                $error['error'] = 100;
                $error['errormsg'] = '修改失败';
                echo json_encode($error);
                exit;
            }
        }
    }

    public function contracttemplatelistAction(){
        $appModel = new Cd_AppModel();
        $app_id = $this->getRequest()->get('id',0);
        $oid = $appModel->getOid($app_id)['oid'];
        
        $this->_tpl->assign('id', $app_id);
        $this->_tpl->assign('oid', $oid);
    }

    public function contracttemplatelistAjaxAction(){
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" =>'',
            "data" => array()
        );

        $app_id = $this->getRequest()->getPost('id',0);
        $appModel = new Cd_AppModel();
        $oid = $appModel->getOid($app_id)['oid'];
        if(!$app_id || !$oid){
            $error['error'] = 100;
            $error['errormsg'] = '获取失败!';
            echo json_encode($error);
            exit;
        }

        $ctModel = new ContractTemplateModel();
        $count = $ctModel->getTemplateContractCount($app_id, $oid);
        $template_list = $ctModel->getTemplateContractList($app_id, $oid);
        
        if($template_list){
            $long = 0;
            foreach($template_list as $k=>$v){
                if($v['appid']!=$app_id){
                    $template_list[$k]['operation'] = '预览';
                }else{
                    $template_list[$k]['operation'] = '<a class="appid" href="/contract/contractPrint?id='.$v['id'].'">预览</a>';
                }
                $long = $k + 1;
            }
            
            //最后一个需要打印的文件固定为付款时间表,此文件并没有模板,所以会url到单独一个页面打印
            $template_list[$long]['operation'] = '<a class="appid" href="/contract/printPayDate?app_id='.$app_id.'">预览</a>';
            $template_list[$long]['name'] = '还款时间表';
            $template_list[$long]['type'] = '#';

            $long++;

            $deadline = $appModel->getApp($app_id)['deadline'];

            $base_sort = (int)$deadline/12*2;

            for($i=1; $i<=$base_sort; $i++){
                $diff = $i/2;
                $diff_int = ceil($diff);
                $a = $diff==$diff_int ? 2 : 1;
                $template_list[$long]['operation'] = '<a class="appid" href="/contract/printInsurance?app_id='.$app_id.'&sort='.$i.'">预览</a>';
                $template_list[$long]['name'] = '投保单（承租人）'.$diff_int.'-'.$a;
                $template_list[$long]['type'] = '#';
                $long++;
            }

            $template_list[$long]['operation'] = '<a class="appid" href="/contract/insurancecustomer?app_id='.$app_id.'">预览</a>';
            $template_list[$long]['name'] = '个人保险投保单';
            $template_list[$long]['type'] = '#';
            $long++;

        }

        $output['recordsFiltered'] = $count;
        $output['data'] = $template_list;
        echo json_encode( $output );exit;
    }

    /**
     * 选择替换模板中字段
     * @param $ids  选中的模板的id用‘，’拼接成字符串
     */
    public function replaceColumnAction(){
       
        $ids = htmlspecialchars($_POST['ids']);
        $app_id = (int)$_POST['app_id'];
        $oid = (int)$_POST['oid'];
        if(!$ids || !$app_id || !$oid){
            $error['error'] = 100;
            $error['errormsg'] = '获取失败';
            echo json_encode($error);
            exit;
        }

        $ctModel = new ContractTemplateModel();
        $contractRealModel = new ContractRealModel();
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();

        $info = $ctModel->getInfos($ids, $oid, $app_id);
        
        foreach($info as $key=>$value){
            $template_path = $config['root'];
            $url = $value['path'];
            $path = $template_path.'/'.$url;
            //获取模板内容并替换字段
            $res = $this->_contractModel->getContractHtmlContent($path, $app_id);
            //如果返回true 代表里面没有可替换字段 则直接存模板内容
            if($res === true){
                $res = file_get_contents($path);
            }
            //替换成功或获取内容成功
            if($res){
                //判断是否为已生成合同
                if($value['appid']){
                    //将模板内容写入服务器文件
                    $byte_size = file_put_contents($template_path.'/'.$value['cpath'], $res);
                    if(!is_numeric($byte_size) || $byte_size == 0){
                        $error['error'] = 100;
                        $error['errormsg'] = '合同['.$value['name'].']修改失败';
                        echo json_encode($error);
                        exit;
                    }
                }else{
                    $value['content'] = $res;
                    $value['appid'] = $app_id;
                    $contract_template_dir = 'fk/contract/example/' .$value['oid'].'/'.date('Ym').'/'.$app_id;
                    $upload = new Utils_Upload($contract_template_dir, $config['root']);
                    $upload_result = $upload->upload_contract($value, 2);
                    if($upload_result){
                        $data['appid'] = $app_id;
                        $data['oid'] = $value['oid'];
                        $data['contract_type'] = $value['type'];
                        $data['status'] = 1;
                        $data['path'] = $upload_result;
                        $result = $contractRealModel->addData($data);
                        if(!$result){
                            $error['error'] = 100;
                            $error['errormsg'] = '合同['.$value['name'].']插入数据库失败';
                            echo json_encode($error);
                            exit;
                        }
                        $result_arr[] = $result;
                    }else{
                        $error['error'] = 100;
                        $error['errormsg'] = '文件['.$value['name'].']存入失败';
                        echo json_encode($error);
                        exit;
                    }
                }
            }else{
                $error['error'] = 100;
                $error['errormsg'] = '模板['.$value['name'].']字段替换失败';
                echo json_encode($error);
                exit;
            }

        }

        $result_value = array_values($result_arr);
        if(!in_array(0, $result_value)){
            $error['error'] = 0;
            $error['errormsg'] = '成功';
            echo json_encode($error);
            exit;
        }
        exit;
    }

    /**
     * 全部替换
     */
    public function replaceAllColumnAction(){
        $oid = (int)$_POST['oid'];
        $app_id = (int)$_POST['app_id'];
        
        $ctModel = new ContractTemplateModel();
        $ids = $ctModel->getTemplateTypeList($app_id, $oid);
        if(!$app_id || !$oid || !$ids){
            $error['error'] = 100;
            $error['errormsg'] = '获取失败';
            echo json_encode($error);
            exit;
        }
        $ids = implode(',', $ids);

        $contractRealModel = new ContractRealModel();
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();

        $info = $ctModel->getInfos($ids, $oid, $app_id);
        
        foreach($info as $key=>$value){
            $template_path = $config['root'];
            $url = $value['path'];
            $path = $template_path.'/'.$url;
            //获取模板内容并替换字段
            $res = $this->_contractModel->getContractHtmlContent($path, $app_id);
            //如果返回true 代表里面没有可替换字段 则直接存模板内容
            if($res === true){
                $res = file_get_contents($path);
            }
            //替换成功或获取内容成功
            if($res){
                //判断是否为已生成合同
                if($value['appid']){
                    //将模板内容写入服务器文件
                    $byte_size = file_put_contents($template_path.'/'.$value['cpath'], $res);
                    if(!is_numeric($byte_size) || $byte_size == 0){
                        $error['error'] = 100;
                        $error['errormsg'] = '合同['.$value['name'].']修改失败';
                        echo json_encode($error);
                        exit;
                    }
                }else{
                    $value['content'] = $res;
                    $value['appid'] = $app_id;
                    $contract_template_dir = 'fk/contract/example/' .$value['oid'].'/'.date('Ym').'/'.$app_id;
                    $upload = new Utils_Upload($contract_template_dir, $config['root']);
                    $upload_result = $upload->upload_contract($value, 2);
                    if($upload_result){
                        $data['appid'] = $app_id;
                        $data['oid'] = $value['oid'];
                        $data['contract_type'] = $value['type'];
                        $data['status'] = 1;
                        $data['path'] = $upload_result;
                        $result = $contractRealModel->addData($data);
                        if(!$result){
                            $error['error'] = 100;
                            $error['errormsg'] = '合同['.$value['name'].']插入数据库失败';
                            echo json_encode($error);
                            exit;
                        }
                        $result_arr[] = $result;
                    }else{
                        $error['error'] = 100;
                        $error['errormsg'] = '文件['.$value['name'].']存入失败';
                        echo json_encode($error);
                        exit;
                    }
                }
            }else{
                $error['error'] = 100;
                $error['errormsg'] = '模板['.$value['name'].']字段替换失败';
                echo json_encode($error);
                exit;
            }

        }

        $result_value = array_values($result_arr);
        if(!in_array(0, $result_value)){
            $error['error'] = 0;
            $error['errormsg'] = '成功';
            echo json_encode($error);
            exit;
        }
        exit;
    }


    public function testAction(){
        $column_id = $_GET['id'];
        $app_id = $_GET['app_id'];
        $result = $this->_contractModel->getColumnValue($column_id, $app_id);
        // $data['result'] = "237.98";
        // $result = $this->_contractModel->cny($data);
        echo "<pre><meta charset='utf-8'>";var_dump($result);exit;
    }

    /**
     * 合同列表重新制作
     * 从 /loan/contractPrint.html 跳转过来
     */
    public function refresh_columnAction(){
        $id = (int)$_GET['id'];
        if(!$id){
            return false;
        }
        //删除已经替换好的合同
        $res = true;
        $info = $this->_contractRealModel->getByAppId($id);
        if($info){
            $res = $this->_contractRealModel->deleteContractByAppId($id);
            if(!$res){
                echo "<pre><meta charset='utf-8'>删除合同失败";
                return false;
            }
        }
        //修改贷中字段录制完成状态
        $_appModel = new Cd_AppModel();
        $res = $_appModel->setColumnUnMake($id);
        if($res){
            $url = '/contract/view/aid/'.$id;
            header('Location:'.$url);
            exit;
        }else{
            return false;
        }
        
    }

    public function deleteContractAction(){
        $ids = $_POST['ids'];
        $app_id = $_POST['app_id'];
        $oid = $_POST['oid'];
        $info = $this->_contractRealModel->getInfo($ids, $app_id, $oid);
        if($info == 0){
            $error['error'] = 100;
            $error['errormsg'] = '没有替换完的合同';
            echo json_encode($error);
            exit;
        }
        $res = $this->_contractRealModel->deleteContract($ids, $app_id);
        if($res){
            $error['error'] = 0;
            $error['errormsg'] = '删除合同成功';
            echo json_encode($error);
            exit;
        }else{
            $error['error'] = 100;
            $error['errormsg'] = '出现错误';
            echo json_encode($error);
            exit;
        }
    }
	
	
    /*
    *合同制作
    */
    public function contranctMakeAction(){

    }
    
    public function contranctMakeAjaxAction(){
        $this->_req = $this->getRequest();
        $risker = (int) $this->_req->get('risker', 0);
        $start = (int) $this->_req->get('start', 0);
        $offset = (int) $this->_req->get('length', 0);

        //搜索
        $borrower_name = trim($this->_req->get('borrower_name', ''));
        $report_user_name = trim($this->_req->get('report_user_name', ''));
        $startTime = trim($this->_req->get('start_time', ''));
        $endTime = trim($this->_req->get('end_time', ''));

        // $where = array();
        $where = '1';
        if ($startTime) {
            $startTime = $startTime . ' 00:00:00';
            // $where['start_time'] = (int) strtotime($startTime);
            $where .= ' start_time='.(int) strtotime($startTime);
        }
        if ($endTime) {
            $endTime = $endTime . '23:59:59';
            // $where['end_time'] = (int) strtotime($endTime);
            $where .= ' and end_time='.(int) strtotime($endTime);
        }

        if (!empty($borrower_name)) {
            // $where['borrower'] = $borrower_name;
            $where .= ' and borrower='.$borrower_name;
        }

        if (!empty($report_user_name)) {
            // $where['report_user_name'] = $report_user_name;
            $where .= ' and report_user_name='.$report_user_name;
        }

        if(isset($risker)&&$risker>=0){
            // $where['risker'] = $risker;	
            $where .= ' and risker='.$risker;
        }

        if ($offset == 0)
            $offset = 10;

        $oid = $this->_getUserOid();
        if ($this->_adminRole()) {
            $where .= ' and a.oid='.$oid;
        }else{
            // $where['oid'] = $oid;
            $where .= ' and a.oid='.$oid;
        }
        
        // $where['a.flow'] = 7;
        // $where['a.status'] = 3;

        // $where['a.flow'] = array('gt', 4);
        // $where['a.launch'] = 3;
        // $where['b.phone'] = array('neq', '');

        $where .= ' and (a.flow=7 and a.status=3) or (a.flow>4 and a.launch=3 and b.phone is not null)';

        // $where['a.column_make'] =  array('neq',1);
        $where .= ' and a.column_make<>1';
        // echo "<pre><meta charset='utf-8'>";var_dump($where);exit;
        $total = $this->_contractModel->selectAppZk($start, $offset, $where,1);
        $apps = $this->_contractModel->selectAppZk($start, $offset, $where);

        foreach($apps as $k=>$app){
            $apps[$k]['created_date'] = date('Y-m-d H:i:s', $app['created']);
        }
        
        $output = array(
            'draw' => $this->_req->get('draw') ? intval($this->_req->get('draw')) : 0,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $apps,
        );

        echo json_encode($output);
        return FALSE;
    }


    public function viewAction($aid) {
       
		$_type = htmlspecialchars($this->_req->get('type',""));
		/* $cdapp['column_make'] = 1;
		echo $this->_contractModel->updateapp($aid,$cdapp,2);
		die; */
		//$accountarr = array("car"=>array("name"=>"车行账户","type"=>0),"input"=>array("name"=>"商业险","type"=>0),"back"=>array("name"=>"商业险返点","type"=>0),"strong"=>array("name"=>"交强险","type"=>0),"strongchange"=>array("name"=>"交强险变更","type"=>0),"getin"=>array("name"=>"过户费","type"=>0),"install"=>array("name"=>"GPS安装费","type"=>1),"cost"=>array("name"=>"GPS成本费","type"=>1),"adfee"=>array("name"=>"征信费","type"=>0));
        $accountarr = array("car"=>array("name"=>"车行账户","type"=>0),"managefeeacc"=>array("name"=>"贷款手续费","type"=>0),"input"=>array("name"=>"商业险","type"=>0),"back"=>array("name"=>"商业险返点","type"=>0),"strong"=>array("name"=>"交强险","type"=>0),"strongchange"=>array("name"=>"交强险变更","type"=>0),"getin"=>array("name"=>"过户费","type"=>0),"install"=>array("name"=>"GPS安装费","type"=>0),"adfee"=>array("name"=>"征信费","type"=>0),"gpsbackacc"=>array("name"=>"gps返费","type"=>0),"marginbackacc"=>array("name"=>"押金返费","type"=>0));
        //$accountarr = array("input"=>array("name"=>"商业险","type"=>0),"back"=>array("name"=>"商业险返点","type"=>0),"strong"=>array("name"=>"交强险","type"=>0),"strongchange"=>array("name"=>"交强险变更","type"=>0),"getin"=>array("name"=>"过户费","type"=>0),"install"=>array("name"=>"GPS安装费","type"=>1),"adfee"=>array("name"=>"征信费","type"=>0));
        $aid = intval($aid);
        //业务员
        $salesman = $this->_contractModel->_appModel->getAppInfo($aid);
        
        if (!$salesman) {
            die('access denied');
        }
        
        //echo "<pre>";
       // print_r($salesman);
		
        foreach($salesman as $key=>$v){
            $salesman[$key]['custom_time'] = date('Y-m-d', $v['created']);
            $salesman[$key]['car_time'] = date('Y-m-d', $v['car_created']);
			if($v['launch'] == 2){
				$salesman[$key]['marriage_name'] = "";
			}else{
				if($v['marriage']==1){
					$salesman[$key]['marriage_name'] = "已婚";
				}else{
					$salesman[$key]['marriage_name'] = "未婚";
				}
			}
            
            $salesman[$key]['age'] = $this->getAge($v['birthday']);
        }
        //print_r($salesman);
		//die;
        $oid = $this->_getUserOid();
        if ($this->_adminRole()  || $oid == '4') {

        }else{
            // if($salesman[0]['oid']!=$oid){
            //     die('access denied');
            // }
        }

        $user = Session_AdminFengkong::instance();
        $roles = (new RoleModel())->getUserRoles($user->uid);
        $user_roles = [];
        foreach ($roles as $r) {
            $user_roles[] = $r['id'];
        }

        //用户没有任何角色 直接报错
        if (!$user_roles) {
            die('access denied');
        }
        
        $user_id = $this->_getUserId();
        //审批内容
        $app = $this->_contractModel->selectAuditZk($aid);
        foreach($app as $key=>$a){
            $app[$key]['creat_time'] = date('Y-m-d', $a['created']);
        }
		
		$is_post = array(0,0,0,0,0);
        //合同订单
        $con_order = $this->_contractModel->selectContractOrder($aid);
        if(!empty($con_order)){
			$is_post[0]= 1;
            $con_order['created_time'] = date('Y-m-d', $con_order['signcreated']);
        }
        $con_order_last = $this->_contractModel->selectContractOrderUser($user_id);
		
        //保险
        $con_risk = $this->_contractModel->selectContractRisk($aid);
        if(!empty($con_risk)){
			$is_post[1]= 1;
            if($con_risk['strongrisk']==1){
                $con_risk['costchange'] = "";
                $con_risk['changeterm'] = "";
            }else{
                $con_risk['strongriskamount'] = "";
                $con_risk['strongriskdate'] = "";
                $con_risk['strongriskvalidity'] = "";
            }
            if(!empty($con_risk['strongriskdate'])){
                $con_risk['strongriskdate'] = date('Y-m-d', $con_risk['strongriskdate']);
            }
            if(!empty($con_risk['changeterm'])){
                $con_risk['changeterm'] = date('Y-m-d', $con_risk['changeterm']);
            }
            if(!empty($con_risk['businessriskdate'])){
                $con_risk['businessriskdate'] = date('Y-m-d', $con_risk['businessriskdate']);
            }
        }
        //用户保险最后操作
        $risk_last = $this->_contractModel->selectContractRiskList($user_id);
        $last_status = $risk_last['status'];//0投保1不投保
        /* echo "<pre>";
        print_r($risk_last);
        die; */
        //GPS
        $con_gps = $this->_contractModel->selectContractGps($aid);
		if(!empty($con_gps)){
			$is_post[2]= 1;
		}
        //service
        $con_service = $this->_contractModel->selectContractService($aid);
		if(!empty($con_service)){
			$is_post[3]= 1;
		}	
        //放款相关账户

        $account = $this->_contractModel->selectContractAccount($aid);
        //echo "<pre>";
		if(!empty($account)){
			$is_post[4]= 1;
		}
        //$accountarr = array("free"=>array("name"=>"贷款手续费","type"=>0),"input"=>array("name"=>"商业险支出","type"=>0),"back"=>array("name"=>"商业险返点","type"=>0),"strong"=>array("name"=>"交强险","type"=>0),"getin"=>array("name"=>"过户费","type"=>0),"install"=>array("name"=>"GPS安装费","type"=>1),"cost"=>array("name"=>"GPS成本费","type"=>1));
		
        foreach($account as $key=>$v){
            $con_account['id'] = $v['aid'];
            switch ($v['type'])
            {
                case 0:
                    $con_account['car']['id'] = $v['id'];
                    $con_account['car']['accountname'] = $v['accountname'];
                    $con_account['car']['openbank'] = $v['openbank'];
                    $con_account['car']['cardno'] = $v['cardno'];
                    $con_account['car']['carall'] = $v['carall'];
                    break;
                case 1:
                    $con_account['input']['id'] = $v['id'];
                    $con_account['input']['accountname'] = $v['accountname'];
                    $con_account['input']['openbank'] = $v['openbank'];
                    $con_account['input']['cardno'] = $v['cardno'];
                    $con_account['input']['carall'] = $v['carall'];
                    break;		
                case 2:
                    $con_account['back']['id'] = $v['id'];
                    $con_account['back']['accountname'] = $v['accountname'];
                    $con_account['back']['openbank'] = $v['openbank'];
                    $con_account['back']['cardno'] = $v['cardno'];
                    $con_account['back']['carall'] = $v['carall'];
                    break;		
                case 3:
                    $con_account['strong']['id'] = $v['id'];
                    $con_account['strong']['accountname'] = $v['accountname'];
                    $con_account['strong']['openbank'] = $v['openbank'];
                    $con_account['strong']['cardno'] = $v['cardno'];
                    $con_account['strong']['carall'] = $v['carall'];
                    break;		
                case 4:
                    $con_account['strongchange']['id'] = $v['id'];
                    $con_account['strongchange']['accountname'] = $v['accountname'];
                    $con_account['strongchange']['openbank'] = $v['openbank'];
                    $con_account['strongchange']['cardno'] = $v['cardno'];
                    $con_account['strongchange']['carall'] = $v['carall'];
                    break;
                case 5:
                    $con_account['getin']['id'] = $v['id'];
                    $con_account['getin']['accountname'] = $v['accountname'];
                    $con_account['getin']['openbank'] = $v['openbank'];
                    $con_account['getin']['cardno'] = $v['cardno'];
                    $con_account['getin']['carall'] = $v['carall'];
                    break;		
                case 6:
                    $con_account['install']['id'] = $v['id'];
                    $con_account['install']['accountname'] = $v['accountname'];
                    $con_account['install']['openbank'] = $v['openbank'];
                    $con_account['install']['cardno'] = $v['cardno'];
                    $con_account['install']['carall'] = $v['carall'];
                    break;	
                case 7:
                    $con_account['adfee']['id'] = $v['id'];
                    $con_account['adfee']['accountname'] = $v['accountname'];
                    $con_account['adfee']['openbank'] = $v['openbank'];
                    $con_account['adfee']['cardno'] = $v['cardno'];
                    $con_account['adfee']['carall'] = $v['carall'];
                    break;
                case 8:
                    $con_account['managefeeacc']['id'] = $v['id'];
                    $con_account['managefeeacc']['accountname'] = $v['accountname'];
                    $con_account['managefeeacc']['openbank'] = $v['openbank'];
                    $con_account['managefeeacc']['cardno'] = $v['cardno'];
                    $con_account['managefeeacc']['carall'] = $v['carall'];
                    break;
				case 9:
                    $con_account['gpsbackacc']['id'] = $v['id'];
                    $con_account['gpsbackacc']['accountname'] = $v['accountname'];
                    $con_account['gpsbackacc']['openbank'] = $v['openbank'];
                    $con_account['gpsbackacc']['cardno'] = $v['cardno'];
                    $con_account['gpsbackacc']['carall'] = $v['carall'];
                    break;
				case 10:
                    $con_account['marginbackacc']['id'] = $v['id'];
                    $con_account['marginbackacc']['accountname'] = $v['accountname'];
                    $con_account['marginbackacc']['openbank'] = $v['openbank'];
                    $con_account['marginbackacc']['cardno'] = $v['cardno'];
                    $con_account['marginbackacc']['carall'] = $v['carall'];
                    break;
                default:
                    break;
            }
		}
		$ispost_status = 0;
        foreach($is_post as $v){
			if($v == 1){
				$ispost_status++;
			}
		}
		if($ispost_status == 5){
			//调用
			if($salesman[0]['column_make'] == 0 || ($salesman[0]['column_make'] == 1 && $_type != "print")){
				$cdapp['column_make'] = 1;
				$this->_contractModel->updateapp($aid,$cdapp);
				//$this->getView()->display("loan/contranctPrint.phtml");exit;
				$this->forward("loan","contranctPrint");
				return false;
			}
			
			//跳转
		}
		
		 /* $this->forward("loan","contranctPrint");
			return false; */
        //厦门机构
        /* $xiamen = 0;
        if(ini_get('yaf.environ')=='test' || ini_get('yaf.environ')=='develop'){
                //echo $oid;
                if($oid == 3){
                        $xiamen = 1;
                }
        }else if(ini_get('yaf.environ')=='product' || ini_get('yaf.environ')=='pre'){
                if($oid == 47){
                        $xiamen = 1;
                }
        } */
        //echo $xiamen;die;
        //print_r($con_account);
        //die;
		
		//放款相关账户缓存
		$ip = Util_Tool::getRealIP();
		if(!$this->redis->get($ip)){
			$this->redis->set($ip,1);
		}
        $arra = array("car","input","back","strong","strongchange","getin","install","adfee","managefeeacc","gpsbackacc","marginbackacc");
        $arrb = array("accountname","openbank","cardno");
        foreach($arra as $key=>$va){
            foreach($arrb as $vb){
                $a = $va.$vb;
				$con_account_cache["$va"]["$vb"] = $this->redis->get($ip.$a);
            }
        }
		
		//订单信息缓存
		$ordera = array('loan', 'rate','deadline','signcreated','company','address','email','tel','represen','accountname','openbank','cardno','emergency_one','emergency_one_phone','emergency_two','emergency_two_phone');
		
		foreach($ordera as $key=>$ora){
			$con_order_cache["$ora"] = $this->redis->get($ip.$ora);
		}
		//服务费用相关缓存
		$servicea = array("managefeeinput","managefeeout","strongriskinput","strongriskout","strongchangeinput","strongchangeout","transferfeeinput","transferfeeout","adfeeinput","adfeeout","depositamount","firstamount","serbusinessrisk","channelrebate","single_point");
		
		foreach($servicea as $key=>$sera){
			$con_service_cache["$sera"] = $this->redis->get($ip.$sera);
		}
		
		
		//保险缓存
		
		$riska = array('costchange', 'changeterm','insurancename', 'strongriskamount','strongriskdate','strongriskvalidity','businessriskamount','businessriskdate','businessriskvalidity','businessriskout','businessriskback','carlossrisk', 'thirdrisk','pilfer','compensate','carpersonrisk','glassrisk','majorrisk','other');
		
		foreach($riska as $key=>$risa){
			$con_risk_cache["$risa"] = $this->redis->get($ip.$risa);
		}
		
		//gps缓存
		$gpsa = array('gpsacount', 'gpscost','gpsinstall','gpsback');
		
		foreach($gpsa as $key=>$gpa){
			$con_gps_cache["$gpa"] = $this->redis->get($ip.$gpa);
		}
		
		
        $this->_tpl->assign('salesman', $salesman);
        $this->_tpl->assign('app', $app);
        $this->_tpl->assign('con_order', $con_order);
        $this->_tpl->assign('con_risk', $con_risk);
        $this->_tpl->assign('last_status', $last_status);
        $this->_tpl->assign('accountarr', $accountarr);
        $this->_tpl->assign('con_account', $con_account);
        $this->_tpl->assign('con_gps', $con_gps);
        $this->_tpl->assign('con_service', $con_service);
        $this->_tpl->assign('con_order_last', $con_order_last);
		$this->_tpl->assign('is_post', $is_post);
		$this->_tpl->assign('type', $_type);
		$this->_tpl->assign('con_account_cache', $con_account_cache);
		$this->_tpl->assign('con_order_cache', $con_order_cache);
		$this->_tpl->assign('con_service_cache', $con_service_cache);
		$this->_tpl->assign('con_risk_cache', $con_risk_cache);
		$this->_tpl->assign('con_gps_cache', $con_gps_cache);
		
        //$this->_tpl->assign('xiamen', $xiamen);

    }

    public function ordercreateAction() {

        $id = (int) $this->_req->getPost('order_id', 0);
        $app['aid'] = (int) $this->_req->getPost('app_id', 0);
        $app['user_id'] = $this->_getUserId();
        $app['loan'] = (float) $this->_req->getPost('loan', 0);
        $app['rate'] = (float) $this->_req->getPost('rate', 0);
        $app['deadline'] = htmlspecialchars($this->_req->getPost('deadline', '')); 
        $app['signcreated'] = strtotime(htmlspecialchars($this->_req->getPost('signcreated', ''))); 
        $app['company'] = htmlspecialchars($this->_req->getPost('company', '')); 
        $app['address'] = htmlspecialchars($this->_req->getPost('address', '')); 
        $app['email'] = htmlspecialchars($this->_req->getPost('email', '')); 
        $app['tel'] = htmlspecialchars($this->_req->getPost('tel', '')); 
        $app['represen'] = htmlspecialchars($this->_req->getPost('represen', '')); 
        $app['accountname'] = htmlspecialchars($this->_req->getPost('accountname', '')); 
        $app['openbank'] = htmlspecialchars($this->_req->getPost('openbank', '')); 
        $app['cardno'] = htmlspecialchars($this->_req->getPost('cardno', '')); 
        $app['reimbursement'] = $app['signcreated'] - 24*60*60;
        $app['created'] = strtotime("now");
        $app['emergency_one'] = htmlspecialchars($this->_req->getPost('emergency_one',''));
        $app['emergency_one_phone'] = htmlspecialchars($this->_req->getPost('emergency_one_phone',''));
        $app['emergency_two'] = htmlspecialchars($this->_req->getPost('emergency_two',''));
        $app['emergency_two_phone'] = htmlspecialchars($this->_req->getPost('emergency_two_phone',''));
        //$Order = new Cd_ContractOrderModel();
        //$data = $Order->add($app);
        //$data = $Order->where("id=$id")->save($app);
        /* *///$idm->add($insertData);
		$ip = Util_Tool::getRealIP();
		// if(!$this->redis->get($ip)){
			$this->redis->set($ip,1);
		// }
		
		foreach($app as $key=>$ap){
			$this->redis->set($ip.$key,$ap);
		}
		
        //添加序号
        $y = date('Y', $app['signcreated']);
        $m = date('m', $app['signcreated']);
        $d = date('d', $app['signcreated']);
        $start_time = mktime(00,00,00,$m,$d,$y);
        $end_time = mktime(24,24,59,$m, $d, $y); 
        //获取当前机构id
        $appModel = new Cd_AppModel();
        $oid = $appModel->getApp($app['aid'])['oid'];
        //获取当前机构当天插入最大值 如果有则+1  没有设置为1
        $has_sort = $this->_contractModel->getSort($oid, $start_time-1, $end_time, $app['aid']);
        if($has_sort['sort']){
            $sort = $has_sort['sort']+1;
        }else{
            $sort = 1;
        }

        $app['sort'] = $sort;

        $Order = new Cd_ContractOrderModel();
        $signcreated = $Order->where("id=$id")->find()['signcreated'];
        if($id>0){//更新
                $data = $Order->where("id=$id")->save($app);
                //重新制作提交后更改状态
                $cdapp['column_make'] = 0;
                $this->_contractModel->updateapp($app['aid'],$cdapp,2);
                // $res = $this->_contractModel->getHetong($app['aid'],$app['signcreated']);
                $res = $this->_contractModel->getHetong($app['aid'],$signcreated,$sort);
                if($data>0){
                        Utils_Output::ajaxReturn(array(
                        'errcode' => '0',
                        'errmsg' => '更新成功'
                        ));
                }else{
                        Utils_Output::ajaxReturn(array(
                        'errcode' => '0',
                        'errmsg' => '更新失败'
                        ));
                }

        }else{//插入
            $app['carall'] = 1;

            $data = $Order->add($app);
            if($data>0){
                // $res = $this->_contractModel->getHetong($app['aid'],$app['signcreated']);
				$res = $this->_contractModel->getHetong($app['aid'],$signcreated,$sort);
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '插入成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '1',
                'errmsg' => '插入失败'
                ));
            }
        }
        return false;
    }
	
	public function getHetongAction(){
		$res = $this->_contractModel->getHetong(14,"1457919342");
        return $res;
	}

    public function riskcreateAction() {
        //$app['created'] = strtotime("now");
        $id = (int) $this->_req->getPost('risk_id', 0);
        $app['aid'] = (int) $this->_req->getPost('app_id', 0);
        $app['user_id'] = $this->_getUserId();
        $app['status'] = (int) $this->_req->getPost('fromchannel', 0);
        $app['insurancename'] = htmlspecialchars($this->_req->getPost('insurancename', ''));
        $app['strongrisk'] = (int)($this->_req->getPost('strongrisk', 0));
        $app['strongriskamount'] = (float)($this->_req->getPost('strongriskamount', 0)); 
        $app['strongriskdate'] = strtotime(htmlspecialchars($this->_req->getPost('strongriskdate', 0)));
        $app['strongriskvalidity'] = (int) $this->_req->getPost('strongriskvalidity', 0);
        $app['costchange'] = (float)($this->_req->getPost('costchange', 0)); 
        $app['changeterm'] = strtotime(htmlspecialchars($this->_req->getPost('changeterm', ''))); 
        $app['businessriskamount'] = (float)($this->_req->getPost('businessriskamount', 0));
        $app['businessriskdate'] = strtotime(htmlspecialchars($this->_req->getPost('businessriskdate', '')));
        $app['businessriskvalidity'] = (int)($this->_req->getPost('businessriskvalidity', 0)); 
        $app['businessriskout'] = (float)($this->_req->getPost('businessriskout', 0)); 
        $app['businessriskback'] = (float)($this->_req->getPost('businessriskback', 0)); 
        $app['installment'] = (int)($this->_req->getPost('installment', 0)); 
        $app['carlossrisk'] = (int)($this->_req->getPost('carlossrisk', 0)); 
        $app['thirdrisk'] = (int)($this->_req->getPost('thirdrisk', 0)); 
        $app['pilfer'] = (int)($this->_req->getPost('pilfer', 0)); 
        $app['compensate'] = (int)($this->_req->getPost('compensate', 0)); 
        $app['carpersonrisk'] = (int)($this->_req->getPost('carpersonrisk', 0));
        $app['glassrisk'] = (int)($this->_req->getPost('glassrisk', 0)); 
        $app['majorrisk'] = (int)($this->_req->getPost('majorrisk', 0)); 
        $app['other'] = (int)($this->_req->getPost('other', 0)); 
        //$app['created'] = $app['created'] - 24*60*60;

        $ip = Util_Tool::getRealIP();
		if(!$this->redis->get($ip)){
			$this->redis->set($ip,1);
		}
		
		foreach($app as $key=>$ap){
			$this->redis->set($ip.$key,$ap);
		}

        $Order = new Cd_ContractRiskModel();
        if($id>0){//更新
            $data = $Order->where("id=$id")->save($app);
			//重新制作提交后更改状态
			$cdapp['column_make'] = 0;
			$this->_contractModel->updateapp($app['aid'],$cdapp,2);
            if($data>0){
				
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新失败'
                ));
            }
        }else{//插入
            $app['created'] = strtotime("now");
            $app['carall'] = 1;
            $data = $Order->add($app);
            if($data>0){
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '插入成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '1',
                'errmsg' => '插入失败'
                ));
            }
        }
        return false;
    }


    public function gpscreateAction() {
        //$app['created'] = strtotime("now");
        $id = (int) $this->_req->getPost('gps_id', 0);
        $app['aid'] = (int) $this->_req->getPost('app_id', 0);
        $app['user_id'] = $this->_getUserId();
        $app['isgpsstages'] = (int) $this->_req->getPost('isgpsstages', 0);
        $app['gpsacount'] = (float)($this->_req->getPost('gpsacount', 0)); 
        $app['gpscost'] = (float)($this->_req->getPost('gpscost', 0));
        $app['gpsinstall'] = (float)($this->_req->getPost('gpsinstall', 0)); 
        $app['isgpsback'] = (int)($this->_req->getPost('isgpsback', 0));
        $app['gpsback'] = (float)($this->_req->getPost('gpsback', 0)); 
        //$app['created'] = $app['created'] - 24*60*60;

        $ip = Util_Tool::getRealIP();
		if(!$this->redis->get($ip)){
			$this->redis->set($ip,1);
		}
		
		foreach($app as $key=>$ap){
			$this->redis->set($ip.$key,$ap);
		}

        $Gps = new Cd_ContractGpsModel();
        if($id>0){//更新
            $data = $Gps->where("id=$id")->save($app);
            //重新制作提交后更改状态
			$cdapp['column_make'] = 0;
			$this->_contractModel->updateapp($app['aid'],$cdapp,2);
			if($data>0){
				
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新失败'
                ));
            }

        }else{//插入
            $app['created'] = strtotime("now");
            $app['carall'] = 1;
            $data = $Gps->add($app);
            if($data>0){
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '插入成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '1',
                'errmsg' => '插入失败'
                ));
            }
        }
        return false;
    }


    public function servicecreateAction() {
        //$app['created'] = strtotime("now");
        $id = (int) $this->_req->getPost('service_id', 0);
        $app['aid'] = (int) $this->_req->getPost('app_id', 0);
        $app['user_id'] = $this->_getUserId();
        $app['serbusinessrisk'] = (int) $this->_req->getPost('serbusinessrisk', 0);
        $app['gpsservicefee'] = (int) $this->_req->getPost('gpsservicefee', 0);
        $app['managefee'] = (int) $this->_req->getPost('managefee', 0);
        $app['serstrongrisk'] = (int) $this->_req->getPost('serstrongrisk', 0);
        $app['serstrongchange'] = (int) $this->_req->getPost('serstrongchange', 0);
        $app['transferfee'] = (int) $this->_req->getPost('transferfee', 0);
        $app['adfee'] = (int) $this->_req->getPost('adfee', 0);
        $app['first'] = (int) $this->_req->getPost('first', 0);
        $app['deposit'] = (int) $this->_req->getPost('deposit', 0);
        $app['managefeeinput'] = (float)($this->_req->getPost('managefeeinput', 0)); 
        $app['managefeeout'] = (float)($this->_req->getPost('managefeeout', 0)); 
        $app['strongriskinput'] = (float)($this->_req->getPost('strongriskinput', 0)); 
        $app['strongriskout'] = (float)($this->_req->getPost('strongriskout', 0)); 
        $app['strongchangeinput'] = (float)($this->_req->getPost('strongchangeinput', 0)); 
        $app['strongchangeout'] = (float)($this->_req->getPost('strongchangeout', 0)); 
        $app['transferfeeinput'] = (float)($this->_req->getPost('transferfeeinput', 0)); 
        $app['transferfeeout'] = (float)($this->_req->getPost('transferfeeout', 0)); 
        $app['adfeeinput'] = (float)($this->_req->getPost('adfeeinput', 0)); 
        $app['adfeeout'] = (float)($this->_req->getPost('adfeeout', 0)); 
        $app['depositamount'] = (float)($this->_req->getPost('depositamount', 0)); 
		$app['channelrebate'] = (float)($this->_req->getPost('channelrebate', 0));
		$app['single_point'] = (float)($this->_req->getPost('single_point', 0));

        $app['firstamount'] = (float)($this->_req->getPost('firstamount', 0)); 
		
		$ip = Util_Tool::getRealIP();
		if(!$this->redis->get($ip)){
			$this->redis->set($ip,1);
		}
		
		foreach($app as $key=>$ap){
			$this->redis->set($ip.$key,$ap);
		}
        //$app['created'] = $app['created'] - 24*60*60;

        //$Order = new Cd_ContractOrderModel();
        //$data = $Order->add($app);
        //$data = $Order->where("id=$id")->save($app);
        /* *///$idm->add($insertData);
        /* Utils_Output::ajaxReturn(array(
                'errcode' => '2',
                'errmsg' => $app['businessriskamount']
                )); */

        $Service = new Cd_ContractServiceModel();
        if($id>0){//更新
            $data = $Service->where("id=$id")->save($app);
            //重新制作提交后更改状态
			$cdapp['column_make'] = 0;
			$this->_contractModel->updateapp($app['aid'],$cdapp,2);
			if($data>0){
				
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新失败'
                ));
            }
        }else{//插入
            $app['created'] = strtotime("now");
            $app['carall'] = 1;
            $data = $Service->add($app);
            if($data>0){
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '插入成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '1',
                'errmsg' => '插入失败'
                ));
            }
        }
        return false;
    }


    public function accountcreateAction() {
        //$app['created'] = strtotime("now");
		$ip = Util_Tool::getRealIP();
		if(!$this->redis->get($ip)){
			$this->redis->set($ip,1);
		}
        $id = (int) $this->_req->getPost('account_id', 0);
        //$arra = array("car","free","input","back","strong","getin","install","cost");
        $arra = array("car","input","back","strong","strongchange","getin","install","adfee","managefeeacc","gpsbackacc","marginbackacc");
        $arrb = array("accountname","openbank","cardno");
        $arr["car"]["carall"] = (int)$this->_req->getPost("car", 0);
        foreach($arra as $key=>$va){
            foreach($arrb as $vb){
                $a = $va.$vb;
                $arr["$va"]["$vb"] = htmlspecialchars($this->_req->getPost("$a", ''));
				$this->redis->set($ip.$a,$arr["$va"]["$vb"]);
			}
            $arr["$va"]["carall"] = (int)$this->_req->getPost("$va", 0);
            $arr["$va"]["type"] = $key;
            $arr["$va"]['aid'] = (int) $this->_req->getPost('app_id', 0);
            $arr["$va"]['user_id'] = $this->_getUserId();
        }
		
        
        $Account = new Cd_ContractAccountModel();
        if($id>0){//更新
            $status = 0;
            foreach($arr as $key=>$vaa){
                //echo "<pre>";
                //print_r($arr["$key"]);
                $type = $arr["$key"]["type"];
                unset($arr["$key"]["type"]);
                //print_r($arr["$key"]);
                $data = $Account->where(array("aid"=>$id,"type"=>$type))->save($arr["$key"]);
                //echo "<br/>";
                if($data>0){
                    $status++;
                }
            }
			//重新制作提交后更改状态
			$cdapp['column_make'] = 0;
			$this->_contractModel->updateapp($id,$cdapp,2);
            if($status>0){
				Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '更新失败'
                ));
            }

        }else{//插入
            foreach($arr as $key=>$vaa){
                $arr["$key"]['created'] = strtotime("now");
                $data = $Account->add($arr["$key"]);
            }
            if($data>0){
                Utils_Output::ajaxReturn(array(
                'errcode' => '0',
                'errmsg' => '插入成功'
                ));
            }else{
                Utils_Output::ajaxReturn(array(
                'errcode' => '1',
                'errmsg' => '插入失败'
                ));
            }
        }
        return false;
    }
	
	
    //获取用户机构ID
    private function _getUserOid(){
        $user = Session_AdminFengkong::instance();
        $m = new AdminUserModel();
        return $m->getOidByUid($user->uid);
    }
	
    private function _getUserId(){
        $user = Session_AdminFengkong::instance();
        return $user->uid;
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
	
    // public function getAge($id){
    //     if(!$id){
    //         return;
    //     }
    //     $date=strtotime($id);
    //     $today=strtotime('today');
    //     $diff=floor(($today-$date)/86400/365);
    //     return strtotime($id.' +'.$diff.'years')>$today?($diff+1):$diff; 
    // }

    public function getAge($birthday){
        $userModel = new Cd_UserModel();
        return $userModel->getAge($birthday);
    }
    
    
     /**
    * 合同模板列表页
    * @author hgy
    * @since 2016-05-20$redirect
    */
    public function templatelistAction() { 
        //查出所有机构
        $orgModel = new OrganizeModel();
        $orgs = $orgModel->getAllOrgs();
        
        $this->_tpl->assign('orgs', $orgs);
    }
    
    /**
    * 合同模板列表页Ajax
    * @author hgy
    * @since 2016-05-20
    */
    public function templatelistAjaxAction() {
        $ctModel = new ContractTemplateModel();
       
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" => '',
            "data" => array()
        );
        
        //查询条件
        $where = array();
        
        //搜索--开始
        $oid = trim($this->_req->get('search_org', 0));
        
        //搜索机构
        if (!empty($oid)) {
            $where['oid'] = trim($oid);
        }
        //搜索--结束
        
        $tempCount = $ctModel->getTemplateCount($where);
       
        if ($tempCount > 0) {
            $tempList = $ctModel->getTemplateList($_POST['start'], $_POST['length'],$where);
        }
        else{
            $tempList = 0;
        }
        
        $output['recordsFiltered'] = $tempCount;
        $output['data'] = $tempList;
        echo json_encode($output);
        exit;
    }
    
    
    /**
    * 新增模板
    * @author hgy
    * @since 2016-05-24
    */
    public function addTemplateAction() {
        //查出所有机构
        $orgModel = new OrganizeModel();
        $orgs = $orgModel->getAllOrgs();
        
        $this->_tpl->assign('orgs', $orgs);
    }
    
    /**
    * 提交新增模板
    * @author hgy
    * @since 2016-05-24
    */
    public function addTemplateAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        
        //接受表单数据
        $addDatas = $this->getRequest()->getPost();
        //过滤表单
        $addDatas = Utils_FilterXss::filterArray($addDatas);
        
        //检验表单数据
        if (empty($addDatas['oid'])) {
            $data['errorMsg'] = '请选择机构';
            echo json_encode($data);
            exit;
        }
        if (empty($addDatas['name'])) {
            $data['errorMsg'] = '请填写合同名称';
            echo json_encode($data);
            exit;
        }
        
        $ctModel =  new ContractTemplateModel();
        
        //查询本机构下type为最大值的模板,如果不存在新增模板的type为1,否则为当前type+1
        $type = $ctModel->getMaxType($addDatas['oid']);
        if(!empty($type)){
            $addDatas['type'] = $type + 1;
        }
        else{
            $addDatas['type'] = 1;
        }
        
        //添加合同模板
        $result = $ctModel->addData($addDatas);
        
        if ($result) {
            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            
            echo json_encode($data);
            exit;
        } else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);
            exit;
        }
    }
    
    /**
    * 修改合同模板
    * @author hgy
    * @since 2016-05-24
    */
    public function editTemplateAction() {
        $ctModel =  new ContractTemplateModel();
        
        //获取模板ID
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            Utils_Output::errorMsg('模板ID获取失败');
        }

        $template_info = $ctModel->get($id);
        if (!$template_info) {
            Utils_Output::errorMsg('模板信息获取失败');
        }
        
        //查询所有机构
        $orgModel = new OrganizeModel();
        $orgs = $orgModel->getAllOrgs();
        
        $this->_tpl->assign('orgs', $orgs);     
        $this->_tpl->assign('template_info', $template_info);
    }
    
    /**
    * 修改模板Ajax
    * @author hgy
    * @since 2016-05-24
    */
    public function editTemplateAjaxAction() {        
        $ctModel =  new ContractTemplateModel();
        
        //获取模板ID
        $id = (int) $this->getRequest()->get('id');
       
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        
        //检验表单数据
        if (empty($id) || !is_numeric($id) || $id <= 0 ) {
            Utils_Output::errorResponse('参数缺失');
            exit;
        }
        if (empty($data['name'])) {
            Utils_Output::errorResponse('请填写合同名称');
            exit;
        }
        if (empty($data['oid'])) {
            Utils_Output::errorResponse('请选择机构');
            exit;
        }
        
        //查询模板详情
        $temp_info = $ctModel->get($id);
        if (!$temp_info) {
            Utils_Output::errorResponse('模板信息获取失败');
            exit;
        }
        
        //如果模板已经生成,则不能再修改机构
        if (($temp_info['status'] != 0) && ($temp_info['oid'] != $data['oid']) ) {
            Utils_Output::errorResponse('模板已生成或已删除,不能再更改机构');
            exit;
        }
        
        //如果编辑后的机构与原机构不同,要重新计算新机构type值
        if($temp_info['oid'] != $data['oid']){
            $type = $ctModel->getMaxType($data['oid']);
            if(!empty($type)){
                $data['type'] = $type + 1;
            }
            else{
                $data['type'] = 1;
            }
        }
        
        //修改模板
        $result = $ctModel->mod($id, $data);
        
        if($result){
            Utils_Output::errorResponse('OK', 0);
            exit;
            
        }else {
            Utils_Output::errorResponse('未做修改或其它错误');
            exit;
        }
        return FALSE;       
    }
    
    /**
    * 删除模板Ajax
    * @author hgy
    * @since 2016-05-24
    */
    public function deleteTemplateAjaxAction() {
        $ctModel =  new ContractTemplateModel();
        
        //获取模板ID
        $id = (int) $this->getRequest()->getPost('id');
        
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            Utils_Output::errorResponse('模板ID获取失败');
            exit;
        }

        $temp_info = $ctModel->get($id);
        if (!$temp_info) {
            Utils_Output::errorResponse('模板信息获取失败');
            exit;
        }
        
        //把模板置为已删除状态
        $delData = array();
        $delData['status'] = 4;
        $result = $ctModel->mod($id,$delData);
        
        if($result){
            Utils_Output::errorResponse('OK', 0);
            exit;
        }else {
            Utils_Output::errorResponse('删除失败');
            exit;
        }
        return FALSE;       
    }
    
    
    /**
    * 模板制作页
    * @author hgy
    * @since 2016-05-20
    */
    public function templateMakeAction() {
        $ctModel =  new ContractTemplateModel();
        
        //模板Id
        $id = $this->getRequest()->get('id', "");
        $id = htmlspecialchars($id);
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            Utils_Output::errorMsg('模板ID获取失败');
        }
        
        //根据模板Id查出模板详情
        $temp_info = $ctModel->get($id);
        if (!$temp_info) {
            Utils_Output::errorMsg('未查询到对应模板');
        }
        
        //到服务器查询是否已有模板,如果有读出渲染到页面
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        $template_path = $config['root'].$temp_info['path'];
        $content = file_get_contents($template_path);
        
//        $contract_template_dir = 'D:\\image/contract/template/' .$temp_info['oid'];
//        $file_name = trim($temp_info['oid']."_".$temp_info['type']."_".$temp_info['id'].".html");
//        $template_file_path = $contract_template_dir. "/" .$file_name;  

        //查出模板所属机构
        $orgModel = new OrganizeModel();
        $org_info = $orgModel->getOrganizeInfo($temp_info['oid']);
        
        $this->_tpl->assign('id', $id);
        $this->_tpl->assign('temp_info', $temp_info);
        $this->_tpl->assign('org_info', $org_info);
        $this->_tpl->assign('con', $content);
    }
    
    
    /**
    * 获取富文本内容,制作模板
    * @author hgy
    * @since 2016-04-25
    */
    public function getContentAjaxAction() {
        $ctModel =  new ContractTemplateModel();
        
        //获取表单数据
        $data = $this->getRequest()->getPost();
        
        //检验表单数据
        if (empty($data['id']) || !is_numeric($data['id']) || $data['id'] <= 0) {
            Utils_Output::errorResponse('模板ID获取失败');
            exit;
        }
        
        if (empty($data['all_content'])) {
            Utils_Output::errorResponse('请填写模板内容');
            exit;
        }
        
        //根据模板Id查出模板详情
        $temp_info = $ctModel->get($data['id']);
        if (!$temp_info) {
            Utils_Output::errorResponse('未查询到对应模板');
            exit;
        }
        
        $data['all_content'] = "<meta charset='utf-8' />".$data['all_content'];
        $temp_info['content'] = $data['all_content'];
       
        //拼接合同模板文件路径,并写入到服务器中
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        //$contract_template_dir = 'D:\\image/contract/template/' .$temp_info['oid']; 
        $contract_template_dir = 'fk/contract/template/' .$temp_info['oid']; 
        $upload = new Utils_Upload($contract_template_dir, $config['root']);
        $upload_result = $upload->upload_contract($temp_info);
        
        if($upload_result){
            //生成或编辑模板成功后总把模板状态改为已制作
            $modData = array();
            $modData['status'] = 1;
            $modData['path'] = $upload_result;
            $ctModel->mod($data['id'],$modData);
            
            Utils_Output::errorResponse('OK', 0);
            exit;
        }
        else{
            Utils_Output::errorResponse('合同模板生成失败');
            exit;
        }
        
    }
    
    
    /**
    * 合同打印页
    * @author hgy
    * @since 2016-05-25
    */
    public function contractPrintAction() {
        //$ctModel =  new ContractTemplateModel();
        $crModel = new ContractRealModel();
        //合同Id
        $id = $this->getRequest()->get('id', "");
        $id = htmlspecialchars($id);
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            Utils_Output::errorMsg('合同ID获取失败');
        }
        
        //根据合同Id查出合同详情
        $con_info = $crModel->get($id);
        if (!$con_info) {
            Utils_Output::errorMsg('未查询到对应合同');
        }
        
        if (empty($con_info['path']) || $con_info['status'] != 1) {
            Utils_Output::errorMsg('合同未替换完成');
        }
        //到服务器查询是否已有模板,如果有读出渲染到页面
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        $contract_path = $config['root'].$con_info['path'];
        $content = file_get_contents($contract_path);
        
        //查出模板所属机构
        $orgModel = new OrganizeModel();
        $org_info = $orgModel->getOrganizeInfo($con_info['oid']);
        
        $this->_tpl->assign('app_id', $con_info['appid']);
        $this->_tpl->assign('id', $id);
        $this->_tpl->assign('con_info', $con_info);
        $this->_tpl->assign('org_info', $org_info);
        $this->_tpl->assign('con', $content);
        
    }
    
    /**
    * 合同内容编辑
    * @author hgy
    * @since 2016-05-25
    */
    public function contractPrintAjaxAction() {
        $crModel =  new ContractRealModel();
        
        //获取表单数据
        $data = $this->getRequest()->getPost();
        
        //检验表单数据
        if (empty($data['id']) || !is_numeric($data['id']) || $data['id'] <= 0) {
            Utils_Output::errorResponse('合同ID获取失败');
            exit;
        }
        
        if (empty($data['all_content'])) {
            Utils_Output::errorResponse('请填写合同内容');
            exit;
        }
        
        //根据合同Id查出合同详情
        $contract_info = $crModel->get($data['id']);
        if (!$contract_info) {
            Utils_Output::errorResponse('未查询到对应合同');
            exit;
        }
        
        //拼接合同文件路径,并写入到服务器中
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        //文件完整路径
        $contract_file_path = $config['root'].$contract_info['path'];
        //将模板内容写入服务器文件
        $byte_size = file_put_contents($contract_file_path, $data['all_content']);
        
        if(is_numeric($byte_size) && $byte_size > 0){
            //编辑合同成功后总把合同状态改为已制作
            $modData = array();
            $modData['status'] = 1;
            $crModel->mod($data['id'],$modData);
            
            Utils_Output::errorResponse('OK', 0);
            exit;
        }
        else{
            Utils_Output::errorResponse('合同编辑失败');
            exit;
        }
        
    }
        
    
    //批量打印合同 (富文本编辑器)
    public function batchprintAction() {
        $cd_App = new Cd_AppModel();
        
        //获取订单与机构ID
        $appid = $this->getRequest()->get('appid',0);
        $oid = $this->getRequest()->get('oid',0);
        $appid = htmlspecialchars($appid);
        $oid = htmlspecialchars($oid);
        
        if(empty($appid) || empty($oid)){
            Utils_Output::errorMsg('订单ID错误');
        }
        
        //查询订单详情
        $appInfo = $cd_App->getApp($appid);
        if(!$appInfo){
            Utils_Output::errorMsg('未查询到订单详情');
        }

        $app_info = $cd_App->getContractCustomerInfo($appid);
        
        //根据订单Id与机构查询出要打印的合同详情
        $crModel = new ContractRealModel();
        $contracts = $crModel->getWaitPrints($appid, $oid);
        if(empty($contracts)){
            Utils_Output::errorMsg('无需要打印的合同');
        }
        
        //获取要打印的文件的内容
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        $cons = array();
        foreach($contracts as $key => $val){
            if(!empty($val['path'])){
                //合同完整路径
                $contract_path = $config['root'].$val['path'];
                //读出合同内容
                $cons[] = file_get_contents($contract_path);
            }
        }
        
        if(empty($cons)){
            Utils_Output::errorMsg('无需要打印的合同');
        }
        
        //查询还款日期表
        $payDateInfo = $this->_getPayDateInfo($appid);

        //查询投保单
        $insuranceInfo = $this->_getInsuranceInfo($appid);
        $cnt = $insuranceInfo['deadline'] / 12;

//        $path1 = "D:\\image/htt1.html";
//        $content1 = file_get_contents($path1);
//        $path2 = "D:\\image/htt2.html";
//        $content2 = file_get_contents($path2);
//        $path3 = "D:\\image/ht3.html";
//        $content3 = file_get_contents($path3);       
        //$cons = array("con1"=>$content1,"con2"=>$content2,"con3"=>$content3);
        //$cons = array("con1"=>$content1,"con2"=>$content2);
        //print_r($cons);exit;
        
        $this->_tpl->assign('app_id', $appid);
        $this->_tpl->assign('cons', $cons);
        $this->_tpl->assign('payInfo', $payDateInfo['payInfo']);
        $this->_tpl->assign('payDates', $payDateInfo['payDates']);
        $this->_tpl->assign('insurance', $insuranceInfo);
        $this->_tpl->assign('cnt', $cnt);
        $this->_tpl->assign('app', $app_info);
        
    }
    
    //批量打印合同 (富文本编辑器)
    public function batchprint2Action() {
    	$cd_App = new Cd_AppModel();
    
    	//获取订单与机构ID
    	$appid = $this->getRequest()->get('appid',0);
    	$oid = $this->getRequest()->get('oid',0);
    	$appid = htmlspecialchars($appid);
    	$oid = htmlspecialchars($oid);
    
    	if(empty($appid) || empty($oid)){
    		Utils_Output::errorMsg('订单ID错误');
    	}
    
    	//查询订单详情
    	$appInfo = $cd_App->getApp($appid);
    	if(!$appInfo){
    		Utils_Output::errorMsg('未查询到订单详情');
    	}
    
    	//根据订单Id与机构查询出要打印的合同详情
    	$crModel = new ContractRealModel();
    	$contracts = $crModel->getWaitPrints($appid, $oid);
    	if(empty($contracts)){
    		Utils_Output::errorMsg('无需要打印的合同');
    	}
    
    	//获取要打印的文件的内容
    	$config = Yaf_Application::app()->getConfig()->imgupload->toArray();
    	$cons = array();
    	foreach($contracts as $key => $val){
    		if(!empty($val['path'])){
    			//合同完整路径
    			$contract_path = $config['root'].$val['path'];
    			//读出合同内容
    			$cons[] = file_get_contents($contract_path);
    		}
    	}
    
    	if(empty($cons)){
    		Utils_Output::errorMsg('无需要打印的合同');
    	}
    
    	//查询还款日期表
    	$payDateInfo = $this->_getPayDateInfo($appid);
    
    	//        $path1 = "D:\\image/htt1.html";
    	//        $content1 = file_get_contents($path1);
    	//        $path2 = "D:\\image/htt2.html";
    	//        $content2 = file_get_contents($path2);
    	//        $path3 = "D:\\image/ht3.html";
    	//        $content3 = file_get_contents($path3);
    	//$cons = array("con1"=>$content1,"con2"=>$content2,"con3"=>$content3);
    	//$cons = array("con1"=>$content1,"con2"=>$content2);
    	//print_r($cons);exit;
    
    	$this->_tpl->assign('app_id', $appid);
    	$this->_tpl->assign('cons', $cons);
    	$this->_tpl->assign('payInfo', $payDateInfo['payInfo']);
    	$this->_tpl->assign('payDates', $payDateInfo['payDates']);
    
    }
    
    
    //批量打印合同 (网页)
    public function batchprinthtmAction() {
        $cd_App = new Cd_AppModel();
        
        //获取订单与机构ID
        $appid = $this->getRequest()->get('appid',0);
        $oid = $this->getRequest()->get('oid',0);
        $appid = htmlspecialchars($appid);
        $oid = htmlspecialchars($oid);
        
        if(empty($appid) || empty($oid)){
            Utils_Output::errorMsg('订单ID错误');
        }
        
        //查询订单详情
        $appInfo = $cd_App->getApp($appid);
        if(!$appInfo){
            Utils_Output::errorMsg('未查询到订单详情');
        }
        
        //根据订单Id与机构查询出要打印的合同详情
        $crModel = new ContractRealModel();
        $contracts = $crModel->getWaitPrints($appid, $oid);
        if(empty($contracts)){
            Utils_Output::errorMsg('无需要打印的合同');
        }
        
        //获取要打印的文件的内容
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        $cons = array();
        foreach($contracts as $key => $val){
            if(!empty($val['path'])){
                //合同完整路径
                $contract_path = $config['root'].$val['path'];
                //读出合同内容
                $cons[] = file_get_contents($contract_path);
            }
        }
        
        if(empty($cons)){
            Utils_Output::errorMsg('无需要打印的合同');
        }
        
        //查询还款日期表
        $payDateInfo = $this->_getPayDateInfo($appid);
        
//        $path1 = "D:\\image/htt1.html";
//        $content1 = file_get_contents($path1);
//        $path2 = "D:\\image/htt2.html";
//        $content2 = file_get_contents($path2);
//        $path3 = "D:\\image/ht3.html";
//        $content3 = file_get_contents($path3);       
        //$cons = array("con1"=>$content1,"con2"=>$content2,"con3"=>$content3);
        //$cons = array("con1"=>$content1,"con2"=>$content2);
        //print_r($cons);exit;
        
        $this->_tpl->assign('app_id', $appid);
        $this->_tpl->assign('cons', $cons);
        $this->_tpl->assign('payInfo', $payDateInfo['payInfo']);
        $this->_tpl->assign('payDates', $payDateInfo['payDates']);
        
        $this->_tpl->assign('cons', $cons);
        
    }
    
    
    
    
    
    
    
    public function setHasPrintAction(){
        $app_id = $_POST['app_id'];
        $appModel = new Cd_AppModel();
        $res = $appModel->setHasPrint($app_id);
        if($res){
            $data['error'] = 0;
            $data['errormsg'] = '修改成功';
            echo json_encode($data);exit;
        }else{
            $data['error'] = 100;
            $data['errormsg'] = '修改失败';
            echo json_encode($data);exit;
        }
    }
    
    
    /**
    * 查询还款时间表
    * @author hgy
    * @since 2016-06-08
    */
    public function printPayDateAction() {
        //获取订单ID
        $app_id = $this->getRequest()->get('app_id',0);
        
        $app_id = htmlspecialchars($app_id);
        
        if(empty($app_id)){
            Utils_Output::errorMsg('订单ID获取失败');
        }
        
        //计算还款日期
        $payDateInfo = $this->_getPayDateInfo($app_id);
        
        $this->_tpl->assign('payInfo', $payDateInfo['payInfo']);
        $this->_tpl->assign('payDates', $payDateInfo['payDates']);
    }
    
    
    /**
    * 计算还款日期表
    * @author hgy
    * @since 2016-06-08
    */
    public function _getPayDateInfo($app_id) {
        
        //获取还款时间表需要展示字段
        $cdModel = new ChedaiModel();
        $payInfo = $cdModel->getPayDate($app_id);
       
        //总还款金额
        $payInfo['pay_total'] = $this->_contractModel->getColumnValue(158, $app_id);
        //每期还款金额
        $payInfo['per_account'] = $this->_contractModel->getColumnValue(159, $app_id);
        //每期还款本金
        $payInfo['per_money'] = $this->_contractModel->getColumnValue(157, $app_id);
        //每期还款利息
        $payInfo['per_rate'] = floor($this->_contractModel->getColumnValue(160, $app_id));
        //合同签订日期
        $payInfo['sign_date'] = date("Y-m-d", $payInfo['signcreated']);
        
        //合同签订日期信息详情
        $date_info = getdate($payInfo['signcreated']);
       
        //减去天数,默认减去一天,即首期还款日期为合同签订日期下月当日的前一天
        $cut_day = 1;
        
        //判断是否闰年
//        $time = mktime(20,20,20,2,1,$date_info['year']);
//        if (date("t",$time)==29){ 
//            $run_year = true;
//        }else{
//            $run_year = false;
//        }
//        //如果签订合同日是以下几个日期,需要特殊处理
//        if($run_year == true && $date_info['mon'] == 1 && $date_info['mday'] == 31 ){
//            $cut_day = 3;
//        }
//        else if($run_year == true && $date_info['mon'] == 1 && $date_info['mday'] == 30 ){
//            $cut_day = 2;
//        }
//        else if($run_year == false && $date_info['mon'] == 1 && $date_info['mday'] == 31 ){
//            $cut_day = 4;
//        }
//        else if($run_year == false && $date_info['mon'] == 1 && $date_info['mday'] == 30 ){
//            $cut_day = 3;
//        }
        
        //判断签订合同日是否是12月,并获取首期还款日期,首期还款日等于合同签订日期
        //但之后几个月的还款日期则提前一天,比如首期还款日是5月6日,第二期还款日是6月5日
        if($date_info['mon'] == 12){
            $first_pay_day = date('Y-m-d',strtotime(($date_info['year']+1) .'-'. '01' .'-'. ($date_info['mday'] - $cut_day)));
        }
        else{
            $first_pay_day = date('Y-m-d',strtotime($date_info['year'].'-'. ($date_info['mon']) .'-'. ($date_info['mday'] - $cut_day)));
        }
        
        //还款日期数组,用于记录每期还款详情
        $payDates = array();
        //首次还款日期时间戳
        $pay_timestamp = strtotime($first_pay_day);
        //首次还款日期信息详情
        $pay_date_info = getdate($pay_timestamp);
        
        //当还款期数小于还款期限时循环
        for($i=0; $i<$payInfo['deadline']; $i++){
            
            //借款期限是否跨年,默认在一年内
            $year = 0;
            
            //如果首期还款月份 + 当前期数,大于12,证明已经跨年
            if($pay_date_info['mon'] + $i > 12){
                //计算跨了几年
                $year = intval(($pay_date_info['mon'] + $i) / 12);
                //所跨年数对应的月数
                $span_month = 12 * $year;               
            }
            else{
                $span_month = 0;
            }
            
            //还款日期 (如果跨年了,年份要加上跨过的年数,月份要减去跨过的月数)
            $second_pay_day = date('Y-m-d',strtotime(($pay_date_info['year'] + $year).'-'. ($pay_date_info['mon'] + $i - $span_month) .'-'.$pay_date_info['mday']));                      
            //还款日期所在月份
            $second_month = date("n",strtotime($second_pay_day));
            
            //计算还款日期 - 3天，是哪一天，用于处理一些特殊的日期
            $first_pay_day = date('Y-m-d',strtotime($second_pay_day ." -3 day"));
            //前三天所在月份
            $first_month = date("n",strtotime($first_pay_day));
            
            //如果还款日期的月份与前三天所在月份不一致,证明当前月没有还款日期,例如:6月没有31日,那就以当前月最后一天为还款日期
            //当 $pay_date_info['mday'] < 4 时,证明还款日期是在月初三天,那就不必进入下面的条件
            if(($second_month != $first_month) && ($pay_date_info['mday'] >= 4) ){
                $second_pay_day = date('Y-m-t',strtotime(($pay_date_info['year'] + $year).'-'.($pay_date_info['mon'] + $i - $span_month)));                            
            }
            
            //添加到还款数组中去：
            //还款日期
            $payDates[$i]['pay_day'] = $second_pay_day;
            //还款金额
            $payDates[$i]['pay_account'] = $payInfo['per_account']; 
            //还款本金
            $payDates[$i]['pay_money'] = $payInfo['per_money'];
            //还款利息
            $payDates[$i]['pay_rate'] = $payInfo['per_rate'];
            //剩余本金
            if($i == 0){
                $payDates[$i]['pay_residue'] = $payInfo['pay_total'];
            }
            else{
                $payDates[$i]['pay_residue'] = $payDates[$i - 1]['pay_residue'] - $payDates[$i]['pay_money'];
            }
            
            //如果剩余金额小于0，就算作0
            if($payDates[$i]['pay_residue'] < 0)
                $payDates[$i]['pay_residue'] = 0;
           
        }
        
        //首期还款日
        $payInfo['first_pay_day'] = $payDates[0]['pay_day'];
        
        $payDateInfo = array('payDates' => $payDates , 'payInfo' => $payInfo);
        return $payDateInfo;        
    }
	
	//账号补齐
	public function getintoaction($id){
		$id = (int)$id;
		$this->_contractModel->getintoaccount($id);
		return false;
		
	}
	
	//账号缓存
	public function setredisaction(){
		$ip = Util_Tool::getRealIP();
		if(!$this->redis->get($ip)){
			echo $this->redis->set($ip,1);
		}else{
			echo $this->redis->get($ip);
		}
		//$this->redis->set("a","aaaaa");
		
		//echo $this->GetIP();
		return false;
	}
	
	public function GetIP(){
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		   $cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
		   $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif(!empty($_SERVER["REMOTE_ADDR"])){
		   $cip = $_SERVER["REMOTE_ADDR"];
		}
		else{
		   $cip = "无法获取！";
		}
		return $cip;
	}
	
	public function getredisaction(){
		echo $this->redis->get("a");
		return false;
	}
    
    //模板库列表
    public function templateLibraryListAction(){
        
    }
    //模板库列表ajax
    public function templateLibraryListAjaxAction(){
        $ctlModel = new ContractTemplateLibraryModel();
       
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" => '',
            "data" => array()
        );
        
        //查询条件
        $where = array();
        
        //搜索--开始
        $name = trim($this->_req->get('template_name', 0));
        //搜索机构
        if (!empty($name)) {
            $where['name'] = array('like', '%'.$name.'%');
        }
        //搜索--结束
        
        $tempCount = $ctlModel->getTemplateCount($where);
        $tempList = 0;
        if ($tempCount > 0) {
            $tempList = $ctlModel->getTemplateList($_POST['start'], $_POST['length'],$where);
        }
        else{
            $tempList = 0;
        }
        
        $output['recordsFiltered'] = $tempCount;
        $output['data'] = $tempList;
        echo json_encode($output);
        exit;
    }

    public function addTemplateLibAction(){

    }
    
    public function addTemplateLibAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        
        //接受表单数据
        $addDatas = $this->getRequest()->getPost();
        //过滤表单
        $addDatas = Utils_FilterXss::filterArray($addDatas);
        
        if (empty($addDatas['name'])) {
            $data['errorMsg'] = '请填写合同名称';
            echo json_encode($data);
            exit;
        }
        
        $ctlModel =  new ContractTemplateLibraryModel();
            
        $name_exists = $ctlModel->getNameCount($addDatas['name']);
        if(!empty($name_exists)){
            $data['errorMsg'] = '已存在合同名称，请不要重复添加';
            echo json_encode($data);
            exit;
        }

        //添加合同模板
        $result = $ctlModel->addData($addDatas);
        
        if ($result) {
            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            
            echo json_encode($data);
            exit;
        } else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);
            exit;
        }
    }

    public function editTemplateLibAction() {
        $ctlModel =  new ContractTemplateLibraryModel();
        
        //获取模板ID
        $id = (int) $this->getRequest()->get('id');
        $id = htmlspecialchars($id);
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            Utils_Output::errorMsg('模板ID获取失败');
        }

        $template_info = $ctlModel->get($id);
        if (!$template_info) {
            Utils_Output::errorMsg('模板信息获取失败');
        }
        
        $this->_tpl->assign('template_info', $template_info);
    }

    public function editTemplateLibAjaxAction() {        
        $ctlModel =  new ContractTemplateLibraryModel();
        
        //获取模板ID
        $id = (int) $this->getRequest()->get('id');
       
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        
        //检验表单数据
        if (empty($id) || !is_numeric($id) || $id <= 0 ) {
            Utils_Output::errorResponse('参数缺失');
            exit;
        }
        if (empty($data['name'])) {
            Utils_Output::errorResponse('请填写合同名称');
            exit;
        }
        
        $name_exists = $ctlModel->getNameCount($data['name']);
        if(!empty($name_exists)){
            Utils_Output::errorResponse('已存在合同名称，请不要重复添加');
            exit;
        }

        //查询模板详情
        $temp_info = $ctlModel->get($id);
        if (!$temp_info) {
            Utils_Output::errorResponse('模板信息获取失败');
            exit;
        }
        
        //修改模板
        $result = $ctlModel->mod($id, $data);
        
        if($result){
            Utils_Output::errorResponse('OK', 0);
            exit;
            
        }else {
            Utils_Output::errorResponse('未做修改或其它错误');
            exit;
        }
        return FALSE;       
    }

    public function deleteTemplateLibAjaxAction() {
        $ctlModel =  new ContractTemplateLibraryModel();
        
        //获取模板ID
        $id = (int) $this->getRequest()->getPost('id');
        
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            Utils_Output::errorResponse('模板ID获取失败');
            exit;
        }

        $temp_info = $ctlModel->get($id);
        if (!$temp_info) {
            Utils_Output::errorResponse('模板信息获取失败');
            exit;
        }
        
        //把模板置为已删除状态
        $delData = array();
        $delData['status'] = 4;
        $result = $ctlModel->mod($id,$delData);
        
        if($result){
            Utils_Output::errorResponse('OK', 0);
            exit;
        }else {
            Utils_Output::errorResponse('删除失败');
            exit;
        }
        return FALSE;       
    }

    public function templateLibMakeAction() {
        $ctlModel =  new ContractTemplateLibraryModel();
        
        //模板Id
        $id = $this->getRequest()->get('id', "");
        $id = htmlspecialchars($id);
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            Utils_Output::errorMsg('模板ID获取失败');
        }
        
        //根据模板Id查出模板详情
        $temp_info = $ctlModel->get($id);
        if (!$temp_info) {
            Utils_Output::errorMsg('未查询到对应模板');
        }
        
        //到服务器查询是否已有模板,如果有读出渲染到页面
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        $template_path = $config['root'].$temp_info['path'];
        $content = file_get_contents($template_path);
        
//        $contract_template_dir = 'D:\\image/contract/template/' .$temp_info['oid'];
//        $file_name = trim($temp_info['oid']."_".$temp_info['type']."_".$temp_info['id'].".html");
//        $template_file_path = $contract_template_dir. "/" .$file_name;  

        
        $this->_tpl->assign('id', $id);
        $this->_tpl->assign('temp_info', $temp_info);
        $this->_tpl->assign('con', $content);
    }

    public function getLibContentAjaxAction() {
        $ctlModel =  new ContractTemplateLibraryModel();
        
        //获取表单数据
        $data = $this->getRequest()->getPost();
        
        //检验表单数据
        if (empty($data['id']) || !is_numeric($data['id']) || $data['id'] <= 0) {
            Utils_Output::errorResponse('模板ID获取失败');
            exit;
        }
        
        if (empty($data['all_content'])) {
            Utils_Output::errorResponse('请填写模板内容');
            exit;
        }
        
        //根据模板Id查出模板详情
        $temp_info = $ctlModel->get($data['id']);
        if (!$temp_info) {
            Utils_Output::errorResponse('未查询到对应模板');
            exit;
        }
        
        $data['all_content'] = "<meta charset='utf-8' />".$data['all_content'];
        $temp_info['content'] = $data['all_content'];
       
        //拼接合同模板文件路径,并写入到服务器中
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        //$contract_template_dir = 'D:\\image/contract/template/' .$temp_info['oid']; 
        $contract_template_dir = 'fk/contract/library'; 
        $upload = new Utils_Upload($contract_template_dir, $config['root']);
        $upload_result = $upload->upload_contract($temp_info, 3);
        
        if($upload_result){
            //生成或编辑模板成功后总把模板状态改为已制作
            $modData = array();
            $modData['status'] = 1;
            $modData['path'] = $upload_result;
            $ctlModel->mod($data['id'],$modData);

            $ctModel = new ContractTemplateModel();
            $temp_list = $ctModel->getlistbylid($data['id']);
            foreach($temp_list as $k=>$v){
                $contract_file_path = $config['root'].$v['path'];
                $byte_size = file_put_contents($contract_file_path, $temp_info['content']);
            }
            
            Utils_Output::errorResponse('OK', 0);
            exit;
        }
        else{
            Utils_Output::errorResponse('合同模板生成失败');
            exit;
        }
        
    }

    //关联模板
    public function relationTemplateAction(){
        //查出所有机构
        $orgModel = new OrganizeModel();
        $orgs = $orgModel->getCdOrgs();

        //查出模板库的所有模板
        $ctlModel =  new ContractTemplateLibraryModel();
        $tempList = $ctlModel->getList();
        $this->_tpl->assign('orgs', $orgs);
        $this->_tpl->assign('tempList', $tempList);
    }
    //关联模板操作
    public function relationTemplateAjaxAction(){
        $oid = (int)$this->getRequest()->get('oid', 0);
        $temps = htmlspecialchars($this->getRequest()->get('temps', ''));

        if(empty($oid) || $oid==0 || !is_numeric($oid)){
            Utils_Output::errorResponse('错误的机构id');
            exit;
        }

        $temps = explode(',', $temps);
        if(empty($temps) || !is_array($temps)){
            Utils_Output::errorResponse('未选择模板');
            exit;
        }

        $ctModel = new ContractTemplateModel();
        // $del = $ctModel->del($oid, $temps);
        $templist = $ctModel->getLids($oid);

        $ctlModel = new ContractTemplateLibraryModel();
        $liblist = $ctlModel->gets($temps);

        //拼接合同文件路径,并写入到服务器中
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();

        foreach($liblist as $k=>$v){
            // //文件完整路径
            // $contract_file_path = $config['root'].$contract_info['path'];
            // //将模板内容写入服务器文件
            // $byte_size = file_put_contents($contract_file_path, $data['all_content']);
            // $liblist[$k]['file_content'] = file_get_contents($v['path']);
            foreach($templist as $kk=>$vv){
                if($v['id'] == $vv['lid']){
                    $content = file_get_contents($config['root'].$v['path']);
                    $contract_file_path = $config['root'].$vv['path'];
                    $byte_size = file_put_contents($contract_file_path, $content);
                    unset($liblist[$k]);
                }
            }
        }

        //查询本机构下type为最大值的模板,如果不存在新增模板的type为1,否则为当前type+1
        $type = $ctModel->getMaxType($addDatas['oid']);
        if(!empty($type)){
            $type = $type + 1;
        }
        else{
            $type = 1;
        }

        if(is_array($liblist) && count($liblist)>0){
            foreach($liblist as $k=>$v){
                $temp_info['oid'] = $oid;
                $temp_info['type'] = $type;
                $temp_info['id'] = $v['id'];
                $temp_info['content'] = file_get_contents($config['root'].$v['path']);
                $contract_template_dir = 'fk/contract/template/' .$oid; 
                $upload = new Utils_Upload($contract_template_dir, $config['root']);
                $upload_result = $upload->upload_contract($temp_info);

                $addData['oid'] = $oid;
                $addData['lid'] = $v['id'];
                $addData['type'] = $type;
                $addData['name'] = $v['name'];
                $addData['status'] = 1;
                $addData['path'] = $upload_result;
                //添加合同模板
                $result = $ctModel->addData($addData);
                $type++;
            }
        }
        
        Utils_Output::errorResponse('OK', 0);
        exit;
    }


    public function getNewTempAction(){
        $oid = (int)$this->getRequest()->get('oid', 0);
        if(!$oid){
            Utils_Output::errorResponse('获取机构id失败');
            exit;
        }

        $ctlModel = new ContractTemplateLibraryModel();
        $temp_list = $ctlModel->getList();
        $ctModel = new ContractTemplateModel();
        $lids = $ctModel->getLids($oid);

        foreach($temp_list as $kk=>$vv){
            foreach($lids as $v){
                if($v['lid'] == $vv['id']){
                    $temp_list[$kk]['is_check'] = 1;
                }
            }
        }
        if(!empty($temp_list)){
            $data['error'] = 0;
            $data['data'] = $temp_list;
            $data['msg'] = '获取成功';
            echo json_encode($data);
            exit;
        }else{
            Utils_Output::errorResponse('获取失败');
            exit; 
        }
    }

    public function printInsuranceAction(){
        $app_id = $this->getRequest()->get('app_id',0);
        $app_id = htmlspecialchars($app_id);

        $cents = $this->getRequest()->get('sort',0);
        $cents = htmlspecialchars($cents);

        if(empty($app_id) || empty($cents)){
            Utils_Output::errorMsg('参数获取失败');
        }

        $appModel = new Cd_AppModel();
        $app_info = $appModel->getContractCustomerInfo($app_id);

        //评估价值
        $data['estimate_total'] = $appModel->getCarEst($app_id)['daizhong_loan'];
        //贷款金额
        $data['loan'] = $this->getDecimal($data['estimate_total'] * 70 / 100 * 10000);
        //利率
        $data['rate'] = $app_info['rate'] / 100;
        //还款期限
        $data['deadline'] = $app_info['deadline'];
        //每月还款本金
        $data['per_amount'] = $this->getDecimal($data['loan'] / $data['deadline']);
        //每月还款利息
        $data['per_rate'] = $this->getDecimal($data['loan'] * $data['rate']);
        //每月还款本息和
        $data['per_total'] = $this->getDecimal($data['per_amount'] + $data['per_rate']);

        $cnt = (int)$app_info['deadline'] / 12;
        $cent = ceil($cents/2);
        for($i=1; $i<=$cnt; $i++){
            // if($cent == $i){
                if($i == 1){
                    $data['amount'.$i] = $this->getDecimal($data['loan'] - 12 * ($i-1) * $data['per_amount']);
                    $data['baoxian'.$i] = $this->getDecimal($data['amount'.$i] + $data['per_rate'] * $data['deadline']);
                    $data['rate'.$i] = $this->getDecimal($data['baoxian'.$i] - $data['loan']);
                    $data['total'.$i] = $this->getDecimal($data['baoxian'.$i] * 2 / 100);
                }else{
                    $data['baoxian'.$i] = $this->getDecimal($data['baoxian'.($i-1)] - $data['per_total'] * 12);
                    $data['amount'.$i] = $this->getDecimal($data['loan'] - 12 * ($i-1) * $data['per_amount']);
                    $data['rate'.$i] = $this->getDecimal($data['baoxian'.$i] - $data['amount'.$i]);
                    $data['total'.$i] = $this->getDecimal($data['baoxian'.$i] * 2 / 100);
                }
                $data['amount_cp_'.$i] = $this->cny($data['amount'.$i]);
                $data['baoxian_cp_'.$i] = $this->cny($data['baoxian'.$i]);
                $data['rate_cp_'.$i] = $this->cny($data['rate'.$i]);
                $data['total_cp_'.$i] = $this->cny($data['total'.$i]);
            // }
        }

        $this->_tpl->assign('app', $app_info);
        $this->_tpl->assign('data', $data);
        $this->_tpl->assign('cent', $cent);
        if($cents%2 == 1){
            $this->_tpl->display('contract/insurance_one.html');exit;
        }else{
            $this->_tpl->display('contract/insurance_two.html');exit;
        }
    }

    public function _getInsuranceInfo($app_id){
        $appModel = new Cd_AppModel();
        $app_info = $appModel->getContractCustomerInfo($app_id);

        //评估价值
        $data['estimate_total'] = $appModel->getCarEst($app_id)['daizhong_loan'];
        //贷款金额
        $data['loan'] = $this->getDecimal($data['estimate_total'] * 70 / 100 * 10000);
        //利率
        $data['rate'] = $app_info['rate'] / 100;
        //还款期限
        $data['deadline'] = $app_info['deadline'];
        //每月还款本金
        $data['per_amount'] = $this->getDecimal($data['loan'] / $data['deadline']);
        //每月还款利息
        $data['per_rate'] = $this->getDecimal($data['loan'] * $data['rate']);
        //每月还款本息和
        $data['per_total'] = $this->getDecimal($data['per_amount'] + $data['per_rate']);

        $cnt = (int)$app_info['deadline'] / 12;

        for($i=1; $i<=$cnt; $i++){                
            if($i == 1){
                $data['amount'.$i] = $data['loan'] - 12 * ($i-1);
                $data['baoxian'.$i] = $this->getDecimal($data['amount'.$i] + $data['per_rate'] * $data['deadline']);
                $data['rate'.$i] = $data['baoxian'.$i] - $data['loan'];
                $data['total'.$i] = $data['baoxian'.$i] * 2 / 100;
            }else{
                $data['baoxian'.$i] = $data['baoxian'.($i-1)] - $data['per_total'] * 12 * ($i-1);
                $data['amount'.$i] = $data['loan'] - 12 * ($i-1);
                $data['rate'.$i] = $data['baoxian'.$i] - $data['amount'.$i];
                $data['total'.$i] = $data['baoxian'.$i] * 2 / 100;
            }
            $data['amount_cp_'.$i] = $this->cny($data['amount'.$i]);
            $data['baoxian_cp_'.$i] = $this->cny($data['baoxian'.$i]);
            $data['rate_cp_'.$i] = $this->cny($data['rate'.$i]);
            $data['total_cp_'.$i] = $this->cny($data['total'.$i]);
        }
        return $data;
    }

    public function insuranceIframeAction(){
        $app_id = (int)$this->getRequest()->get('app_id', 0);
        if(!$app_id){
            return false;
        }
        $appModel = new Cd_AppModel();
        $app_info = $appModel->getContractCustomerInfo($app_id);
        //评估价值
        $data['estimate_total'] = $appModel->getCarEst($app_id)['daizhong_loan'];
        //贷款金额
        $data['loan'] = $this->getDecimal($data['estimate_total'] * 70 / 100 * 10000);
        //利率
        $data['rate'] = $app_info['rate'] / 100;
        //还款期限
        $data['deadline'] = $app_info['deadline'];
        //每月还款本金
        $data['per_amount'] = $this->getDecimal($data['loan'] / $data['deadline']);
        //每月还款利息
        $data['per_rate'] = $this->getDecimal($data['loan'] * $data['rate']);
        //每月还款本息和
        $data['per_total'] = $this->getDecimal($data['per_amount'] + $data['per_rate']);
        //利率（用于显示）
        $data['show_rate'] = $app_info['rate'];

        $cnt = (int)$app_info['deadline'] / 12;

        for($i=1; $i<=$cnt; $i++){
            if($i == 1){
                $data['amount'.$i] = $this->getDecimal($data['loan'] - 12 * ($i-1) * $data['per_amount']);
                $data['baoxian'.$i] = $this->getDecimal($data['amount'.$i] + $data['per_rate'] * $data['deadline']);
                $data['rate'.$i] = $this->getDecimal($data['baoxian'.$i] - $data['loan']);
                $data['total'.$i] = $this->getDecimal($data['baoxian'.$i] * 2 / 100);
            }else{
                $data['baoxian'.$i] = $this->getDecimal($data['baoxian'.($i-1)] - $data['per_total'] * 12);
                $data['amount'.$i] = $this->getDecimal($data['loan'] - 12 * ($i-1) * $data['per_amount']);
                $data['rate'.$i] = $this->getDecimal($data['baoxian'.$i] - $data['amount'.$i]);
                $data['total'.$i] = $this->getDecimal($data['baoxian'.$i] * 2 / 100);
            }
            $data['amount_cp_'.$i] = $this->cny($data['amount'.$i]);
            $data['baoxian_cp_'.$i] = $this->cny($data['baoxian'.$i]);
            $data['rate_cp_'.$i] = $this->cny($data['rate'.$i]);
            $data['total_cp_'.$i] = $this->cny($data['total'.$i]);
        }
        $this->_tpl->assign('app', $app_info);
        $this->_tpl->assign('insurance', $data);
        $this->_tpl->assign('cnt', $cnt);
    }

    public function insuranceCustomerAction(){
        $app_id = (int)$this->getRequest()->get('app_id', 0);
        if(!$app_id){
            return false;
        }
        $appModel = new Cd_AppModel();
        $app_info = $appModel->getContractCustomerInfo($app_id);
        $birth_path = strpos($app_info['birthday'], '-') + 1;
        $app_info['birth'] = str_replace('-', '/', substr($app_info['birthday'],$birth_path));
        $app_info['age'] = $this->getAge($app_info['birthday']);
        $app_info['temp_add'] = mb_substr($app_info['temp_address'],0,20,'utf-8');
        $this->_tpl->assign('app', $app_info);
    }

    //转换2位小数
    public function getDecimal($data){
        return number_format($data, 2, '.', ''); 
    }
    //金额转换大写
    public function cny($num){

        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2); 
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 15) {
                return "金额太大，请检查";
        } 
        $i = 0;
        $c = "";
        while (1) {
                if ($i == 0) {
                        //获取最后一位数字
                        $n = substr($num, strlen($num)-1, 1);
                } else {
                        $n = $num % 10;
                }
                //每次将最后一位数字转化为中文
                $p1 = substr($c1, 3 * $n, 3);
                $p2 = substr($c2, 3 * $i, 3);
                if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                        $c = $p1 . $p2 . $c;
                } else {
                        $c = $p1 . $c;
                }
                $i = $i + 1;
                //去掉数字最后一位了
                $num = $num / 10;
                $num = (int)$num;
                //结束循环
                if ($num == 0) {
                        break;
                } 
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
                //utf8一个汉字相当3个字符
                $m = substr($c, $j, 6);
                //处理数字中很多0的情况,每次循环去掉一个汉字“零”
                if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                        $left = substr($c, 0, $j);
                        $right = substr($c, $j + 3);
                        $c = $left . $right;
                        $j = $j-3;
                        $slen = $slen-3;
                } 
                $j = $j + 3;
        } 
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c)-3, 3) == '零') {
                $c = substr($c, 0, strlen($c)-3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
                return "零元整";
        }else{
                return $c . "整";
        }
    }

    /**
     * 根据生日计算年龄
     * @param string $birthday
     * @return number
     */
    // public function getAge($birthday) {

    //     $nowyear = date('Y');
    //     $nowmonth = date('m');
    //     $nowday = date('d');
        
    //     list ($year, $month, $day) = explode('-', $birthday);
    //     if ($nowyear < $year) {
    //         return FALSE;
    //     }
        
    //     $age = 0;
    //     if ($nowmonth > $month) {
    //         $age = $nowyear - $year;
    //     } else {
    //         if ($nowday > $day) {
    //             $age = $nowyear - $year;
    //         } else {
    //             $age = $nowyear - $year - 1;
    //         }
    //     }
    //     return $age;
    // }
}