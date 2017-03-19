<?php
/**
 * Utils_Output{   
 * 
 * 统一输出 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author wengxuejie 
 * @date 2014-09-11 }
 */
class Utils_Output{

    public static function jsonResponse( $code = 0, $data, $msg = ''){
        $res = new stdClass();
        $res->error = $code;
        $res->msg = $msg;
        $res->res = $data;
        echo json_encode($res);
        exit;
    }

    public static function zxjsonResponse( $code = 0,$msg = '', $data ){
        $res = new stdClass();
        $res->error = $code;
        $res->msg = $msg;
        $res->res = $data;
        echo json_encode($res);
        exit;
    }

	public static function echoJson($code =0, $data, $msg=''){
		$res = new stdClass();
		$res->error = $code;
		$res->msg = $msg;
		$res->res = $data;
		echo json_encode($res);
	}

	public static function html($code, $msg, $url = "http://xmcd.yianjinrong.com", $time=0){
		$face = $code == 0 ?":)":":(";	
		if($time){
			die("<html><script>var quit =function(){window.location.href = \"$url\";};setTimeout(\"quit()\",$time);</script><body><h7>error:$code  errorMsg :$msg $face</h7><br/> <h7>by yinuo!</h7></body></html>");
		}
		header("Location: $url");
		exit;
	}

	public static function ajaxJsonReturn($data, $json_option=0) {
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode($data, $json_option);
	}
	
	/**
	 * ajax响应返回 格式为json
	 * @param array $data
	 */
	public static function ajaxReturn($data) {
		header('Content-type: application/json');
		echo json_encode($data);
	}

    public static function errorResponse($errmsg = '',$error = 1){
        $res = new stdClass();
        $res->error = $error;
        $res->errmsg = $errmsg;
        echo json_encode($res);
        exit;
    }

    public static function redirect($url){
        if(!$url) return false;
        header('Location: http://'.Yaf_Application::app()->getConfig()->website->host.$url);
    }
    
    /**
    * 错误提示页
    * @author hgy
    * @since 2016-05-30
    */
    public static function errorMsg($msg='',$pagename='',$redirect=''){
        //过滤
        $msg = htmlspecialchars($msg);
        $pagename = htmlspecialchars($pagename);
        $redirect = htmlspecialchars($redirect);
        
        //编码
        $msg = base64_encode($msg);
        $pagename = base64_encode($pagename);
        $redirect = base64_encode($redirect);
        
        $url = "/error?msg=".$msg."&pagename=".$pagename."&redirect=".$redirect;
        
        self::redirect($url);
        exit;
    }

    /**
    * 错误提示页(M端)
    * @author hgy
    * @since 2016-05-30
    */
    public static function merrorMsg($msg='',$pagename='',$redirect=''){
        //过滤
        $msg = htmlspecialchars($msg);
        $pagename = htmlspecialchars($pagename);
        $redirect = htmlspecialchars($redirect);
        
        //编码
        $msg = base64_encode($msg);
        $pagename = base64_encode($pagename);
        $redirect = base64_encode($redirect);
        
        $url = "/error/merror?msg=".$msg."&pagename=".$pagename."&redirect=".$redirect;
        
        self::redirect($url);
        exit;
    }

}
