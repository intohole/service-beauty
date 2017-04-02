<?php
/**
 * Created by jianghai.
 * php excel导出数据
 * Use:
 * Date: 15-4-7
 * Time: 下午12:33
 */

class Utils_ExportExcel {

    /**
     * Excel导出
     * $list为导出数据集
     * $param为导出参数列表,传参格式为：array('sms_id','phoneNum','sms_type','content','create_time')
     * $title为Excel表头,传参格式为： array('ID','手机号','短信类型','短信内容','发送时间')
     * $filename为导出的Excel文件名,形如"短信发送记录"
     */
    public static function export($list,$param=array(),$title=array(),$filename){
        $data = $list['rows'];
        $paramLength = count($param);
        foreach($data as $v) {
            for ($i = 0; $i < $paramLength; $i++) {
                $arr[$param[$i]] = $v[$param[$i]];

                //$param[$i] = $v[$param[$i]];
            }
            $paramArr[] = $arr;
        }
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=".$filename.".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        //导出xls 开始
        if (!empty($title)){
            foreach ($title as $k => $v) {
                $title[$k]=iconv("UTF-8", "GB2312",$v);
            }
            $title= implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($paramArr)){
            foreach($paramArr as $key=>$val){
                foreach ($val as $ck => $cv) {
                    $paramArr[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
                }
                $paramArr[$key]=implode("\t", $paramArr[$key]);

            }
            echo implode("\n",$paramArr);
        }
        exit;
    }


}