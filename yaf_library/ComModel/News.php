<?php
/**
 * 新闻Model
 * @author liangxiao
 * @date 2013-12-04
 */
class ComModel_News {
    private $_dbNewsModel;


    function __construct() {
        $this->_dbNewsModel = new ComModel_Db_News();
    }
    
    /**
     * getNewsInfo
     * 获取新闻信息
     * @access public
     * @return array
     */
    public function getNewsInfo($newsId) {
        $field = array(
                'news_id',
                'cat_id',
                'title',
                'content',
                'update_at',
                'game_id',
                'name'
            );
        $where = 'news_id='.$newsId;
        
        $selectField = implode(', ', $field);
        $sql = "SELECT ";
        $sql .= $selectField;
        $sql .= " FROM news";
        $sql .= " LEFT JOIN `news_category` ON `news_category`.id=news.cat_id";
        $sql .= " WHERE ". $where;
        $sql .= " LIMIT 1";
        
        $db = $this->_dbNewsModel->getDbObject();
        $query = $this->_dbNewsModel->getAdapter()->query($sql);
        $data = $query->fetch();
        
        return $data;
    }

    /**
     * updateNews
     * 更新新闻
     * @param array $data
     * @access public
     * @return int
     */
    public function saveData($data) {
        if(empty($data)) {
            return false;
        }
        /*
        foreach($data as $k => $v) {
            $data[$k] = mysql_escape_string(trim($v));
        }*/
        if(empty($data['title']) || empty($data['game_id']) || empty($data['cat_id'])) {
            return false;
        }
        if(array_key_exists('news_id', $data) && $data['news_id']) {
            $this->_dbNewsModel = $this->_dbNewsModel->find($data['news_id']);
        }
        foreach($data as $key => $value) {
            $this->_dbNewsModel->$key = $value;
        }
        return $this->_dbNewsModel->save();
    }

    /**
     * ajax异步获取新闻数据
     * @param int $page
     * @param int $pageSize
     * @param array $where sql条件数组
     * @return array 结果集
     */
    public function getNewsListByPage($page = 1, $pageSize = 10, $where = array()) {
        
        //默认空数据
        $result = array(
            'total' => 0,
            'rows' => array()
        );
        $start = ($page - 1) * $pageSize;
        $where = $this->_getWhere($where);
        //--------------获取总数
        $db = $this->_dbNewsModel->getAdapter();
        $select = $db->select();
        $select->from('news','count(news_id) as count');
        $total = $db->fetchRow($select);
        $total = $total['count'];
        
        if(!$total) {
            return $result;
        }
        $result['total'] = $total;
        
        $data = $this->_dbNewsModel->fetchAll($where, 'news_id DESC ', $pageSize, $start);
        
        $categoryModel = new CategoryModel();
        $categoryInfo = array();
        $category_arr = array();
        $categoryInfo = $categoryModel->getCategoryItem();
        foreach($categoryInfo->toArray() as $k => $v) {
            $category_arr[$v['id']] = $v['name'];
        }
        $gameModel = new ComModel_Game();
        $gameInfo = array();
        $game_arr = array();
        $field = array('game_id','game_name');
        $gameInfo = $gameModel->getGameItem($field,array('1=1'),0);
        foreach($gameInfo as $k => $v) {
            $game_arr[$v['game_id']] = $v['game_name'];
        }
        foreach($data->toArray() as $value) {
            $value['update_at'] = date('Y-m-d H:i:s', $value['update_at']);
            $value['game_name'] = @$game_arr[$value['game_id']];//游戏
            $value['cat_name'] = @$category_arr[$value['cat_id']];//新闻分类
            $result['rows'][] = $value;
        }
        return $result;
    }

    /**
     * 解析where,得到可以放到sql中的条件语句
     * @param unknown $where
     * @return string
     */
    private function _getWhere($where = array()) {
        $returnWhereArray = array();
        if(!$where) {
            return '1=1';
        }
        if(array_key_exists('cat_id', $where)) {
            $returnWhereArray[] = " `cat_id` =". $where['cat_id'];
        }
        if(array_key_exists('game_id', $where)) {
            $returnWhereArray[] = " `game_id` =". $where['game_id'];
        }
        $returnWhere = implode(' AND ', $returnWhereArray);
        
        return $returnWhere;
    }
    
    /**
     * deleteNews 
     * 删除新闻
     * @param int $news_id 
     * @access public
     * @return boolean
     */
    public function deleteNews($news_id) {
        if(empty($news_id)) {
            return false;   
        }
        $where = $this->_dbNewsModel->getAdapter()->quoteInto('news_id = ?', $news_id);
        $delOk = $this->_dbNewsModel->delete($where);
        return $delOk;
    }
	
	    /**
     * 读取新闻列表(不分页)
     * @param int $gameId
     * @param string $newsType
     * @param int $limit 
     */
    public function getNewsList($gameId,$newsType='',$limit){
        $catSql = "SELECT `name`,`id` FROM `news_category`";//先读新闻分类
        $typeList = array();
        $typeList = $this->db->fetchAll($catSql);
        $typeIndex = array();
        $typeArray = array();
        foreach($typeList as $v){
            $typeIndex[$v['id']] = $v['name'];
        }
        $where = ' WHERE 1=1';
        if($newsType){
            $where .= " AND `cat_id` IN (".$newsType.")";
        }
        if($gameId){
            $where .= " AND `game_id` = $gameId";
        }
        $sql = 'SELECT `news_id`,`cat_id`,`title`,`update_at`,`game_id`';
        $sql .= ' FROM `news` '.$where.' ORDER BY `update_at` DESC LIMIT 0,'.$limit;  
        $list = array();
        $listResult = array();
        $list = $this->db->fetchAll($sql);
        foreach($list as $k=> $v){
            //$link = $url . @$v['news_id'] . '.html';
            $v['sub_name'] = $typeIndex[$v['cat_id']];
            $v['timestamp'] = $v['update_at']; 
            //$v['link'] = $link;//新闻路径
            $list[$k] = $v;
        }
        return json_decode(json_encode($list));;
    }

    /**
     * 新闻列表展示
     *
     */
    public function getList($game_id, $news_type, $page, $limit) {
        $page = $page ? $page : 1;
        $limit = $limit ? $limit : 12;
        $where = "";
        if ($game_id) {
            $where[] = " `game_id`=" . $game_id;
        }
        if ($news_type) {
            $type_arr = explode(',', $news_type);
            $idb_sql = '(';
            foreach ($type_arr as $key => $value) {
                if ($key == 0) {
                    $idb_sql .= " `cat_id`=" . $value;
                } else {
                    $idb_sql .= " or `cat_id`=" . $value;
                }
            }
            $idb_sql .= ')';
            $where[] = $idb_sql;
        }
        if($where)
            $where = implode(" AND ", $where);

        $order = " update_at DESC ";
        $res = $this->getItems($where, $order, $page, $limit);
        $items = @$res['items'];
        $tmp_items = array();   
        if ($items) {
            foreach ($items as $key => $item) {
                $timestamp = @$item['update_at'];
                //$link = $url . @$item['news_id'] . '.html';
                $tmp_items[$key] = array(
                    'title' => @$item['title'],
                    'sub_name' => @$item['sub_name'],
                    //'link' => $link,
                    'timestamp' => $timestamp,
                    'news_id' => $item['news_id'],
                );
            }
            $res['items'] = $tmp_items;
        }
        //递归将数组转换为对象
        return json_decode(json_encode($res));
    }

    public function getItems($where, $order, $page, $size=1) {
	$db = $this->_dbNewsModel->getDbObject();
	$select = $db->select();
	$select->from('news_category');
	$typeList = $db->fetchAll($select);
		
        $typeIndex = array();
        foreach($typeList as $v){
            $typeIndex[$v['id']] = $v['name'];
        }
        $res = array('page' => 1, 'pages' => 1, 'size' => 1, 'total' => 0, 'items' => null);
		
        $totalSql = "select count(news_id) as count from news where ".$where ;
        $totalQuery = $this->_dbNewsModel->getAdapter()->query($totalSql);
		$totalFetch = $totalQuery->fetch();
		$total = $totalFetch['count'];
        if(!$total) {
            return $res;
        }
        $result['total'] = $total;  
        $items = null;
/*
        $sql = " SELECT `news_id`, `cat_id`, `title`,`update_at` FROM `news` ";
        $sql.= " WHERE ";
        $sql.= $where;
        $sql.= $order;
        $size = $size ? $size : 18;
        $start = ($page - 1) * $size;
        $limit = " LIMIT " . $start . ",  " . $size;
        $sql.= $limit;
        $itemsQuery = $this->_dbNewsModel->getAdapter()->query($sql);
		$items = $itemsQuery->fetchAll();
*/	
        $size = $size ? $size : 18;
        $start = ($page - 1) * $size;
	$selectNews = $db->select();
	$selectNews->from('news');
	$selectNews->where($where,'');	
	$selectNews->order($order);
	$selectNews->limit($size, $start);
	$items = $db->fetchAll($selectNews);
        if ($items) {
            foreach ($items AS $key => $item) {
                $items[$key] = $item;
                $items[$key]['sub_name'] = $typeIndex[$item['cat_id']];
            }
        }
        $pages = ceil($total / $size);
        $res['pages'] = $pages;
        $res['page'] = ($page >= $pages) ? $pages : $page;
        $res['size'] = $size;
        $res['total'] = $total;
        $res['items'] = $items;
        return $res;
    }

    /**
     * 新闻内容展示
     *
     */
    public function show($news_id){
        $news_item = $this->getItemFront($news_id);
        if ($news_item == false) {
            die('');
        }
        $prev = $news_item['prev'];
        $next = $news_item['next'];
        //调用时 去拼接链接地址
        /*if ($prev){
            $prev['link'] = $type_name_en . $prev['news_id'] . '.html';
        }
        if ($next){
            $next['link'] =  $type_name_en . $next['news_id'] . '.html';
        }*/            
        $data['content'] = $news_item['content'];
        $data['title'] = $news_item['title'];
        $data['sub_id'] = $news_item['cat_id'];
        $data['prev'] = $prev;
        $data['next'] = $next;
        $data['update_at'] = $news_item['update_at'];
        return json_decode(json_encode($data));
    }

    public function getItemFront($news_id) {
        if ($news_id == false)
            return false;
        $item = null;
        $db = $this->_dbNewsModel->getDbObject();
        $select = $db->select();
        $select->from('news');
        $select->where('news_id = ?', $news_id);
        $item = $db->fetchRow($select);
        if (!$item) {
            return false;
        }
        $item['prev'] = array();
        $item['next'] = array();

        $order = ' update_at DESC';
        $next_order = str_replace('DESC','ASC',$order);

        //获取下一篇文章信息
        $select = $db->select();
        $select->from('news');
        $select->where(" update_at > ?", $item['update_at']);
        $select->where(" game_id = ?", $item['game_id']);
        $select->where(" cat_id = ?", $item['cat_id']);
        $select->where(" news_id != ?", $item['news_id']);
        $select->order($next_order);
        $item['next'] = $db->fetchRow($select);

        //获取上一篇文章信息
        $select = $db->select();
        $select->from('news');
        $select->where(" update_at < ?", $item['update_at']);
        $select->where(" game_id = ?", $item['game_id']);
        $select->where(" cat_id = ?", $item['cat_id']);
        $select->where(" news_id != ?", $item['news_id']);
        $select->order($order);
        $item['prev'] = $db->fetchRow($select);

        return $item;
    }

}
