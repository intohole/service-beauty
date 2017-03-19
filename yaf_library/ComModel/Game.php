<?php
/**
 * 游戏公用model
 * 包含所有游戏game的方法,其他地方不许单独开辟,有需要在这个model里增加,
 * 一定要做到数据读写都从一个地方产生.
 *
 * @author liumignzhao
 * @date 2014年4月10日 13:53:16
 */
class ComModel_Game {

    private $_dbGameModel = null;
	private $_memcacheKey = null;

    function __construct() {
        $this->_dbGameModel = new ComModel_Db_Game();
		$this->_memcacheKey = 'letv_portal_';
    }

    /**
     * 获取游戏列表数据
     * 显示全部,下线的游戏也显示
     * 游戏数据不是太多,不提供搜索.
     * @param int $page 当前页码
     * @param int $rows 每页显示数量
     * @param string $sort 排序字段
     * @param string $order 正序or倒序
     * @return array 结果集
     */
    public function getListForAdmin($page, $rows, $order) {
        if(!$page) {
            $page = 1;
        }
        if(!$rows) {
            $rows = 10;
        }
        //默认空数据
        $result = array(
            'total' => 0,
            'rows' => array()
        );
        
        //获取总数
        $db = $this->_dbGameModel->getDbObject();
        $select = $db->select();
        $select->from('game', 'count(game_id) as count');
        $total = $db->fetchOne($select);

        if(!$total) {
            return $result;
        }
        $result['total'] = $total;                
        
        $startRows = ($page - 1) * $rows;
        $data = $this->_dbGameModel->fetchAll($where, $order, $rows, $startRows);

        $oneValue = array();
        foreach($data->toArray() as $value) {
            //处理辅助显示数据
            $value['game_pay_pic_img'] = '<a href="'. $value['game_pay_pic']. '" target="_blank" title="点击新窗口打开">';
            $value['game_pay_pic_img'] .= '<img src="'. $value['game_pay_pic']. '" width="25" height="25" /></a>';
            $value['game_role_msg'] = !$value['game_role'] ? '不用选择' : '需要选择';
            $value['game_state_msg'] = ($value['game_state'] == 1) ? '运营中' : '已下线';
            $value['game_server'] = '<a href="/server/index?game_id='. $value['game_id']. '">查看服</a>';

            $result['rows'][] = $value;
        }
        return $result;
    }

    /**
     * 根据条件获取记录集
     * @param array $field 获取的字段名称. 字段名作为这个参数的value.默认值为 game_id
     * @param array $where 获取的条件. 条件作为参数的value. 默认为 1=1
     * @param array $gameState 游戏状态干预结果集. 默认值为1,只显示运营中的游戏. 0为显示全部,包含下线的游戏.
     * @return array 结果集
     */
    public function getGameItem($field = array('game_id'), $where = array('1=1'), $gameState = 1, $gameOrder = 'game_id') {
        if(!$where) {
            return false;
        }

        if($gameState) {
            $where[] = 'game_state = 1';
        }
        
        $db = $this->_dbGameModel->getDbObject();
        $select = $db->select();
        $select->from('game',$field);
        $select->where(implode(' AND ',$where));
        $data = $db->fetchAll($select);

        return $data;
    }
    
    public function saveData($data){
        if(array_key_exists('game_id', $data) && $data['game_id']) {
            $this->_dbGameModel = $this->_dbGameModel->find($data['game_id']);
        }
        foreach($data as $key => $value) {
            $this->_dbGameModel->$key = $value;
        }
        $result = $this->_dbGameModel->save();
        $memcache = new Cache_Memcache();
        $memcache->remove('letv_portal_game');//清除缓存 游戏列表
        return $result;
    }

}
