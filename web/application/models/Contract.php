<?php
class ContractModel {

    private $_contractColumnDb;

    public function __construct() {
        $this->_contractColumnDb = new Contract_ColumnModel();
        $this->_contractColumnLocationDb = new Contract_ColumnLocationModel();
        $this->_appModel = new Cd_AppModel();
		$this->_user = new Cd_UserModel();
		$this->_audit = new Cd_AuditInfoModel();
		$this->_order = new Cd_ContractOrderModel();
		$this->_risk = new Cd_ContractRiskModel();
		$this->_account = new Cd_ContractAccountModel();
		$this->_gps = new Cd_ContractGpsModel();
		$this->_service = new Cd_ContractServiceModel();

    }

    public function add($addDatas) {
        if(empty($addDatas)){
            return false;
        }
        $addDatas['created'] = time();
        return $this->_contractColumnDb->data($addDatas)->add();
    }

    /**
     * 查询字段个数
     */
    public function getColumnCount($where){
        if($where){
            return $this->_contractColumnDb->where($where)->count();
        }else{
            return $this->_contractColumnDb->count();
        }
    }

    /**
     * 查询字段列表
     */
    public function getColumnList($start,$length,$where){
        $contract = $this->_contractColumnDb;
        $contract->limit($start, $length);
        if(!empty($where)){
            $contract->where($where);
        }

        $list = $this->_contractColumnDb->alias('fcc')
               ->join("LEFT JOIN xmcd_contract_column_location fccl on fcc.location_id=fccl.id")
               ->field("fcc.id, fcc.column_name, fcc.item_type, fccl.location, fccl.location_type")
               ->select();
        return $list;
    }

    /**
     * 获取字段信息
     */
    public function getInfo($id){
        return $this->_contractColumnDb->where(array('id'=>$id))->find();
    }

    /**
     * 删除字段
     */
    public function delectColumn($id){
        $res = $this->_contractColumnDb->where(array('id'=>$id))->delete();
        return $res;
    }

    public function mod($id, $data) {
        return $this->_contractColumnDb->where(array('id'=>$id))->save($data);
    }

    /**
     * 根据字段位置查询字段真实数值
     * @param $column_id 字段id
     * @param $app_id  订单id
     * @return string/int 返回查询出的结果
     */
    public function getColumnValue($column_id, $app_id){
        $column_info = $this->_contractColumnDb->getInfo($column_id);
// echo "<pre><meta charset='utf-8'>";var_dump($column_info);exit;
        if(!$column_info)
            return false;

        $location = explode('.', $column_info['location']);

        //判断为车贷还是房贷字段
        if($column_info['item_type'] == 1){
            //为车贷字段
            //判断location为字段位置
            if($column_info['location_type'] < 4){
            //location为字段位置
                $columnModel = new Cd_AppModel();

                if($column_info['location']){

                    if($column_info['relation_table']){//需要连表查询
                        $relation_table = explode(',', $column_info['relation_table']);
                        $relation_t_column = explode(',', $column_info['relation_t_column']);
                        $relation_j_column = explode(',', $column_info['relation_j_column']);

                        $sql_join = array();
                        foreach($relation_table as $k=>$v){
                            if($sql_join[$v]){
                                $sql_join[$v] .= " AND ".$relation_t_column[$k]."=".$relation_j_column[$k];
                            }else{
                                $sql_join[$v] = " LEFT JOIN ".$relation_table[$k]." ON ".$relation_t_column[$k]."=".$relation_j_column[$k];
                            }
                        }
                        $sql_join = implode(' ', $sql_join);

                        $sql_where = " where xmcd_cd_app.id=".$app_id." AND ".$column_info['location']." is not null ";
                        if($column_info['extend']){
                            if(!strstr($column_info['extend'], ',')){
                                $sql_where .= " AND ".$column_info['extend'];
                            }else{
                                $extend = str_replace(',', ' AND ', $column_info['extend']);
                                $sql_where .= " AND ".$extend;
                            }
                        }

                        $sql = "select ".$column_info['location']." from xmcd_cd_app ".$sql_join." ".$sql_where." limit 1";
                    }else{
                        $sql = "select ".$location[1]." from ".$location[0]." where id=".$app_id;
                    }
                    // echo $sql."<br>";
                    $result = $columnModel->query($sql);
                    $result = $result[0][$location[1]];
                }else{
                    $result = 0;
                }
                
            }else{
                $result = $this->computeValue($column_info['location'], $app_id);
            }
        }else{
        //为房贷字段
        }

        //判断是否有自定义函数
        if($column_info['custom_func']){
            if(strstr($column_info['custom_func'], ',')){
                $temp_arr = explode(',', $column_info['custom_func']);
                $custom_func = $temp_arr[0];
                $data['symbol'] = $temp_arr[1];
                foreach($temp_arr as $k=>$v){
                    if($k>1){
                        $data['param'][] = $v;
                    }
                }
                $data['location'] = $location;
                $data['result'] = $result;
                $data['app_id'] = $app_id;
                $result = $this->$custom_func($data);
            }else{
                $data['location'] = $location;
                $data['result'] = $result;
                $data['app_id'] = $app_id;
                $result = $this->$column_info['custom_func']($data);
            }
            
        }
// echo "<pre><meta charset='utf-8'>";var_dump($result);
        //自定义函数2
        if($column_info['custom_func_again']){
            if(strstr($column_info['custom_func_again'], ',')){
                $temp_arr_again = explode(',', $column_info['custom_func_again']);
                $custom_func_again = $temp_arr_again[0];
                $data_again['symbol'] = $temp_arr_again[1];
                foreach($temp_arr_again as $k=>$v){
                    if($k>1){
                        $data_again['param'][] = $v;
                    }
                }
                $data_again['location'] = $location;
                $data_again['result'] = $result;
                $data_again['app_id'] = $app_id;
                $result = $this->$custom_func_again($data_again);
            }else{
                $data_again['location'] = $location;
                $data_again['result'] = $result;
                $data_again['app_id'] = $app_id;
                $result = $this->$column_info['custom_func_again']($data_again);
            }
        }

        return $result;
    }

    /**
     * 根据公式计算字段值
     * @param string $relation 公式
     * @param int    $app_id   订单id
     * @param int    $result   公式计算出的结果
     */
    public function computeValue($relation, $app_id){
        $pattern_num = "/\d|\.|#/";
        $pattern_symbol = "/\+|\-|\*|\/|\(|\)/";
        $replacement = ',';

        $content_num = preg_replace($pattern_symbol, $replacement, $relation);
        $content_symbol = preg_replace($pattern_num, $replacement, $relation);
        $content_first = substr($content_num, 0, 1);

        $content_num_arr = array_values(explode($replacement, $content_num));
        $content_symbol_arr = array_values(array_filter(explode($replacement, $content_symbol)));

        foreach($content_num_arr as $k=>$v){
            if(strstr($v, '#')){
                $column_id = str_replace('#', '', $v);
                $result = $this->getColumnValue($column_id, $app_id);
                if($result == null)
                    $result = 0;
                $content_num_arr[$k] = $result;
            }else{
                $content_num_arr[$k] = $v;
            }
        }
        
        $new_arr = array();
        $size = count($content_num_arr)>count($content_symbol_arr) ? count($content_num_arr) : count($content_symbol_arr);
        if($content_first == ','){
            for($i=0; $i<$size; $i++){
                array_push($new_arr, $content_symbol_arr[$i]);
                array_push($new_arr, $content_num_arr[$i]);
            }
        }else{
            for($i=0; $i<$size; $i++){
                array_push($new_arr, $content_num_arr[$i]);
                array_push($new_arr, $content_symbol_arr[$i]);
            }
        }

        $symbol = implode('', $new_arr);
        // echo $symbol;
        eval("\$result=$symbol;");
        return $result;
    }

    /**
     * 合同模板路径
     * @param $path     模板路径
     * @param $app_id   订单id
     * @return $result  替换之后合同
     */
    public function getContractHtmlContent($path, $app_id){
        $content = file_get_contents($path);
        if(!$content){
            $error['error'] = 100;
            $error['errormsg'] = '获取模板['.$path.']失败';
            echo json_encode($error);
            exit;
        }
        if(!strstr($content, '##')){
            return true;
        }
        $result = $this->replaceColumnToValue($content, $app_id);
        return $result;
    }

    /**
     * 递归替换模板中特殊标记
     * @param $content  模板内容
     * @param $app_id   订单id
     * @return $new_content 替换之后合同
     */
    public function replaceColumnToValue($content, $app_id){
        static $new_content = '';
        preg_match('/##\d+##/',$content, $matches);
        if($matches){
            $column_id = str_replace('#', '', $matches[0]);
            $value = $this->getColumnValue($column_id, $app_id);
            $new_content = preg_replace("/##$column_id##/", $value, $content);
            if(strstr($new_content, '##')){
                $this->replaceColumnToValue($new_content, $app_id);
            }
        }
        return $new_content;
    }
	

/********************* 合同字段自定义函数 **********************/
    //合同字段自定义函数  判断商业险分期有效
    public function contract_checkinstallment($data){
        $contractRiskModel = new Cd_ContractRiskModel();
        $risk_stages = $contractRiskModel->field("installment")->where(array('aid'=>$data['app_id']))->find()['installment'];
        if($risk_stages==1){
            return number_format($data['result'], 2, '.', '');
        }else{
            return 0;
        }
    }
    //合同字段自定义函数  判断GPS分期有效
    public function contract_checkgps($data){
        $contractGpsModel = new Cd_ContractGpsModel();
        $gps_stages = $contractGpsModel->field("isgpsstages")->where(array('aid'=>$data['app_id']))->find()['isgpsstages'];
        if($gps_stages==1){
            return number_format($data['result'], 2, '.', '');
        }else{
            return 0;
        }
    }
    //合同字段自定义函数  判断商业险 不分期有效
    public function contract_checkinstallmentUnstage($data){
        $contractRiskModel = new Cd_ContractRiskModel();
        $risk_stages = $contractRiskModel->field("installment")->where(array('aid'=>$data['app_id']))->find()['installment'];
        if($risk_stages==0){
            return number_format($data['result'], 2, '.', '');
        }else{
            return 0;
        }
    }
    //合同字段自定义函数  判断GPS 不分期有效
    public function contract_checkgpsUnstage($data){
        $contractGpsModel = new Cd_ContractGpsModel();
        $gps_stages = $contractGpsModel->field("isgpsstages")->where(array('aid'=>$data['app_id']))->find()['isgpsstages'];
        if($gps_stages==0){
            return number_format($data['result'], 2, '.', '');
        }else{
            return 0;
        }
    }
    //合同字段自定义函数  时间格式转换
    public function contranct_timeStampToDate($data){
        return date('Y年m月d日', $data['result']);
    }
    //合同字段自定义函数  合同截至日期
    public function contract_signEndDate($data){
        $contractOrderModel = new Cd_ContractOrderModel();
        //获取合同签订日期
        $sign_start = $data['result'];
        
        //获取期限
        $deadline = $contractOrderModel->field("deadline")->where(array('aid'=>$data['app_id']))->find();
        $deadline = $deadline['deadline'];
        
        // $end_time = $sign_start + $deadline*30*24*60*60 - 24*60*60;
        $res = $this->_calRepayDates($deadline, $sign_start);
        
        return $res;
    }
    //合同字段自定义函数  用户信息转换
    public function contract_cd_getUserInfo($data){
        $info['value_id'] = $data['result'];
        $info['field_name'] = $data['location'][1];
        $userEntityModel = new Cd_UserEntityModel();
        $res = $userEntityModel->getUserInfo($info);
        return $res['value'];
    }
    //合同字段自定义函数  获取金额大写
    // public function cny($ns) { 
    //     $ns = $ns['result'];
    //     static $cnums=array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"), 
    //     $cnyunits=array("元","角","分"), 
    //     $grees=array("拾","佰","仟","万","拾","佰","仟","亿"); 
    //     list($ns1,$ns2)=explode(".",$ns,2); 
    //     $ns2=array_filter(array($ns2[1],$ns2[0])); 
    //     $ret=array_merge($ns2,array(implode("",$this->_cny_map_unit(str_split($ns1),$grees)),"")); 
    //     $ret=implode("",array_reverse($this->_cny_map_unit($ret,$cnyunits))); 
    //     return str_replace(array_keys($cnums),$cnums,$ret); 
    // }
    // //合同字段自定义函数  获取金额大写辅助函数
    // public function _cny_map_unit($list,$units) {
    //     $ul=count($units); 
    //     $xs=array(); 
    //     foreach (array_reverse($list) as $x) { 
    //         $l=count($xs); 
    //         if ($x!="0" || !($l%4)) $n=($x=='0'?'':$x).($units[($l-1)%$ul]); 
    //         else $n=is_numeric($xs[0][0])?$x:''; 
    //         array_unshift($xs,$n); 
    //     } 
    //     return $xs; 
    // }
    //合同字段自定义函数  贷款金额转成单位元
    public function contract_amountToBit($num){
        $num = $num['result'];
        // echo "<pre><meta charset='utf-8'>";var_dump($num);exit;
        return $num*10000;
    }
    //合同字段自定义函数  获取当前时间年
    public function contract_getYear(){
        return date("Y", time());
    }
    //合同字段自定义函数  获取当前时间月
    public function contract_getMonth(){
        return date("m", time());
    }
    //合同字段自定义函数  获取当前时间日
    public function contract_getDay(){
        return date("d", time());
    }
    //合同字段自定义函数  获取还款时间日
    public function contract_getRepaymentDay($date){
        $date = $date['result'];
        $data = (int)$date-24*60*60;
        return date("d", $data);
    }
    //合同字段自定义函数  车辆颜色转换
    public function contract_getCarColor($data){
        $data = $data['result'];
        switch ($data) {
            case 1:
                $color = '黑色';
                break;
            case 2:
                $color = '白色';
                break;
            case 3:
                $color = '银色';
                break;
            case 4:
                $color = '咖啡色';
                break;
            case 5:
                $color = '灰色';
                break;
            case 6:
                $color = '其他色';
                break;
            case 7:
                $color = '红色';
                break;
        }
        return $color;
    }
    //合同字段自定义函数   分期时计算
    public function contract_stages($data){
        $gps = array(110,139);
        $risk = array(109);
        foreach($data['param'] as $v){
            if(in_array($v, $gps)){
                $gps_arr[] = $v;
            }
            if(in_array($v, $risk)){
                $risk_arr[] = $v;
            }
        }
        if($gps_arr){
            foreach($gps_arr as $k=>$v){
                $res_gps[] = $this->getColumnValue($v, $data['app_id']);
            }
            $res_gps_result = implode($data['symbol'], $res_gps);
        }
        if($risk_arr){
            foreach($risk_arr as $k=>$v){
                $res_risk[] = $this->getColumnValue($v, $data['app_id']);
            }
            $res_risk_result = implode($data['symbol'], $res_risk);
        }
        $contractRiskModel = new Cd_ContractRiskModel();
        $contractGpsModel = new Cd_ContractGpsModel();
        $risk_stages = $contractRiskModel->field("installment")->where(array('aid'=>$data['app_id']))->find()['installment'];
        $gps_stages = $contractGpsModel->field("isgpsstages")->where(array('aid'=>$data['app_id']))->find()['isgpsstages'];
        $result = $data['result'];

        if($risk_stages==1 && $res_risk_result){
            $symbol = $result.$data['symbol'].$res_risk_result;
            eval("\$result=$symbol;");
        }
        if($gps_stages==1 && $res_gps_result){
            $symbol = $result.$data['symbol'].$res_gps_result;
            eval("\$result=$symbol;");
        }
        return $result;
    }
    //合同字段自定义函数   不分期时添加
    public function contract_unStages($data){
        $gps = array(110,139);
        $risk = array(109);
        foreach($data['param'] as $v){
            if(in_array($v, $gps)){
                $gps_arr[] = $v;
            }
            if(in_array($v, $risk)){
                $risk_arr[] = $v;
            }
        }
        if($gps_arr){
            foreach($gps_arr as $k=>$v){
                $res_gps[] = $this->getColumnValue($v, $data['app_id']);
            }
            $res_gps_result = implode($data['symbol'], $res_gps);
        }
        if($risk_arr){
            foreach($risk_arr as $k=>$v){
                $res_risk[] = $this->getColumnValue($v, $data['app_id']);
            }
            $res_risk_result = implode($data['symbol'], $res_risk);
        }
        $contractRiskModel = new Cd_ContractRiskModel();
        $contractGpsModel = new Cd_ContractGpsModel();
        $risk_stages = $contractRiskModel->field("installment")->where(array('aid'=>$data['app_id']))->find()['installment'];
        $gps_stages = $contractGpsModel->field("isgpsstages")->where(array('aid'=>$data['app_id']))->find()['isgpsstages'];
        $result = $data['result'];
        if($risk_stages==0 && $res_risk_result){
            $symbol = $result.$data['symbol'].$res_risk_result;
            eval("\$result=$symbol;");
        }
        if($gps_stages==0 && $res_gps_result){
            $symbol = $result.$data['symbol'].$res_gps_result;
            eval("\$result=$symbol;");
        }
        return $result;
    }
    //合同字段自定义函数   系数除以100
    public function contract_divideCeint($data){
        return number_format($data['result']/100, 2, '.', '');
    }
    //合同字段自定义函数   获取截止时间
    public function _calRepayDates($deadline, $sign_start) {
        
        //合同签订日期信息详情
        $date_info = getdate($sign_start);
      
        //减去天数,默认减去一天,即首期还款日期为合同签订日期下月当日的前一天
        $cut_day = 1;
        
        //判断是否闰年
        $time = mktime(20,20,20,2,1,$date_info['year']);
        if (date("t",$time)==29){ 
            $run_year = true;
        }else{
            $run_year = false;
        }
        
        //如果签订合同日是以下几个日期,需要特殊处理
        if($run_year == true && $date_info['mon'] == 1 && $date_info['mday'] == 31 ){
            $cut_day = 3;
        }
        else if($run_year == true && $date_info['mon'] == 1 && $date_info['mday'] == 30 ){
            $cut_day = 2;
        }
        else if($run_year == false && $date_info['mon'] == 1 && $date_info['mday'] == 31 ){
            $cut_day = 4;
        }
        else if($run_year == false && $date_info['mon'] == 1 && $date_info['mday'] == 30 ){
            $cut_day = 3;
        }
        
        //判断签订合同日是否是12月,并获取首期还款日期
        if($date_info['mon'] == 12){
            $first_pay_day = date('Y-m-d',strtotime(($date_info['year']+1) .'-'. '01' .'-'. ($date_info['mday'] - $cut_day)));
        }
        else{
            $first_pay_day = date('Y-m-d',strtotime($date_info['year'].'-'. ($date_info['mon'] + 1) .'-'. ($date_info['mday'] - $cut_day)));
        }
        
        //借款期限
        $deadline = $deadline;
        //首次还款日期时间戳
        $pay_timestamp = strtotime($first_pay_day);
        //首次还款日期信息详情
        $pay_date_info = getdate($pay_timestamp);
        
        //借款期限是否跨年,默认在一年内
        $year = 0;

        //如果首期还款月份 + 当前期数,大于12,证明已经跨年
        if($pay_date_info['mon'] + $deadline-1 > 12){
            //计算跨了几年
            $year = intval(($pay_date_info['mon'] + $deadline-1) / 12);
            //所跨年数对应的月数
            $span_month = 12 * $year;               
        }
        else{
            $span_month = 0;
        }

        //还款日期 (如果跨年了,年份要加上跨过的年数,月份要减去跨过的月数)
        $second_pay_day = date('Y-m-d',strtotime(($pay_date_info['year'] + $year).'-'. ($pay_date_info['mon'] + $deadline-1 - $span_month) .'-'.$pay_date_info['mday']));                      
        //还款日期所在月份
        $second_month = date("n",strtotime($second_pay_day));

        //计算还款日期 - 3天，是哪一天，用于处理一些特殊的日期
        $first_pay_day = date('Y-m-d',strtotime($second_pay_day ." -3 day"));
        //前三天所在月份
        $first_month = date("n",strtotime($first_pay_day));

        //如果还款日期的月份与前三天所在月份不一致,证明当前月没有还款日期,例如:6月没有31日,那就以当前月最后一天为还款日期
        //当 $pay_date_info['mday'] < 4 时,证明还款日期是在月初三天,那就不必进入下面的条件
        if(($second_month != $first_month) && ($pay_date_info['mday'] >= 4) ){
            $second_pay_day = date('Y-m-t',strtotime(($pay_date_info['year'] + $year).'-'.($pay_date_info['mon'] + $deadline-1 - $span_month)));                            
        }

        //最终还款日期
        $lastDates = date('Y年m月d日',strtotime($second_pay_day));;
        
        return $lastDates;
    }
    
    //合同字段自定义函数   金额保留两位小数
    public function contract_getDecimal($data){
        return number_format($data['result'], 2, '.', '');
    }
    //合同字段自定义函数   四舍五入取整数
    public function contract_getInt($data){
        return round($data['result']);
    }
    //合同字段自定义函数   金额转大写最新
    public function cny($num){
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num['result'], 2); 
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 15) {
                return "金额太大，请检查";
        } 
        $i = 0;
        $c = "";
        while (1) {
                if ($i == 0) {
                        //获取最后一位数字
                        $n = substr($num, strlen($num)-1, 1);
                } else {
                        $n = $num % 10;
                }
                //每次将最后一位数字转化为中文
                $p1 = substr($c1, 3 * $n, 3);
                $p2 = substr($c2, 3 * $i, 3);
                if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                        $c = $p1 . $p2 . $c;
                } else {
                        $c = $p1 . $c;
                }
                $i = $i + 1;
                //去掉数字最后一位了
                $num = $num / 10;
                $num = (int)$num;
                //结束循环
                if ($num == 0) {
                        break;
                } 
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
                //utf8一个汉字相当3个字符
                $m = substr($c, $j, 6);
                //处理数字中很多0的情况,每次循环去掉一个汉字“零”
                if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                        $left = substr($c, 0, $j);
                        $right = substr($c, $j + 3);
                        $c = $left . $right;
                        $j = $j-3;
                        $slen = $slen-3;
                } 
                $j = $j + 3;
        } 
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c)-3, 3) == '零') {
                $c = substr($c, 0, strlen($c)-3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
                return "零元整";
        }else{
                return $c . "整";
        }
    }
    //合同字段自定义函数   获取法律信息审核结果
    public function contract_getLegal($data){
        if($data['result'] == 1){
            return '正常';
        }else{
            return '不正常';
        }
    }
    //合同字段自定义函数   法律信息审核原因
    public function contract_getLegalReason($data){
        if($data['result']){
            return $data['result'];
        }else{
            return '';
        }
    }

/********************* 合同字段自定义函数结束 **********************/
	

	public function selectAppZk($start,$offset,$where,$type=0){
		//alias('a')->join()->select();
		//$this->_app->alias('a')->field(a.)->join()
		if($type==1){
			$apps = $this->_appModel->alias('a')->join("LEFT JOIN xmcd_cd_user b on a.customer=b.id")->where($where)->count();
		}else{
			$apps = $this->_appModel->alias('a')
                                ->field("a.id,a.no,a.amount,a.deadline,a.created,b.phone,b.name,a.rate")
                                ->join("LEFT JOIN xmcd_cd_user b on a.customer=b.id")
                                //->join("LEFT JOIN xmcd_cd_contract_order o on a.id=o.aid")
                                ->where($where)
                                ->order("a.created desc")->limit($start,$offset)->select();
		}
		// echo "<pre><meta charset='utf-8'>";var_dump($this->_appModel->getLastSql());exit;
		return $apps;
	}
	
	public function selectAuditZk($app_id){
		
		//取各个节点最后一条为1的
		$appa = $this->_audit->where(array("app_id"=>$app_id,"result"=>1))->order("created DESC")->buildsql();
		$apps = $this->_audit->table($appa.' a')->field("a.*,b.realname as name")->join("LEFT JOIN xmcd_users b on a.user_id=b.id")->group("flow")->select();
		return $apps;
	}
	
	public function selectContractOrder($app_id){
		return $this->_order->where(array("aid"=>$app_id,"carall"=>1))->find();
	}
	
	public function selectContractOrderUser($user_id){
		return $this->_order->where(array("user_id"=>$user_id,"carall"=>1))->order("created desc")->find();
	}
	
	
	public function selectContractRisk($app_id){
		return $this->_risk->where(array("aid"=>$app_id,"carall"=>1))->order("created desc")->find();
	}
	
	public function selectContractRiskList($user_id){
		return $this->_risk->field("status")->where(array("user_id"=>$user_id,"carall"=>1))->order("id desc")->find();
	}
	
	public function selectContractAccount($app_id){
		return $this->_account->where(array("aid"=>$app_id))->order("type asc")->select();
	}
	
	
	public function selectContractGps($app_id){
		return $this->_gps->where(array("aid"=>$app_id,"carall"=>1))->order("created desc")->find();
	}
	
	public function selectContractService($app_id){
		return $this->_service->where(array("aid"=>$app_id,"carall"=>1))->order("created desc")->find();
	}
	
	public function updateapp($app_id,$app,$column_make=0){
		if($column_make == 2){
			$appss = $this->_appModel->getAppInfo($app_id);
			if($appss[0]['column_make'] == 2){
				return $this->_appModel->where("id=$app_id")->save($app);
			}
		}else{
			return $this->_appModel->where("id=$app_id")->save($app);
		}
		
	}
	
	public function getHetong($id,$time,$sort){
		$org_info = $this->_appModel
					->alias('c')
                    ->join("LEFT JOIN xmcd_cd_contract_order fcco on c.id=fcco.aid")
					->join("LEFT JOIN xmcd_org o on c.oid=o.id")
					->field("o.first_letter, fcco.signcreated") 
					->where(array('c.id'=>$id))
					->find();

// echo "<pre><meta charset='utf-8'>";var_dump($time, $org_info['signcreated']);exit;
        if($time != $org_info['signcreated']){
            $sort = 10000+$sort;
            $sort = substr($sort,strlen($sort)-3,3);
    		
    		$no = $org_info['first_letter'].date('Ymd',$org_info['signcreated']).'11'.$sort;
            $res = true;
            $res = $this->_appModel->where(array('id'=>$id))->save(array('no'=>$no));
        }
        return $res;
	}
	
	public function getintoaccount($id){
		//$data = $this->_account->where(array("type"=>0))->select();
		$data1 = $this->_account->field('aid')->where(array("type"=>$id))->select();
		$a= "";
		foreach($data1 as $key=>$da1){
			if($key == 0){
				$a.=$da1["aid"];
			}else{
				$a.=','.$da1["aid"];
			}
		}
		if($a){
			$where['aid'] = array("not in",$a);
		}
		$where['type'] = 0;
		$data = $this->_account->where($where)->select();
		
		foreach($data as $key=>$da){
			$arr['aid'] = $da['aid'];
			$arr['user_id'] = $da['user_id'];
			$arr['type'] = $id;
			$arr['created'] = time();
			$this->_account->add($arr);
		}
		
	}

    public function getSort($oid, $start_time, $end_time, $aid){
        // $where['a.oid'] = $oid;
        // $where['o.created'] = array('between', $start_time, $end_time);
        $where = "a.oid=".$oid." and o.signcreated between ".$start_time." and ".$end_time." and o.aid<>".$aid;
        $sort = $this->_order->alias('o')
                ->join("LEFT JOIN xmcd_cd_app a on o.aid=a.id")
                ->field("o.sort")
                ->where($where)
                ->order("o.sort desc")
                ->limit(1)
                ->find();
// echo "<pre><meta charset='utf-8'>";var_dump($this->_order->getLastSql());exit;
        return $sort;
    }

}