<?php

class Util_Tool {
    public static function getRealIP() {
        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $real_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			//$real_ip = preg_replace('/[^0-9a-fA-F:.]/', '',$real_ip);
			if(strpos($real_ip, ",")){
				$ips = explode(',', $real_ip);
				$real_ip = $ips[0];
			}
        } else {
            $real_ip=$_SERVER["REMOTE_ADDR"];
        }
        return $real_ip;
    }

	public static function genRandomString2($length = 10 ) {
		$randpwd = '';  
		for ($i = 0; $i < $length; $i++)  
		{  
			$randpwd .= chr(mt_rand(33, 126));  
		}  
		return $randpwd;  
	}


	public static function genRandomString($length = 10 ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';  
		$password = '';  
		for ( $i = 0; $i < $length; $i++ )  
		{  
			$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}  
		return $password;  
	}


    /**
     * getAddressByIp
     * 通过IP获得用户物理地址
     * @param array $ip
     * @access public
     * @return array
     */
    public static function getAddressByIp($ip){
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_ENCODING ,'utf8');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        $location = curl_exec($ch);
        $location = json_decode($location);
        curl_close($ch);
        $data = $location->data;
        $loc = "";
        if($location===FALSE) return "";
        if ($location->code == '0') {
            if($data->ip != '127.0.0.1'){
                $loc = $data->region.$data->city.$data->county.$data->isp;
            }else{
                $loc = '内网IP';
            }

        }else{
            $loc = '';
        }
        return $loc;
    }

    /*
    返回数据格式：
    {"code":0,"data":{"ip":"210.75.225.254","country":"\u4e2d\u56fd","area":"\u534e\u5317",
    "region":"\u5317\u4eac\u5e02","city":"\u5317\u4eac\u5e02","county":"","isp":"\u7535\u4fe1",
    "country_id":"86","area_id":"100000","region_id":"110000","city_id":"110000",
    "county_id":"-1","isp_id":"100017"}}
    其中code的值的含义为，0：成功，1：失败。

    返回参数详解：
    code 状态码，正常为0，异常的时候为非0
    data 查询到的结果
    country 国家
    country_id 国家代码
    area 地区名称（华南、华北...）
    area_id 地区编号
    region 省名称
    region_id 省编号
    city 市名称
    city_id 市编号
    county 县名称
    county_id 县编号
    isp ISP服务商名称（电信/联通/铁通/移动...）
    isp_id ISP服务商编号
    ip 查询的IP地址
     */





}
