<?
require_once 'APP/Pager/Abstract.php';
class APP_Pager_Simple2 extends APP_Pager_Abstract
{
	public $db = null;
	public $sql = null;
	public $countsql = null;

// 设置数据总数
    protected function setItems()
	{
		$this->items = $this->db->fetchOne($this->countsql);
		return $this;
	}
   

    // 获取每页的数�?
    protected function setData()
	{
		$start = ($this->page - 1) * $this->itemNum;
		$sql = $this->sql . " LIMIT $start, $this->itemNum";
		$this->data = $this->db->fetchAll($sql);
		return $this;
	}

    // 这是�?��决定页码条在前台显示的页码范�?
    public function setPageBar()
    {
        $first =  floor( ($this->page - 1) / $this->pageNum ) * $this->pageNum  + 1 ;
        $last = $first + $this->pageNum - 1 ;
        if( $this->pages <= $last ){
            $this->pageBar = range($first,$this->pages);
        }
        else{
            $this->pageBar = range($first,$last);
        }
    }

    // 设置查询sql
	public function setSql($db,$sql,$sqls)
	{
		$this->db = $db;
		$this->sql = $sql;
		$this->countsql = $sqls;
		return $this;
	}
}

