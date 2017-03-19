<?php
/**
 * 微信测试
 */
class WxtestController extends Yaf_Controller_Abstract {
    private $_req;
    private $_token = 'hanguangyu';
    private $_appID = 'wxa8d3f860d3777605';
    private $_appsecret = '2cb6d4aeacd9303a1ffe5e78ed4ac83a';
    private $curl;
    private $curl2;
	private $test33;
	private $test55;
        

    public function init() {
        $this->_req = $this->getRequest();
    }
    
    //获取基础access_token
    public function getAccess_token() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->_appID."&secret=".$this->_appsecret;
        $arr = file_get_contents($url);
        $arr = json_decode($arr, true);
        return $arr['access_token'];
        
        
//        $this->cache = new Utils_Redis();
//        
//        $tokenKey = 'WEIXINTICKETKEY:token_'.$this->_appID;
//        
//        $token = $this->cache->get($tokenKey);
//        
//        if(!$token){
//            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->_appID."&secret=".$this->_appsecret;
//            $arr = file_get_contents($url);
//            $arr = json_decode($arr, true);
//            $this->cache->set($tokenKey, $arr['access_token'], 3600);
//            return $arr['access_token'];
//        }
//        
//        return $token;
    }

    
    
    //发送模板消息
    public function sendmsgAction() {
        $access_token = $this->getAccess_token();
        
        //$qzurl = 'http://'.$_SERVER['HTTP_HOST'].'/m/view/aid/222/role_id/8';
        $qzurl = 'http://www.baidu.com';
        
        $qzdata = array();
        $qzdata['first'] = array('value' => "您有新的权证报单需要分配",'color' => "#333333");
        $qzdata['keyword1'] = array('value'=> "分配权证",'color'=>"#333333");
        $qzdata['keyword2'] = array('value' => "2017-02-24" . "\n".'借款人：韩一' ."\n". '借款金额：500'."万元"."\n".'订单类型：车贷',
                'color' => "#333333");
        $qzdata['remark'] = array('value' => "点击详情进行分配",'color' => "#333333");
        
        $template = array
            (
                'touser'=>'o0C4Es247iYlxjAEDI6JHI8nuIwk',
                'template_id' => 'Rm4b_YJhB3S0T9vgFSqr1sPmEsIAboTlSzSAd8uAxuo',
                //'url' => $qzurl,
                'topcolor' => '#7B68EE',
                'data' => $qzdata
            );
        
        $json_template = json_encode($template);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
        
        $postUrl = $url;
        $curlPost = urldecode($json_template);
        
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $res = curl_exec($ch); //运行curl
        curl_close($ch);
        
        if($res['errcode'] == 0){
                //return true;
                echo 'ssssss';
        }else{
                //return false;
                echo 'fffff';
        }
        exit;
           
    }
    
    
    
    //接受接口
    public function ReceiveAction() {
        //file_put_contents("D:\\image\glog.txt", 'ggggggggg', FILE_APPEND);
        
        $xmlInput1 = $_POST['cd_params'];
        
        $xmlInput2 = file_get_contents('php://input');
        
        $log1 = 'post1: '.$xmlInput1;
        file_put_contents("D:\\image\glog.txt", $log1, FILE_APPEND);
        //file_put_contents("/home/ybk_hanguangyu/glog.txt", $log1, FILE_APPEND);
        
        $log2 = 'post2: '.$xmlInput2;
        //file_put_contents("D:\\image\glog2.txt", $log2, FILE_APPEND);
        file_put_contents("/home/ybk_hanguangyu/glog2.txt", $log2, FILE_APPEND);
        
        //print_r($xmlInput1);
        echo 'kkkkkk';
        exit;
        
    }
    
    
    //接入验证
    public function TestverifyAction() {
        
        $wxModel = new WeixintestModel();
        
        //随机字符串
        $echostr = $this->_req->get('echostr');
        
        //如果随机字符串不为空证明是公众号的接入验证请求
        if ($echostr) {
            //时间戳
            $timestamp = $this->_req->get('timestamp');
            //随机数
            $nonce = $this->_req->get('nonce');
            //微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
            $signature = $this->_req->get('signature');
            
            $tmpArr = array($this->_token, $timestamp, $nonce);
            
            //排序
            sort($tmpArr,SORT_STRING);
            $tmpStr = implode($tmpArr);
            
            //加密
            $tmpStr = sha1($tmpStr);
            
            //如果签名正确原样返回微信发来的随机字符串
            if ($tmpStr == $signature) {
                echo $echostr;
            }
        
        //公众号事件
        } else {
            $wxModel->responseMsg();
        }
        
        return FALSE;
    }
    
    //获取code
    public function indexAction() {
        //请求code的url
        $code_url = "https://open.weixin.qq.com/connect/oauth2/authorize";
                
        //回调url,请求code后会跳转到此地址
        $redirect_uri = "http://fk.fangwudiya.com/wxtest/getAccess";
        
        //请求code需要的参数
        $params = array(
                   'appid' => $this->_appID,
                   'redirect_uri' => $redirect_uri,
                   'response_type' => 'code',
                   //'scope' => "snsapi_base",
                   'scope' => "snsapi_userinfo",
                   'state' => "STATE",
                  );
        
        $url = $code_url."?". http_build_query($params).'#wechat_redirect';
        
        header('Location:'.$url);
        
    }
    
    //获取access-token,openID,及拉取用户信息
    public function getAccessAction() {
        //微信借口返回的code
        $code = $this->_req->get('code');
        
        //请求acess_token的接口
        $access_url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        
        //请求acess_token的参数
        $params = array(
            'appid' => $this->_appID,
            'secret' => $this->_appsecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        );
        
        $url = $access_url."?".http_build_query($params);
        $params = array();
        
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
        
        //微信接口返回的数据
        $response = curl_exec($this->curl);

        $curlInfo = curl_getinfo($this->curl);

        //截取出公众号返回的相关信息
        $headerSize = $curlInfo['header_size'];
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $results = array(
                    'curl_info' => $curlInfo,
                    'content_type' => $curlInfo['content_type'],
                    'status' => $curlInfo['http_code'],
                    'headers' => $this->splitHeaders($header),
                    'data' => $body,
                   );
        
        $json_data = json_decode($results['data'], TRUE);
        
        
        //如果网页授权作用域为snsapi_userinfo,获取微信用户相信信息
        if($json_data['scope'] == "snsapi_userinfo"){
            //获取用户详细信息的接口
            $userinfo_url = 'https://api.weixin.qq.com/sns/userinfo';
            
            //获取用户详细信息所需参数
            $queries = array(
                'access_token' => $json_data['access_token'],
                'openid' => $json_data['openid'],
                'lang' => 'zh_CN',
            );
            
            $userinfo_url = $userinfo_url.'?'.http_build_query($queries);
            
            $this->curl2 = curl_init();
            curl_setopt($this->curl2, CURLOPT_HEADER, 1);
            curl_setopt($this->curl2, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curl2, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($this->curl2, CURLOPT_URL, $userinfo_url);
            curl_setopt($this->curl2, CURLOPT_POSTFIELDS, $params);

            //微信接口返回的数据
            $response2 = curl_exec($this->curl2);
            
            $curlInfo2 = curl_getinfo($this->curl2);

            $headerSize2 = $curlInfo2['header_size'];
            $header2 = substr($response2, 0, $headerSize2);
            $body2 = substr($response2, $headerSize2);

            $results2 = array(
                        'curl_info' => $curlInfo2,
                        'content_type' => $curlInfo2['content_type'],
                        'status' => $curlInfo2['http_code'],
                        'headers' => $this->splitHeaders($header2),
                        'data' => $body2,
                       );

            $json_data2 = json_decode($results2['data'], TRUE);
             //转码
//            foreach($json_data2 as $key => $val){
//                $json_data2[$key] = iconv("utf-8","gbk",$val);
//            }
            
            print_r($json_data2); exit();
            
        }
    }
    
    
    
    
    public function splitHeaders($rawHeaders)
    {
        $headers = array();

        $lines = explode("\n", trim($rawHeaders));
        $headers['HTTP'] = array_shift($lines);

        foreach ($lines as $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                $headers[$h[0]] = trim($h[1]);
            }
        }

        return $headers;
    }
    
    
}



//$log = 'wuwuwuwwuuw';
//file_put_contents("/home/ybk_hanguangyu/glog.txt", $log, FILE_APPEND);
            
//            $log = 'echostr: '.$echostr . "\n";
//            $log .= 'timestamp: '.$timestamp . "\n";
//            $log .= 'nonce: '.$nonce . "\n";
//            $log .= 'signature: '.$signature . "\n";
//            $log .= 'tmpStr: '.$tmpStr . "\n";
//            file_put_contents("/home/ybk_hanguangyu/glog.txt", $log, FILE_APPEND);

            //转码
//            foreach($json_data2 as $key => $val){
//                $json_data2[$key] = iconv("utf-8","gbk",$val);
//            }