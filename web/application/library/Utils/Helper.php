<?php
/**
 * Created by jianghai.
 * 工具类
 * Date: 16-01-04
 * Time: 下午19:56
 */

class Utils_Helper {

    public static function arrayToString($data = array()){
        $str = '';
        if(empty($data)) return $str;
        foreach($data as $key=>$val){
            $st = "{$key}:{$val},";
            $str = $str.$st;
        }
        return substr($str, 0, -1);
    }


    public static function hidPhone($phone){
        $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i',$phone); //固定电话
        if($IsWhat == 1){
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i','$1****$2',$phone);
        }else{
            return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
        }
    }

}