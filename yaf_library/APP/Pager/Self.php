<?php
/**
*	基于数组的分页
*	autor：gengxuliang 2011-3-9
*/
require_once 'APP/Pager/Abstract.php';
class APP_Pager_Self extends APP_Pager_Abstract
{

    // 设置数据总数
    protected function setItems()
    {
		$this->items = count($this->data);

        return $this;
    }

    // 获取每页的数据
    protected function setData()
    {
        if(is_array($this->data)){
			$temp = array_chunk($this->data,$this->itemNum);
			$index = $this->page - 1;
			$this->data = $temp[$index];
		}
        return $this;
    }

    // 这是一个决定页码条在前台显示的页码范围
    protected function setPageBar()
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

    // 设置查询APP_Table
    public function setTable($table , $where = null, $order = null)
    {
       
    }

	//设置当前要查询的数组
	public function setArray($arr){
		$this->data = $arr;
		return $this;
	}

	/**
	 * 自定义初始化数组
	 * @param $data array 要分页的数据
	 * @param $url string 分页链接
	 * @param $currentPage int 当前页
	 * @param $pageSize int 每页显示多少条
	 * @param $pageNum int 显示多少个分页数字
	 * return object
	 */
	public function init($data,$url,$currentPage=1,$pageSize=20,$pageNum=5) {
			$this->setArray($data)
			->setUrl($url)
			->setPage($currentPage)
			->setPageNum($pageNum)
			->setItemNum($pageSize)
			->generate();
			return $this;
	}
}

