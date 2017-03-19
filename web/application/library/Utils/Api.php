<?php
/**
 * Created by jianghai.
 * API工具类
 * Date: 16-01-21
 * Time: 上午11:06
 */

class Utils_Api {


    /**
     * 百度逆地理编码api
     * @param $lat 维度坐标
     * @param $lng 经度坐标
     * @api exp:http://api.map.baidu.com/geocoder/v2/?ak=Gl2c1KGKYKHP4r64ZP1ayI16&callback=renderReverse&location=39.896866,116.484436&output=json&pois=0   //callback为空返回正常json
     * @return $city
     */
    public static function gencode($lat,$lng){
        $data = array();
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
        return $data;
    }


}