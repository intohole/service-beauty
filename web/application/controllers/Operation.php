<?php 
class OperationController extends Yaf_Controller_Abstract {
	private $_tpl;
    private $_operationModel;
	
	public function init() {
		$this->_tpl = $this->getView();
        $this->_operationModel = new OperationModel();
	}
	
	public function indexAction() {

	}

	public function getOperationListAjaxAction() {
        $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => ($_POST['length']),
            "recordsFiltered" =>'',
            "data" => array()
        );
		$operationCount = $this->_operationModel->getOperationCount();
        if($operationCount > 0 ){
            $operationList = $this->_operationModel->getOperationList($_POST['start'],$_POST['length']);
        }
        $output['recordsFiltered'] = $operationCount;
        $output['data'] = $operationList;
        echo json_encode( $output );exit;

	}
	
	public function addOperationAction() {
		
	}
	
	public function addOperationAjaxAction() {
        $data = array(
            'error' => 1,
            'errorMsg' => ''
        );
		$addDatas = $this->getRequest()->getPost();
        $addDatas = Utils_FilterXss::filterArray($addDatas);
		if (!$addDatas['name']) {
            $data['errorMsg'] = '日志名称为空';
            echo json_encode($data);
            exit;
		}
        //检查name是否有重复
        $info = $this->_operationModel->nameExist($addDatas['name']);
        if(!empty($info)){
            $data['errorMsg'] = '日志名称重复';
            echo json_encode($data);exit;
        }
		$result = $this->_operationModel->add($addDatas);
		if ($result) {
            $data['error'] = 0;
            $data['errorMsg'] = '添加成功';
            echo json_encode($data);exit;
		} else {
            $data['errorMsg'] = '添加失败';
            echo json_encode($data);exit;
		}

	}
	
	public function editOperationAction() {
		$id = (int)$this->getRequest()->get('id');
		if ($id <= 0) {
			die('日志ID获取失败');
		}
		
		$info = $this->_operationModel->get($id);
		if (!$info) {
			die('日志信息获取失败');
		}
		
		$this->_tpl->assign('info', $info);
	}
	
	public function editOperationAjaxAction() {
		$id = (int)$this->getRequest()->get('id');
		$data = $this->getRequest()->getPost();
        $data = Utils_FilterXss::filterArray($data);
        if ($id <= 0 || !$data['name']) {
            Utils_Output::errorResponse('参数缺失');exit;
        }
		if ($this->_operationModel->mod($id, $data)) {
            Utils_Output::errorResponse('OK',0);exit;
		} else {
            Utils_Output::errorResponse('未做修改或其它错误');exit;
		}

		return FALSE;
	}
	
	public function deleteOperationAjaxAction() {
		$id = (int)$this->getRequest()->getPost('id');
		if ($id <= 0) {
            Utils_Output::errorResponse('没有权限');exit;
		}
		if ($this->_operationModel->del($id)) {
            Utils_Output::errorResponse('OK',0);exit;
		} else {
            Utils_Output::errorResponse('删除失败');exit;
		}
		return FALSE;
	}
	


}