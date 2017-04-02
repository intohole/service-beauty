<?php
/**
 * 游戏服公共Model
 * 包含所有游戏服server的方法,其他地方不许单独开辟,有需要在这个model里增加,
 * 一定要做到数据读写都从一个地方产生.
 *
 * @author liumingzhao
 * @date 2014年4月10日 14:50:33
 */
class ComModel_Server {
    private $_dbServerModel;


    function __construct() {
        $this->_dbServerModel = new ComModel_Db_Server();
    }

    /**
     * updateServer
     * 更新服务器信息
     * for backend
     *
     * @param array $data
     * @access public
     * @return int
     */
    public function updateServer($data) {
        if(empty($data)) {
            return false;
        }
        if(empty($data['game_id'])) {
            return false;
        }
        
        if(array_key_exists('server_id', $data) && $data['server_id']) {
            $this->_dbServerModel = $this->_dbServerModel->find($data['server_id']);
            if($data['server_start_time'] > time()){
                $data['server_state'] = 0;
            }
        }
        else{
           $data['server_state'] = 0;
        }
        
        foreach($data as $key => $value) {
            $this->_dbServerModel->$key = $value;
        }
        $result = $this->_dbServerModel->save();
        return $result;
    }

    /**
     * ajax异步获取服务器数据
     * For backend
     *
     * @param int $page
     * @param int $pageSize
     * @param array $where sql条件数组
     * @return array 结果集
     */
    public function getServerListByPage($page = 1, $pageSize = 10, $where = array()) {
        //默认空数据
        $result = array(
            'total' => 0,
            'rows' => array()
        );
        
        //--------------获取总数
        $db = $this->_dbServerModel->getDbObject();
        $select = $db->select();
        $select->from('server', 'count(server_id) as count');
        if(array_key_exists('server_sn', $where)) {
            $select->where(" `server_sn` = ?",$where['server_sn']);
        }
        if(array_key_exists('game_id', $where)) {
            $select->where(" `game_id` = ?", $where['game_id']);
        }
        $total = $db->fetchOne($select);
        if(!$total) {
            return $result;
        }
        $result['total'] = $total;  
        
        //获取游戏服数据
        $start = ($page - 1) * $pageSize;        
        $whereString = '';
        $returnWhereArray = array('1=1');
        if(array_key_exists('server_sn', $where)) {
            $returnWhereArray[] = " `server_sn` =". $where['server_sn'];
        }
        if(array_key_exists('game_id', $where)) {
            $returnWhereArray[] = " `game_id` =". $where['game_id'];
        }
        $whereString = implode(' AND ', $returnWhereArray);
        
        $data = $this->_dbServerModel->fetchAll($whereString, 'server_id DESC ', $pageSize, $start);

        $server_state = array(
            '-1' => '已删除', 
            '0' => '未开放', 
            '1' => '已开放', 
            '2' => '在维护', 
            '3' => '已停服'
        );
        $field = array(
            'game_id',
            'game_name'
        );
        $gameInfo = array();
        $game_arr = array();
        $gameModel = new ComModel_Game();
        $gameInfo = $gameModel->getGameItem($field, array('1=1'), 0);
        foreach($gameInfo as $k => $v) {
            $game_arr[$v['game_id']] = $v['game_name'];
        }
        foreach($data->toArray() as $value) {
            $value['server_start_time'] = date('Y-m-d H:i:s', $value['server_start_time']);
            $value['maintain_start'] = date('Y-m-d H:i:s', $value['maintain_start']);
            $value['maintain_end'] = date('Y-m-d H:i:s', $value['maintain_end']);
            //格式化时间
            $value['state'] = @$server_state[$value['server_state']];
            $value['game_name'] = @$game_arr[$value['game_id']];
            $result['rows'][] = $value;
        }
        return $result;
    }

    /**
     * 获取某个游戏的服
     */    
    public function getServerListByGameId($gameId, $rank = 0, $limit = 0){
        if(!intval($gameId)){
            return false;
        }
        $db = $this->_dbServerModel->getDbObject();
        $select = $db->select();
        $select->from('server');
        $select->where(' game_id = ?', $gameId);
        $select->where(' server_state in (1,2)');
        $select->where(' ifshow = ?',1);
        if(!$rank){
            $select->order(' server_sn ASC');
        }
        if($rank){
            $select->where(' rank > ?', 0);
            $select->order(' rank ASC');
            $limit = $limit ? $limit : 4;
            $select->limit($limit);
        }
        $result = $db->fetchAll($select);
        return $result;
    }

    /**
     * 获取玩过的服
     */
    public function getPlayedServers($userId,$gameId = null,$limit = 4){
        $result = array();
        $sdk = new Sdk_Passport_Letv_SdkNew();
        $res = $sdk->playedServer($userId,$gameId,$limit);
        if($res && $res->errorCode == 0){
            $resultFromApi = $res->value;
        }
        foreach($resultFromApi as $value){
            $server = $this->getServerById($value['server_id']);
            $value['server_name'] = $server['server_name'];
            $value['num_level'] = $server['num_level'];
            $result[] = $value;
        }
        return $result;
    }

    /**
     * 获取最新开服
     */
    public function getNewServerList($limit = 4){
        $db = $this->_dbServerModel->getDbObject();
        $select = $db->select();
        $select->from('server');
        $select->where(' server_state in (1,2)');
        $select->where(' server_start_time <= ?',time());
        $select->order(' server_start_time DESC');
        $select->limit($limit);
        $result = $db->fetchAll($select);
        return $result;
    }

    /**
     * 获取服信息
     */
    public function getServerByID($serverId = 0){
        $db = $this->_dbServerModel->getDbObject();
        $select = $db->select();
        $select->from('server');
        $select->where(' server_id = ?', $serverId);
        $select->limit(1);
        $result = $db->fetchRow($select);
        return $result;
    }
}
