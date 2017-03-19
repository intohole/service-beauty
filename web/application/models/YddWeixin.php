<?php
class YddWeixinModel {
	private $token = 'zNcCvqffTFOfFQcNXXsZVfCFCFofTf8S';
	
	public function __construct() {
		
	}
	
	public function handler() {
		$post = file_get_contents('php://input');
		if ($post) {
			$msg = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);
			$type = trim($msg->MsgType);
            $this->logger("handler_type:".$type);
            switch ($type) {
                case "event":
                    $result = $this->receiveEvent($msg);
                    break;
                case "text":
					$result = $this->transmitService($msg);
                    break;
                case "image":
                    break;
                case "location":
                    break;
                case "voice":
                    break;
                case "video":
                    break;
                case "link":
                    break;
                default:
                	$result = $this->transmitService($msg);
                    break;
			}
			echo $result;
		}
	}
	
	public function valid($echostr, $signature, $timestamp, $nonce) {
		$tmpArr = array($this->token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature) {
			echo $echostr;
		}
	}
	
	private function emptyresponse() {
		return 'success';
	}
	
	private function receiveEvent($msg) {
		$content = '';
		switch ($msg->Event) {
			case 'subscribe':
				$content = $this->transmitText($msg, "【想要贷款快，就找易安贷】\n\n平台为全国提供低息，安全、快速的贷款服务！同时秉承不接直户，专注服务于同行经纪人的理念！\n\n如您有任何疑问或建议，欢迎随时拨打客服电话400-667-8811或直接留言。");

				if (substr($msg->EventKey, 0, 8) == 'qrscene_') {
					$code = substr($msg->EventKey, 8);
					$m = new WxInviteBindModel();
					$m->setBind((string)$msg->FromUserName, $code);
				}
				break;
			case 'CLICK':
				switch ($msg->EventKey) {
					case 'BTN_VIEW_HOW_BJ':
						$content = $this->transmitNews($msg, array(
							array('Title'=>'业务介绍-北京房抵', 'Description'=>'', 'PicUrl'=>'https://mmbiz.qlogo.cn/mmbiz/nzhlVibVGiaaI2cPqcXeW6ibukiadgzezhvkHeMjicb7M0NDARA3oVwyJ4aSG9wl5yLUFapqUd2JtIp23EZvFWJ8kDA/0?wx_fmt=jpeg', 'Url' =>'http://fk.fangwudiya.com/report/introduc?action=fangdi'),
						));
						break;
					case 'BTN_VIEW_HOW_SH':
						$content = $this->transmitNews($msg, array(
							array('Title'=>'业务介绍-北京垫资', 'Description'=>'', 'PicUrl'=>'https://mmbiz.qlogo.cn/mmbiz/nzhlVibVGiaaI2cPqcXeW6ibukiadgzezhvkAzUOIWPbAl3puUPERYTLYpLtsSF3SMZImKAgSmcwQl0EzHk9wmZgcA/0?wx_fmt=jpeg', 'Url' =>'http://fk.fangwudiya.com/report/introduc?action=dianzi'),
						));
						break;
					case 'BTN_VIEW_HOW_AWARD':
						$content = $this->transmitText($msg, '【1月11日震撼上线】邀同行报单，每报一单奖您20元，上不封顶！敬请期待！');
						break;
					case 'BTN_VIEW_HOW_FIRST':
						$content = $this->transmitText($msg, '【开启赚钱新方法】首次报单即送10元！奖励将以现金形式发放。如有任何疑问，请随时致电客服400-667-8811');
						break;
					case 'GDZC':
						$content = $this->transmitText($msg, "哎呦！你终于点这里了！\n现在先稳住你激动的情绪，静静的在下面寻找与你相对应城市。\n然后猛戳城市名称，即可看到活动点位了噢~ \n\n活动城市：\n<a href = 'http://mp.weixin.qq.com/s/dn-2U3LAwxOrUuECoGjc8g'>【北京】</a>\n（其他城市敬请期待噢……）\n\n看完了点位就赶紧报单呀！");
						break;
					default:
						$content = $this->emptyresponse();
				}
				break;
			case 'unsubscribe':
                break;
            case 'LOCATION':
                break;
			default:
				$content = $this->emptyresponse();
                break;
		}
		echo $content;
	}
	
	//回复文本消息
	private function transmitText($object, $content) {
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
		return $result;
	}
	
	//回复图片消息
	private function transmitImage($object, $imageArray) {
		$itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";
	
		$item_str = sprintf($itemTpl, $imageArray['MediaId']);
	
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		$item_str
		</xml>";
	
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
		return $result;
	}
	
	//回复语音消息
	private function transmitVoice($object, $voiceArray) {
		$itemTpl = "<Voice>
    <MediaId><![CDATA[%s]]></MediaId>
</Voice>";
	
		$item_str = sprintf($itemTpl, $voiceArray['MediaId']);
	
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[voice]]></MsgType>
		$item_str
		</xml>";
	
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
		return $result;
	}
	
	//回复视频消息
	private function transmitVideo($object, $videoArray) {
		$itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";
	
		$item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);
	
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[video]]></MsgType>
		$item_str
		</xml>";
	
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
		return $result;
	}
	
	//回复图文消息
	private function transmitNews($object, $newsArray) {
		if(!is_array($newsArray)){
			return;
		}
		$itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
		$item_str = "";
		foreach ($newsArray as $item){
			$item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
		}
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[news]]></MsgType>
		<ArticleCount>%s</ArticleCount>
		<Articles>
		$item_str</Articles>
		</xml>";
	
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
		return $result;
	}
	
	//回复音乐消息
	private function transmitMusic($object, $musicArray) {
		$itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";
	
		$item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);
	
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[music]]></MsgType>
		$item_str
		</xml>";
	
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
		return $result;
	}
	
	//回复多客服消息
	private function transmitService($object) {
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
		return $result;
	}

    //接收位置消息并记录
    private function receiveLocation($object)
    {
        if(!$object) exit;
        $openid = (string)$object->FromUserName;
        $latitude = $object->Latitude;
        $longitude = $object->Longitude;
        //检查对应的微信用户是否已有地理位置信息，没有的话需要调用接口获取
        $wxInviteBindModel = new WxInviteBindModel();
        $this->logger("receiveLocation_openid: ".$openid);
        $info = $wxInviteBindModel->getInfo($openid);
        $this->logger("receiveLocation_info");
        if(empty($info)){
            $this->logger("receiveLocation_in_if");
            //调用逆地理位置api解析地理位置并在表中记录
            $location = $this->gencode($latitude,$longitude);
            $this->logger("receiveLocation_location: ".$location);
            if($location){
                $wxInviteBindModel->addData(array(
                    'openid'=>$openid,
                    'location'=>$location
                ));
            }
        }
        if(!empty($info) && !$info['location']){
            $location = $this->gencode($latitude,$longitude);
            if($location){
                $wxInviteBindModel->modData($info['openid'],array(
                    'location'=>$location
                ));
            }
        }
        exit;
    }

    /**
     * 百度逆地理编码api
     * @param $lat 维度坐标
     * @param $lng 经度坐标
     * @api exp:http://api.map.baidu.com/geocoder/v2/?ak=Gl2c1KGKYKHP4r64ZP1ayI16&callback=renderReverse&location=39.896866,116.484436&output=json&pois=0   //callback为空返回正常json
     * @return $city
     */
    private function gencode($lat,$lng){
        $city = '';
        $url="http://api.map.baidu.com/geocoder/v2/?ak=Gl2c1KGKYKHP4r64ZP1ayI16&callback=&location=".$lat.",".$lng."&output=json&pois=0";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {return 'ERROR '.curl_error($curl);}
        curl_close($curl);
        $data = json_decode($data,true);
        if($data['status'] == 0){
            $city = $data['result']['addressComponent']['city'];
            $city = str_replace('市','',$city);
            $this->logger("gencode_city:".$city);
        }
        return $city;
    }


    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 10000;
            $log_filename = "/tmp/wechat.log";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }

}