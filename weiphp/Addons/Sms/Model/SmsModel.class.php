<?php

namespace Addons\Sms\Model;
use Think\Model;

/**
 * Sms模型
 */
class SmsModel extends Model{
	var $config;
	//$from_type发送短信的用途 'card':会员卡手机认证
	function sendSms($to,$from_type){
		$this->config = getAddonConfig('Sms');
		if(strlen($to)!=11){
			$res['result'] = 0;
			$res['msg'] = "请检查手机号是否填写正确";
		}else{
			if($this->config ['type']==1){
				//云之讯
				$res = $this->_sendUcpassSms($to,$from_type);
			}else if($this->config ['type']==2){
				//云通讯
				$res = $this->_sendCloopenSms($to,$from_type);
			}else{
				$res = "配置参数出错";
			}
		}
		return $res;
	}
	function checkSms($phone,$code){
		$this->config = getAddonConfig('Sms');
		$map['phone'] = $phone;
		$sms = M ( 'sms' )->where ($map)->order ( 'id desc' )->find ();
		if($sms && $code == $sms['code']){
			$expire = (int)($this->config['expire']);
			if(NOW - $sms['cTime']>expire*60){
				$res['result'] = 0;
				$res['msg'] = "验证码已过期，请重新发送";
			}else{
				$res['result'] = 1;
				$res['msg'] = "验证码成功";
			}
		}else{
			$res['result'] = 0;
			$res['msg'] = "验证失败";
		}
		return $res;
	}
	//云之讯服务
	function _sendUcpassSms($to,$from_type){
		require_once VENDOR_PATH . 'Ucpaas.php';
		//初始化必填
		$options['accountsid']=$this->config['accountSid'];
		$options['token']=$this->config['authToken'];
		$ucpass = new \Ucpaas($options);
		//短信验证码（模板短信）,默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
		$appId = $this->config['appId'];
		if($from_type=='card'){
			$templateId = (int)($this->config['cardTemplateId']);
			$param[] = rand(1111,9999);
			$param[]= $this->config['expire'];
			$is_lock = smsLock($to);
			if($is_lock){
				$resStr = $ucpass->templateSMS($appId,$to,$templateId,implode(',',$param));
				$res = json_decode($resStr,true);
			}else{
				$result['result'] = 0;
				$result['msg'] = "获取验证码太频繁";
				return $result;
			}
		}else{
			//Todo
			
		}
		if($res['resp'] && $res['resp']['respCode']=="000000"){
			$data['phone'] = $to;
			$data['plat_type'] = $this->config['type'];
			$data['from_type'] = $from_type;
			$data['code'] = $param[0];
			$data['status'] = 0;
			$data['smsId'] = $res['resp']['respCode']['smsId'];
			$data['cTime'] = time();
			$this -> add($data);
			$result['result'] = 1;
			$result['msg'] = "发送成功";
		}else{
			$result['result'] = 0;
			$result['msg'] = "发送失败";
		}
		return $result;
	}
	
	//云通讯服务  此方法暂时没有测试过
	function _sendCloopenSms($to,$from_type){
		require_once VENDOR_PATH . 'CCPRestSmsSDK.php';
		// 初始化REST SDK
		$rest = new REST ( 'app.cloopen.com', '8883', '2013-12-26' );
		$rest->setAccount ( $this->config['accountSid'], $this->config['authToken'] );
		$rest->setAppId ( $this->config['appId'] );
		// 发送模板短信
		if($from_type=='card'){
			$templateId = (int)($this->config['cardTemplateId']);
			$param[] = rand(1111,9999);
			$param[]= $this->config['expire'];
			$is_lock = smsLock($to);
			if($is_lock){
				$res = $rest->sendTemplateSMS ( $to, $param, $templateId );
			}
		}else{
			//Todo
			
		}
		if($res['resp'] && $res['resp']['respCode']=="000000"){
			$data['phone'] = $to;
			$data['plat_type'] = $this->config['type'];
			$data['from_type'] = $from_type;
			$data['code'] = $param[0];
			$data['status'] = 0;
			$data['smsId'] = $res['resp']['respCode']['smsId'];
			$data['cTime'] = time();
			$this -> add($data);
			$result['result'] = 1;
			$result['msg'] = "发送成功";
		}else{
			$result['result'] = 0;
			$result['msg'] = "发送失败";
		}
		return $result;
	}
}
