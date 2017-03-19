<?php
class UserController extends Yaf_Controller_Abstract {
    private $_tpl;
    private $_organizeModel;
    private $_departmentModel;
    private $_userModel;
    private $_operationModel;
    private $_role;

    public function init() {
        $this->_tpl = $this->getView();
        $this->_organizeModel = new OrganizeModel();
        $this->_departmentModel = new DepartmentModel();
        $this->_userModel = new AdminUserModel();
        $this->_operationModel = new OperationModel();
        $this->_cdlinkModel = new Cd_LinkModel();
        $this->_role = new RoleModel();
        $this->_host = Yaf_Application::app()->getConfig()->get("website")['host'];
    }

    public function indexAction() {

    }

    public function getUserListAjaxAction() {

        $user_realname = trim($this->getRequest()->get('user_name', ''));
        $phone = trim($this->getRequest()->get('phone', ''));
        $role_name = trim($this->getRequest()->get('role_name', ''));
        $org_name = trim($this->getRequest()->get('org_name', ''));
        $type = trim($this->getRequest()->get('type', ''));
//        var_dump($user_realname,$phone,$role_name,$org_name);exit;
        if (!empty($user_realname)) {
            $where['realname'] = array("like", '%' . $user_realname . '%');;
        }
        if (!empty($phone)) {
            $where['phone'] = $phone;
        }
        if (!empty($role_name)) {
            $where['role_name'] = $role_name;
        }
        if (!empty($org_name)) {
            $where['org_name'] = $org_name;
        }
        if (!empty($type)) {
            $where['type'] = $type;
        }

        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" =>'',
            "data" => array()
        );
        $userCount = $this->_userModel->getUserCount($where);
        if($userCount > 0 ){
            $userList = $this->_userModel->getUser($where,$_POST['start'],$_POST['length']);
        }else{
            $output['recordsFiltered'] = 0;
            $output['data'] = '';
            echo json_encode( $output );exit;
        }
        // $userList = $this->_cdlinkModel->getLeaderListByUsers($userList);
//        if(!empty($userList) && is_array($userList)){
//            $organizeIds = array();
//            $departmentIds = array();
//            $roleIds = array();
//            foreach($userList as $val){
//                $organizeIds[] = $val['oid'];
//                $departmentIds[] = $val['did'];
//            }
//        }
//        $organizeList = $this->_organizeModel->getOrganizeListByOids($organizeIds);
//        $departmentList = $this->_departmentModel->getDepartmentListByDids($departmentIds);
//        $userList = $this->_userModel->formatUserList($userList,$organizeList,$departmentList);
        $output['recordsFiltered'] = $userCount;
        $output['data'] = $userList;
        echo json_encode( $output );exit;

    }

    //选择机构时
    public function getDepartmentItemAjaxAction(){
        $data = array(
            'error' => 1,
            'errorMsg' => '',
            'data'=>array()
        );
        $oid = (int)$_POST['oid'];
        if(!$oid){
            $data['errorMsg'] = '机构编号获取失败';
            echo json_encode($data);
            exit;
        }
        $departmentItemInfo = $this->_departmentModel->getDepartmentItemByOid($oid);
        if(empty($departmentItemInfo)){
            $data['errorMsg'] = '部门信息获取失败';
            echo json_encode($data);
            exit;
        }
        $data['error'] = 0;
        $data['errorMsg'] = '获取成功';
        $data['data'] = $departmentItemInfo;
        echo json_encode($data);exit;

    }

    public function addUserAction() {

    }

    public function addUserAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        $data_role = array();
        $addDatas = $this->getRequest()->getPost();
        $addDatas = Utils_FilterXss::filterArray($addDatas);
        foreach($addDatas as $key=>$addk){
            if(substr($key , 0 , 6) == 'roleid'){
                $data_role[] = $addk;
                unset($addDatas[$key]);
            }
        }
        unset($addDatas['username']);


        if (!$addDatas['realname'] || !$addDatas['phone'] || !$addDatas['oid'] || !$addDatas['did']) {
            $data['errorMsg'] = '参数缺失';
            echo json_encode($data);
            exit;
        }


        //检查phone是否有重复
        $info = $this->_userModel->nameExist($addDatas['phone']);
        if(!empty($info)){
            $data['errorMsg'] = '手机号重复';
            echo json_encode($data);exit;
        }

        $len = strlen($addDatas['phone']);
        if($len < 11 || $len > 11 || !preg_match("/1[3|4|5|7|8]{1}[0-9]{9}$/",$addDatas['phone']) ){
            $data['errorMsg'] = '手机号格式不正确';
            echo json_encode($data);exit;
        }

        //生成随机密码
        $str = substr(md5(time()), 0, 6);
        $addDatas['passwd'] = $str;
        $result = $this->_userModel->add($addDatas);
        if ($result) {
            //生成角色
            $this->_role->setusrtrole($result,$data_role);
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "用户列表";
            $logInfo['option'] = "添加用户";
            $addDatas['id'] = $result;
            $logInfo['new_data'] = Utils_Helper::arrayToString($addDatas);
            $this->_operationModel->add($logInfo);
            $resqueQueue = new Resque_Queue();
            $token = $resqueQueue->enqueue('fk_sms', 'Sms_PHP_Job', array( 'name'=>'sms', 'phone'=>$addDatas['phone'], 'type'=>4, 'param'=>array('passwd'=>$str) , 'smstype'=>1));

            //同时注册微信M端用户
            $reportUserModel = new ReportUserModel();
            $report_result = $reportUserModel->findUser($addDatas['phone']);
            if(!$report_result){
                $report_user = array();
                $report_user['username'] = $addDatas['realname'];
                $report_user['passwd'] = $addDatas['passwd'];
                $report_user['phone'] = $addDatas['phone'];
                $report_user['reg_ip'] = Util_Tool::getRealIP();
                $report_user['org_id'] = $addDatas['oid'];//机构关联
                $report_user['login_status'] = 2;//登录状态(未登录)
                $res = $reportUserModel->add($report_user);

                //用户注册成功之后生成用户邀请码
                if ($res) {
                    $inviteInfoModel = new ReportInviteInfoModel();
                    $addData = array();
                    $addData['user_id'] = $res;
                    $addData['code'] = 1000 + (int) $res;
                    $addData['create_time'] = time();
                    $inviteInfoModel->add($addData);
                }

            }

            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            echo json_encode($data);exit;
        } else {
            $data['errorMsg'] = '注册失败';
            echo json_encode($data);exit;
        }

    }

    public function editUserAction() {
        $id = (int)$this->getRequest()->get('id');
        if ($id <= 0) {
            echo ('用户ID获取失败');
        }

        $info = $this->_userModel->get($id);
        if (!$info) {
            echo ('用户信息获取失败');
        }
        $orginfo = $this->_organizeModel->get($info['oid']);

        if($orginfo['insti_attr'] == 6){
            $personal = 1;
        }else{
            $personal = 0;
        }
        //所属机构下部门列表
        $departmentInfo = $this->_departmentModel->getDepartmentItemByOid($info['oid']);

        $this->_tpl->assign('orginfo',$orginfo);
        $this->_tpl->assign('departmentInfo',$departmentInfo);
        $this->_tpl->assign('info', $info);
        $this->_tpl->assign('personal', $personal);
    }

    public function editUserAjaxAction() {
        $id = (int)$this->getRequest()->get('id');
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if ($id <= 0 || !$data['realname'] || !$data['phone'] || !$data['oid'] || !$data['did']) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
        $len = strlen($data['phone']);
        if($len < 11 || $len > 11 || !preg_match("/1[3|4|5|7|8]{1}[0-9]{9}$/",$data['phone']) ){
            Utils_Output::errorResponse('手机号格式不正确');exit;
        }
        //获取修改前数据
        $oldData = $this->_userModel->get($id);
        foreach($data as $key=>$addk){
            if(substr($key , 0 , 6) == 'roleid'){
                $data_role[] = $addk;
                unset($data[$key]);
            }
        }
        unset($data['username']);
        $roleinfo = $this->_role->setusrtrole($id,$data_role);
        if ($this->_userModel->mod($id, $data)) {
            //生成角色

            //判断修改后的机构是否为个人
            $org_info = $this->_organizeModel->get($data['oid']);
            if($org_info['insti_attr'] != 6){
                //不是个人机构,将跟个人机构相关的字段值清空
                $this->_userModel->changFour($id);
            }
            //记录日志
            $newData = $this->_userModel->get($id);
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "用户列表";
            $logInfo['option'] = "修改用户及角色";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = Utils_Helper::arrayToString($newData);

            $this->_operationModel->add($logInfo);

            Utils_Output::errorResponse('OK',0);exit;
        } else if($roleinfo){
            //记录日志
            $newData = $this->_userModel->get($id);
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "用户列表";
            $logInfo['option'] = "修改用户角色";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $logInfo['new_data'] = Utils_Helper::arrayToString($newData);

            $this->_operationModel->add($logInfo);
            Utils_Output::errorResponse('OK',0);exit;
        } else {
            Utils_Output::errorResponse('未做修改或其它错误');exit;
        }
        return FALSE;
    }

    public function deleteUserAjaxAction() {
        $id = (int)$this->getRequest()->getPost('id');
        if ($id <= 0) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
        //获取删除前数据
        $oldData = $this->_userModel->get($id);
        if ($this->_userModel->del($id)) {
            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "用户列表";
            $logInfo['option'] = "删除用户";
            $logInfo['old_data']  = Utils_Helper::arrayToString($oldData);
            $this->_operationModel->add($logInfo);
            Utils_Output::errorResponse('删除成功',0);exit;
        } else {
            Utils_Output::errorResponse('删除失败');exit;
        }
        return FALSE;
    }


    public function modifyUserPwdAction(){
        $userId = (int)$this->getRequest()->get('id');
        //获取用户信息
        $userInfo = $this->_userModel->get($userId);
        if(!empty($userInfo)){
            $phone = Utils_Helper::hidPhone($userInfo['phone']);
            $realName = $userInfo['realname'];
        }else{
            $phone = "获取失败";
            $realName = "获取失败";
        }
        $this->_tpl->assign('id',$userId);
        $this->_tpl->assign('phone',$phone);
        $this->_tpl->assign('realname',$realName);
    }

    public function modifyUserPwdAjaxAction(){
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
        $postDatas = $this->getRequest()->getPost();
        $userId = $postDatas['id'];
        $newpwd = $postDatas['newuserpwd'];
        $confirmpwd = $postDatas['confirmuserpwd'];
        if (!$newpwd || !$confirmpwd) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
        if (strlen($newpwd) < 6) {
            Utils_Output::errorResponse('新密码长度不符合要求');exit;
        }
        if ($newpwd != $confirmpwd) {
            Utils_Output::errorResponse('两次密码不一致');exit;
        }
        //修改密码
        $info = $this->_userModel->changePwd($userId,$newpwd);
        if($info){
            //查询用户详情
            $userinfo = $this->_userModel->get($userId);

            //同时修改微信M端密码
            $reportUserModel = new ReportUserModel();
            $report_user = $reportUserModel->findUser($userinfo['phone']);
            if($report_user){
                $reportUserModel->changePwd($report_user['id'], $newpwd);
            }

            //记录日志
            $userId = Session_AdminFengkong::instance()->getUid();
            $logInfo = array();
            $logInfo['user_id'] = $userId;
            $logInfo['model'] = "用户列表";
            $logInfo['option'] = "修改用户密码";
            $logInfo['new_data'] = "id:{$userId}";
            $this->_operationModel->add($logInfo);
            Utils_Output::errorResponse('密码修改成功',0);exit;
        }else{
            Utils_Output::errorResponse('密码修改失败');exit;
        }

    }

    public function addleaderAction(){
        $id = (int)$this->getRequest()->get('id');
        if($id <= 0){
            echo ('用户ID获取失败');
        }

        $roles = (new RoleModel())->getUserRoles($id);

        $linkModel = new Cd_LinkModel();
        $link_list = $linkModel->getUserLinkList($id);
        //获取已有用户列表
        $user_list = $this->_userModel->getUserList();
        // $roleModel = new RoleModel();
        //判断选中人是否有多个角色 若没有则删除
        foreach($user_list as $key=>$value){
            if($value['id'] == $id){
                $user_name = $value['realname'];
                // unset($user_list[$key]);
            }
        }
        $this->_tpl->assign('roles', $roles);
        $this->_tpl->assign('id', $id);
        $this->_tpl->assign('realname', $user_name);
        $this->_tpl->assign('user_list', $user_list);
        $this->_tpl->assign('link_list', $link_list);
        $this->_tpl->display();
    }

    public function addLeaderAjaxAction(){
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if(empty($data['id']) || empty($data['pid']) || empty($data['role_id'])){
            Utils_Output::jsonResponse(101, '获取信息失败', '获取信息失败');
        }
        $linkModel = new Cd_LinkModel();
        if($linkModel->addlink($data['id'], $data['pid'], $data['role_id'])){
            Utils_Output::jsonResponse(0, '','添加成功');
        }else{
            Utils_Output::jsonResponse(101, '', '未做修改或其它错误');
        }
    }

    public function showleaderAction(){
        $id = (int)$this->getRequest()->get('id');
        if($id <= 0){
            echo ('用户ID获取失败');
        }
        $linkModel = new Cd_LinkModel();
        $link_list = $linkModel->getUserLinkList($id);
        //获取已有用户列表
        $user_list = $this->_userModel->getUserList();
        // $roleModel = new RoleModel();
        //判断选中人是否有多个角色 若没有则删除
        foreach($user_list as $key=>$value){
            if($value['id'] == $id){
                $user_name = $value['realname'];
                // unset($user_list[$key]);
            }
        }
        $this->_tpl->assign('id', $id);
        $this->_tpl->assign('realname', $user_name);
        $this->_tpl->assign('user_list', $user_list);
        $this->_tpl->assign('link_list', $link_list);
        $this->_tpl->display();
    }

    public function checkOrgAjaxAction(){
        $oid = (int)$_POST['oid'];
        //$oid = (int)$this->getRequest()->getPost('oid');
        $org_info = $this->_organizeModel->get($oid);
        $data = array(
            'error' => 1,
            'errorMsg' => '',
            'data'=>array(),
            'person' => 0
        );

        if(!$oid){
            $data['errorMsg'] = '机构编号获取失败';
            echo json_encode($data);
            exit;
        }

        if(empty($org_info)){
            $data['errorMsg'] = '机构属性获取失败';
            echo json_encode($data);
            exit;
        }

        if($org_info['insti_attr'] == 6){
            $data['person'] = 1;
        }
        $data['error'] = 0;
        $data['errorMsg'] = '获取成功';
        echo json_encode($data);exit;

    }

    public function getuserinfoajaxAction(){
        $data = $this->getRequest()->getPost();
        $oldid_data = htmlspecialchars($data['oldid']);
        $data = Utils_FilterXss::filterArray($data);
        if ($this->_host != "fk.yianjinrong.com" && $this->_host != "prefk.yianjinrong.com") {
            $role = array(5,6,10,13,33,35,36,46,60,50,51,52,53,59,61,62,63,65,69,70);
        } else {
            $role = array(5,6,10,13,26,27,35,38,55,46,47,48,49,39,56,57,58,59,62,63);
            //报单,客服,初审,公证抵押专员,风控leader,下户专员,初审leader,权证风控leader,放款申请人
            //全国车贷业务员、全国车贷评估师、全国车贷风控经理、全国车贷总部初审、车贷合同岗、全国车贷分公司材料岗位


        }

        $result = array();
        $i = 1;

        if(!empty($oldid_data)){
            $oldid = explode(',',$oldid_data);
            $oldidinfo = $this->_role->getallidrole($oldid);
            foreach($oldidinfo as $old){
                $result[$old['id']]['id'] = $old['id'];
                $result[$old['id']]['name'] = $old['name'];
                $result[$old['id']]['for'] = 1;
            }
        }

        if(empty($data['key'])){
            //默认角色
            foreach($role as $key=>$rol){
                if(in_array($rol,$oldid)){
                    unset($role[$key]);
                }
            }
            $roleinfo = $this->_role->getallidrole($role);
            foreach($roleinfo as $rolei){
                $result[$rolei['id']]['id'] = $rolei['id'];
                $result[$rolei['id']]['name'] = $rolei['name'];
                $result[$rolei['id']]['for'] = 0;
            }
            $rdata['error'] = 1;
            $rdata['data'] = $result;
        }else{
            $userinfo = $this->_role->getlikerole($data['key']);
            if($userinfo){
                foreach($userinfo as $key=>$use){

                    if(!in_array($use['id'],$oldid)){

                        $result[$use['id']]['id'] = $use['id'];
                        $result[$use['id']]['name'] = $use['name'];
                        $result[$use['id']]['for'] = 0;
                    }
                }
                $rdata['error'] = 1;
            }else{
                foreach($role as $key=>$rol){
                    if(in_array($rol,$oldid)){
                        unset($role[$key]);
                    }
                }

                $roleinfo = $this->_role->getallidrole($role);
                foreach($roleinfo as $rolei){
                    $result[$rolei['id']]['id'] = $rolei['id'];
                    $result[$rolei['id']]['name'] = $rolei['name'];
                    $result[$rolei['id']]['for'] = 0;
                }
                $rdata['error'] = 0;
            }
            $rdata['data'] = $result;
        }

        echo json_encode($rdata);exit;

    }

    public function getroleinfoajaxAction(){
        $data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);


        if(!$data['id']){
            $rdata['error'] = 2;
            $rdata['errormsg'] = '此用户不存在';
            echo json_encode($rdata);exit;
        }

        /* if ($this->_host != "fk.yianjinrong.com" && $this->_host != "prefk.yianjinrong.com") {

            $role = array('5'=>'报单',
                          '27'=>'下户专员',
                          '13'=>'公证抵押专员',
                          '6'=>'客服',
                          '10'=>'初审',
                          '35'=>'初审leader',
                          '26'=>'风控leader',
                          //'5'=>'分配公证抵押',
                          '38'=>'分配公证抵押风控leader',
                          '55'=>'放款申请人',
            );
        } else {
            $role = array('5'=>'报单',
                          '35'=>'下户专员',
                          '13'=>'公证抵押专员',
                          '6'=>'客服',
                          '10'=>'初审',
                          '36'=>'初审leader',
                          '33'=>'风控leader',
                          //'5'=>'分配公证抵押',
                          '46'=>'分配公证抵押风控leader',
                          '60'=>'放款申请人',
            );

        } */

        if ($this->_host != "fk.yianjinrong.com" && $this->_host != "prefk.yianjinrong.com") {
            $role = array(5,6,10,13,33,35,36,46,60,50,51,52,53,59,61,62,63,65,69,70);
        } else {
            $role = array(5,6,10,13,26,27,35,38,55,46,47,48,49,39,56,57,58,59,62,63);
            //报单,客服,初审,公证抵押专员,风控leader,下户专员,初审leader,权证风控leader,放款申请人
            //全国车贷业务员、全国车贷评估师、全国车贷风控经理、全国车贷总部初审、车贷合同岗、全国车贷分公司材料岗位


        }
        $info = $this->_role->getUserRole($data['id']);
        $infoq = array();
        if($info){
            foreach($info as $ino){
                $infoq[] = $ino['role_id'];
            }
            $oldidinfo = $this->_role->getallidrole($infoq);
            foreach($oldidinfo as $key=>$rol){
                $result[$rol['id']]['id'] = $rol['id'];
                $result[$rol['id']]['name'] = $rol['name'];
                $result[$rol['id']]['for'] = 1;
            }
            $rdata['error'] = 1;
        }else{
            //默认角色
            /* foreach($role as $key=>$rol){

                $result[$key]['id'] = $key;
                $result[$key]['name'] = $rol;
                $result[$key]['for'] = 0;

            } */
            $roleinfo = $this->_role->getallidrole($role);
            foreach($roleinfo as $rolei){
                $result[$rolei['id']]['id'] = $rolei['id'];
                $result[$rolei['id']]['name'] = $rolei['name'];
                $result[$rolei['id']]['for'] = 0;
            }
            $rdata['error'] = 0;
        }

        $rdata['data'] = $result;
        echo json_encode($rdata);exit;
    }


}