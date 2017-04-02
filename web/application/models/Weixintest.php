<?php

/**
 * 微信测试模型
 */
class WeixintestModel extends TK_M  {
    protected $tableName = 'fk_wxcode';
    
    private $_appID = 'wxa8d3f860d3777605';
    private $_appsecret = '2cb6d4aeacd9303a1ffe5e78ed4ac83a';

    public function __construct() {
        parent::__construct();
    }

    //响应微信事件
    public function responseMsg() {
        //获取微信消息与事件
        $post = file_get_contents('php://input');
        
        if ($post) {
            //将XML转为对象
            $msg = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            //微信消息类型
            $type = trim($msg->MsgType);
            
            switch ($type) {
                case "event": //事件
                    $result = $this->receiveEvent($msg);
                    break;
                
                case "text":  //普通消息
                    file_put_contents("/home/ybk_hanguangyu/glog.txt", $msg->Content, FILE_APPEND);
   
                    if(is_numeric((int)$msg->Content)){
                        $result = $this->inviteAward($msg);
                    }
                    else{
                        $result = $this->transmitText($msg,"您输入的邀请码有误,请重新输入");
                    }
                    break;
                
                case "image": //回复图片消息
                    break;
                
                case "voice": //回复语音消息
                    break;
                
                case "video": //回复视频消息
                    break;
                
                default:
                    $result = $this->transmitService($msg);
                    break;
            }
            echo $result;
        }
    }


    //处理微信事件
    private function receiveEvent($msg) {
        $content = '';
        switch ($msg->Event) {
            case 'subscribe': //关注事件
                $content = "感谢关注!";
                break;
            
            case 'CLICK': //点击事件
                switch ($msg->EventKey) {
                    case 'TEST_EVENT':
                        //根据openId群发消息
                        $this->massByOpenId();
                        //给用户回复
                        $content = $this->transmitText($msg,'群发成功');
                        break;
                    
                    default:
                        $content = $this->emptyresponse();
                }
                break;
            
            case 'unsubscribe':  //取消关注事件
                break;
            
            case 'LOCATION':  //上报地理位置事件
                break;
            
            default:
                $content = $this->emptyresponse();
                break;
        }
        
        return $content;
    }
    
    
    
    //群发信息
    private function massByOpenId() {
        //获取access_token
        $access_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->_appID."&secret=".$this->_appsecret;
        $access_json = file_get_contents($access_url);
        $access_arr = json_decode($access_json, true);
        
        //公众号群发接口
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$access_arr['access_token'];
        
        //群发数据
        $jsonData = '{
                        "touser":[
                         "o0C4Es247iYlxjAEDI6JHI8nuIwk",
                         "o0C4EsyMzPk6GbdCyg2AKjZTjJyM"
                        ],
                         "msgtype": "text",
                         "text": { "content": "您有一笔新的订单,请及时审核"}
                     }';
        $jsonData = urldecode($jsonData);
        
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POST, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        $data = curl_exec($ch); 
        curl_close($ch);
        
        file_put_contents("/home/ybk_hanguangyu/glog.txt", $data, FILE_APPEND);
    }
    
	
    //邀请奖励
    public function inviteAward($object) {
        //根据邀请码查询用户
        $res = $this->where(array('code'=>$object->Content))->find();
        
        if(!$res){
            $content = "抱歉,您的邀请码不存在请重新输入";
        }
        else{
            if($res['status'] != 0){
                $content = "您已领取过邀请奖励";
            }
            else{
                $data['status'] = 1;
                $data['money'] = 100;
                $this->where(array('code'=>$object->Content))->save($data);
                $content = "您的邀请人.".$res['name']."账户已增加100元奖金";
            }
        }
        
        //给用户回复消息
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
    
    private function emptyresponse() {
        return 'success';
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