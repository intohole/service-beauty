<?php
class WechatorderController extends Yaf_Controller_Abstract {

    private $_reportOrderModel;

	public function init() {
        $this->_tpl = $this->getView();
        $this->_reportOrderModel = new ReportOrderModel();
	}
	
	public function indexAction() {

        $userName = Session_AdminFengkong::instance()->getUserName();
        $userId = Session_AdminFengkong::instance()->getUid();

        $menus = (new RoleModel())->getUserMenus($userId);

        $this->_tpl->assign('menus', $menus);
        $this->_tpl->assign('username', $userName);
	}


    public function submitReportAction(){

    }

    public function submitReportAjaxAction(){

        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );

        $city = trim($this->getRequest()->getPost('city',''));
        $city = Utils_FilterXss::filterXss($city);
        $district = trim($this->getRequest()->getPost('district',''));
        $district = Utils_FilterXss::filterXss($district);
        $house_site = trim($this->getRequest()->getPost('housesite',''));
        $house_site = Utils_FilterXss::filterXss($house_site);
        $house_site = $city." ".$district." ".$house_site;
        $house_area = floatval($this->getRequest()->getPost('housearea',0));
        $house_type = (int)$this->getRequest()->getPost('housetype','');

        $borrower_name = trim($this->getRequest()->getPost('name',''));
        $borrower_name = Utils_FilterXss::filterXss($borrower_name);
        $borrower_idcard = trim($this->getRequest()->getPost('id',''));
        $borrower_idcard = Utils_FilterXss::filterXss($borrower_idcard);
        $borrower_money = floatval($this->getRequest()->getPost('money',''));

        $user_name = trim($this->getRequest()->getPost('username',''));
        $user_name = Utils_FilterXss::filterXss($user_name);
        $phone = trim($this->getRequest()->getPost('phone',''));
        $phone = Utils_FilterXss::filterXss($phone);

        $len = strlen($phone);
        if($len < 11 || $len > 11 || !preg_match("/1[3|4|5|7|8]{1}[0-9]{9}$/",$phone) ){
            $data['errorMsg'] = '手机号格式不正确';
            echo json_encode($data);
            exit;
        }
        $idlen = strlen($borrower_idcard);
        if($idlen != 18 ){
            $data['errorMsg'] = '身份证号输入有误';
            echo json_encode($data);
            exit;
        }


//        //upload
//        $elename = 'img';
//        if ($_FILES[$elename]) {
//            $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
//            $dir = 'fk/'.date('Ymd');
//            $upload = new Utils_Upload($dir, $config['root']);
//            $upload->file($_FILES[$elename]);
//            //仅允许上传gif,jpg,png三种类型图片
//            $upload->set_allowed_mime_types(array('image/gif', 'image/jpeg', 'image/png'));
//            $results = $upload->upload();
//            if (!$results['status']) {
//                $img = '';
//            } else {
//                $img = str_replace('\\', '/', $results['path']);
//
//            }
//        }

        $addData = array(
            'report_user_name'=> $user_name,
            'report_user_phone'=> $phone,
            'house_type'=> $house_type,
            'house_site'=> $house_site,
            'house_area'=> $house_area,
            'borrower_name'=> $borrower_name,
            'borrower_idcard'=> $borrower_idcard,
            'borrower_money'=>$borrower_money
        );

        $result = $this->_reportOrderModel->add($addData);
        if ($result) {
            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            echo json_encode($data);exit;
        } else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);exit;
        }


    }



}