<?php
class CarBrandModel {

    private $_carBrandDb;
    private $_carSeriesDb;
    private $_carStyleDb;
    private $_fileModel;

    public $letters = array (
          0 => 
          array (
            'letter' => 'A',
            'enable' => '1',
          ),
          1 => 
          array (
            'letter' => 'B',
            'enable' => '1',
          ),
          2 => 
          array (
            'letter' => 'C',
            'enable' => '1',
          ),
          3 => 
          array (
            'letter' => 'D',
            'enable' => '1',
          ),
          4 => 
          array (
            'letter' => 'E',
            'enable' => '0',
          ),
          5 => 
          array (
            'letter' => 'F',
            'enable' => '1',
          ),
          6 => 
          array (
            'letter' => 'G',
            'enable' => '1',
          ),
          7 => 
          array (
            'letter' => 'H',
            'enable' => '1',
          ),
          8 => 
          array (
            'letter' => 'I',
            'enable' => '0',
          ),
          9 => 
          array (
            'letter' => 'J',
            'enable' => '1',
          ),
          10 => 
          array (
            'letter' => 'K',
            'enable' => '1',
          ),
          11 => 
          array (
            'letter' => 'L',
            'enable' => '1',
          ),
          12 => 
          array (
            'letter' => 'M',
            'enable' => '1',
          ),
          13 => 
          array (
            'letter' => 'N',
            'enable' => '1',
          ),
          14 => 
          array (
            'letter' => 'O',
            'enable' => '1',
          ),
          15 => 
          array (
            'letter' => 'P',
            'enable' => '1',
          ),
          16 => 
          array (
            'letter' => 'Q',
            'enable' => '1',
          ),
          17 => 
          array (
            'letter' => 'R',
            'enable' => '1',
          ),
          18 => 
          array (
            'letter' => 'S',
            'enable' => '1',
          ),
          19 => 
          array (
            'letter' => 'T',
            'enable' => '1',
          ),
          20 => 
          array (
            'letter' => 'U',
            'enable' => '0',
          ),
          21 => 
          array (
            'letter' => 'V',
            'enable' => '0',
          ),
          22 => 
          array (
            'letter' => 'W',
            'enable' => '1',
          ),
          23 => 
          array (
            'letter' => 'X',
            'enable' => '1',
          ),
          24 => 
          array (
            'letter' => 'Y',
            'enable' => '1',
          ),
          25 => 
          array (
            'letter' => 'Z',
            'enable' => '1',
          ),
        );

    public $hotbrands = array (
        0 => 
        array (
          'id' => '1',
          'v' => '大众',
        ),
        1 => 
        array (
          'id' => '15',
          'v' => '宝马',
        ),
        2 => 
        array (
          'id' => '3',
          'v' => '丰田',
        ),
        3 => 
        array (
          'id' => '33',
          'v' => '奥迪',
        ),
        4 => 
        array (
          'id' => '36',
          'v' => '奔驰',
        ),
        5 => 
        array (
          'id' => '14',
          'v' => '本田',
        ),
        6 => 
        array (
          'id' => '8',
          'v' => '福特',
        ),
        7 => 
        array (
          'id' => '38',
          'v' => '别克',
        ),
        8 => 
        array (
          'id' => '63',
          'v' => '日产',
        ),
    );

    public function __construct() {
        $this->_carBrandDb = new Car_CarBrandModel();
        $this->_carSeriesDb = new Car_CarSeriesModel();
        $this->_carStyleDb = new Car_CarStyleModel();
        $this->_fileModel = new FilesModel();
    }

    public function getBrandList(){
        $list = $this->_carBrandDb
            ->field("brand_id, brand_name, bfirstletter, class_name")
            ->select();
        return $list;
    }

    /**
     * 根据首字母分类 获取品牌列表
     */
    public function getBrandGroupByFirst(){
        $list = $this->_carBrandDb
            ->field("brand_id, brand_name, bfirstletter")
            ->where(array('brand_id'=>array('neq', 235)))
            ->select();
        $firstList = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        foreach($list as $v){
            foreach($firstList as $f){
                if($v['bfirstletter'] == $f){
                    $brand_list[$f][] = array('id'=>$v['brand_id'], 'name'=>$v['brand_name']);
                }
            }
        }
        return $brand_list;
    }

    /**
     * 根据品牌获取车系列表
     */
    public function getSeriesListByBrand($brand_id){
        $where['brand_id'] = $brand_id;
        $where['pid'] = array('neq', 0);
        $list = $this->_carSeriesDb
            ->field("series_id as sid, series_name as sname")
            ->where($where)
            ->select();
// echo $this->_carSeriesDb->getLastSql();
        return $list;
    }

    /**
     * 根据品牌获取车系列表及价格区间
     */
    public function getSeriesGroupByFirst($brand_id){
        $where['xmcd_car_series.brand_id'] = $brand_id;
        $where['pid'] = array('neq', 0);
        $list = $this->_carSeriesDb
            ->join("LEFT JOIN xmcd_car_style fcst on xmcd_car_series.series_id=fcst.series_id")
            ->field("xmcd_car_series.brand_id, xmcd_car_series.series_id, xmcd_car_series.series_name, xmcd_car_series.pid, xmcd_car_series.firstletter, MIN(fcst.minprice) as minprice, MAX(fcst.maxprice) as maxprice")
            ->where($where)
            ->group("xmcd_car_series.series_id,xmcd_car_series.pid")
            ->select();
        return $list;
    }

    /**
     * 根据品牌车系获取款式列表
     */
    public function getStyleListBySeries($series_id){
        $where['series_id'] = $series_id;
        $where['pid'] = array('neq', 0);
        $list = $this->_carStyleDb
            ->field("style_id as id, style_name as name")
            ->where($where)
            ->select();
        return $list;
    }

    /**
     * 获取车辆品牌、车系、款式
     */
    public function getCarInfo($brand_id, $series_id, $style_id){
        $where['fcb.brand_id'] = $brand_id;
        $where['xmcd_car_series.series_id'] = $series_id;
        $where['fcst.style_id'] = $style_id;
        $list = $this->_carSeriesDb
            ->join("LEFT JOIN xmcd_car_style fcst on xmcd_car_series.series_id=fcst.series_id")
            ->join("LEFT JOIN xmcd_car_brand fcb on xmcd_car_series.brand_id=fcb.brand_id")
            ->field("brand_name, series_name, style_name")
            ->where($where)
            ->find();
        if($list) return $list;
        else return false;
    }

    public function add($addDatas) {
        if(empty($addDatas)){
            return false;
        }
        return $this->_carBrandDb->data($addDatas)->add();
    }

    public function add_series($addDatas){
        if(empty($addDatas)){
            return false;
        }
        return $this->_carSeriesDb->data($addDatas)->add();
    }

    public function add_style($addDatas){
        if(empty($addDatas)){
            return false;
        }
        return $this->_carStyleDb->data($addDatas)->add();
    }

    public function getletters(){
        $letters = $this->letters;
        return $letters;
    }

    public function gethotbrands(){
        $hotbrands = $this->hotbrands;
        return $hotbrands;
    }

}