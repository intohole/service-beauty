һ����ӭʹ��weiphp��֧��ͨ���������������˵����

1����ϵͳ��̨��װ���
2���ڹ���ǰ̨����֧������Ĳ���
3��������ҳ����������֧�����ܣ��������Ƿ���ͨ����������ȷ�ϲ����Ƿ�������ȷ

������������˵��

Ŀǰ֧��ͨ������ͨ����תURL�ķ�ʽ��������������߹��ܡ������Ҫ�õ�֧������ʱ��ֻ��Ҫ����֧��ͨ�ṩ�Ľӿڵ�ַ��װ�ò�������ת�����ͣ�֧��������ת��ָ���Ļص���ַ�ϡ�

1���ӿڵ�ַ��ʽ��

addons_url ( 'Payment://Alipay/pay', array (
					'from' => ֧���ɹ���Ļص���ַ����ʽ��addons_url�ĸ�ʽ����Payment://Payment/playok������ Shop://Wap/afterPlay,
					'orderName' => ��������,
					'price' => ֧�����,
					'token' => ���ں�token,
					'wecha_id' => �û�openid,
					'paytype' => ֧�����ͣ���������Ͳ����������˵��,
					'orderid' => �������,
					'bid' => ��չID,
					'sid' => ��չID
			) )


2��֧�������У�

<select name="zftype">                          
	<option value="1">֧����</option>
	<option value="2">�Ƹ�ͨ(WAP�ֻ��ӿ�)</option>
	<option value="3">�Ƹ�ͨ(��ʱ����)</option>
	<option value="0">΢��֧��</option>
	<option value="4">��������</option>
</select>


3�����õ�PHP demo���£�


	/**
	 * ********************����֧��������������Է��������κ�һ��������߹�����***********************
	 */
	public function testpay() {
		if (IS_POST) {
			header ( "Content-type: text/html; charset=utf-8" );
			// token
			$token = get_token ();
			// ΢���û�ID
			$openid = get_openid ();
			// ��������
			$orderName = urlencode ( "���ǲ��ԵĶ���" );
			// ����ID
			$orderid = date ( 'Ymd' ) . substr ( implode ( NULL, array_map ( 'ord', str_split ( substr ( uniqid (), 7, 13 ), 1 ) ) ), 0, 8 );
			// ֧�����
			$price = $_POST ['zfje'];
			// ֧������
			$zftype = $_POST ['zftype'];
			/*
			 * �ɹ��󷵻ص��õķ��� addons_url�ĸ�ʽ
			 * ����GET����:token,wecha_id,orderid
			 * ������playok�ķ�����˵������ʵ�����ַҲ���ɿ��������ⶨ��
			 */
			$from = "Payment://Payment/playok";
			$bid = "";
			$sid = "";
			redirect ( addons_url ( 'Payment://Alipay/pay', array (
					'from' => $from,
					'orderName' => $orderName,
					'price' => $price,
					'token' => $token,
					'wecha_id' => $openid,
					'paytype' => $zftype,
					'orderid' => $orderid,
					'bid' => $bid,
					'sid' => $sid 
			) ), 1, '����,׼����ת��֧��ҳ��,�벻Ҫ�ظ�ˢ��ҳ��,�����ĵȴ�...' );
		} else {
			$normal_tips = '����֧������';
			$this->assign ( 'normal_tips', $normal_tips );
			$this->display ( "testpay" );
		}
	}
	public function playok() {
		// ֧���ɹ����ܵõ��Ĳ����У�
		$token = I ( 'token' );
		$openid = I ( 'wecha_id' );
		$orderid = I ( 'orderid' );
		
		// TODO �����￪���߿��Լ�֧���ɹ��Ĵ������
		
		$this->success ( '֧���ɹ���', U ( 'lists' ) );
	}