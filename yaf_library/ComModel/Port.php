<?php
class ComModel_Port {
    private $_dbPort;
    public $userId;

    function __construct() {
        $this->_dbPort = new ComModel_Db_Port();
    }

    /**
     * 根据用户id,项目id获取用户可以访问的url地址.
     *
     * @return array
     * array(2) {
     *     [1]=>   key为1,表示正则
     *     array(1) {
     *       [372]=>
     *       string(1) "/"
     *     }
     *     [0]=>   key为0,表示不正则
     *     array(1) {
     *       [374]=>
     *       string(5) "/game"
     *     }
     *   }
     */
    public function getAllowUrlByUserIdAppId($userId, $appId) {
        $this->userId = $userId;
        $proInfoArr = $this->getProInfoByUserId();
        $urlInfoArr = $this->getUrlInfoByUserId();

        $AccessUrlArr = array();
        $result = array();

        if(!empty($proInfoArr) && !empty($urlInfoArr)) {//如果项目或url为空，直接退出
            foreach($urlInfoArr as $key => $value) {
                $AccessUrlArr[$value['proId']][$value['reg']][$value['id']] = $value['actionUrl'];
            }
        }
        if(isset($AccessUrlArr[$appId]) && !empty($AccessUrlArr[$appId])) {
            $result = $AccessUrlArr[$appId];
        }

        return $result;
    }

    public function validateAccess($userId, $appId, $url) {
        $allUrl = $this->getAllowUrlByUserIdAppId($userId, $appId);
        if( isset($allUrl[0]) 
            && !empty($allUrl[0]) 
            && in_array($url, $allUrl[0])
        ) {
            return true;
        }
        else if(isset($allUrl[1]) && !empty($allUrl[1])) {
            foreach($allUrl[1] as $k => $v) {
                $v = str_replace("/", "\/", $v);
                preg_match("/". $v. "/is", $url, $match);
                if(!empty($match)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 根据userId 获取所有url的基本属性
     * param int或array urlId
     * return array
     */
    public function getUrlInfoByUserId() {
        if(!$this->userId)
            return array();
        $sql = "SELECT distinct b.urlId FROM `ledu_role_user` a, `ledu_role_url` b WHERE a.roleId=b.roleId AND a.userId=$this->userId";
        //$urlArr = $this->db->fetchCol($sql);

        $query = $this->_dbPort->getAdapter()->query($sql);
        $urlArr = $query->fetchAll();
        if(empty($urlArr))
            return array();
        $urlArrArray = array();
        foreach($urlArr as $v) {
            $urlArrArray[] = $v['urlId'];
        }
        $urlList = implode(',', $urlArrArray);
        $sql = "SELECT id,proId,actionUrl,actionName,reg FROM `ledu_project_url` WHERE id in ($urlList)";
        //return $this->db->fetchAll($sql);
        $query1 = $this->_dbPort->getAdapter()->query($sql);
        $urlArr = $query1->fetchAll();
        return $urlArr;
    }

    /**
     * 根据userId 获取所有项目的基本属性
     * param int或array urlId
     * return array
     */
    public function getProInfoByUserId($sort = '') {
        $data = array();
        if(!$this->userId)
            return array();
        $sql = "SELECT distinct b.proId FROM `ledu_role_user` a, `ledu_role_url` b WHERE a.roleId=b.roleId AND a.userId=$this->userId";

        //$proArr = $this->db->fetchCol($sql);
        $query = $this->_dbPort->getAdapter()->query($sql);
        $proArr = $query->fetchAll();

        if(empty($proArr))
            return array();
        $proArrArray = array();
        foreach($proArr as $v) {
            $proArrArray[] = $v['proId'];
        }
        $proList = implode(',', $proArrArray);
        $where = '';
        if($sort != '')
            $where = ' and sort='. $sort;
        $sql = "SELECT id,proName,proEname,proLogo,proUrl FROM `ledu_project` WHERE id in ($proList) $where order by `order` asc ";
        //$result = $this->db->fetchAll($sql);
        $query1 = $this->_dbPort->getAdapter()->query($sql);
        $result = $query1->fetchAll();

        if(!empty($result)) {
            foreach($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            return $data;
        }
        else {
            return array();
        }
    }

}
