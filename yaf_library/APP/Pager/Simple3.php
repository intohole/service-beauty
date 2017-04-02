<?php
/*
 * Created for message system.
 * Added by shaogx 2010-12-16
 */
//require_once 'APP/Pager/Abstract.php';

class APP_Pager_Simple3 extends APP_Pager_Abstract {

    public $db = null;
    public $sql = null;
    public $countsql = null;
    public $query = '';

// 设置数据总数
    public function setItems() {
        $this->items = $this->db->fetchOne($this->countsql);
        return $this;
    }

    // 获取每页的数�?
    protected function setData() {
        $start = ($this->page - 1) * $this->itemNum;
        $sql = $this->sql . " LIMIT $start, $this->itemNum";
        $this->data = $this->db->fetchAll($sql);
        return $this;
    }

    // 这是�?��决定页码条在前台显示的页码范�?
    public function setPageBar() {
        $first = floor(($this->page - 1) / $this->pageNum) * $this->pageNum + 1;
        $last = $first + $this->pageNum - 1;
        if ($this->pages <= $last) {
            $this->pageBar = range($first, $this->pages);
        } else {
            $this->pageBar = range($first, $last);
        }
    }

    public function generate() {
        // 全部记录数量(查询)
        $this->items = $this->getItems();

        // 全部页码数量(计算)
        $this->pages = (int) ceil($this->items / $this->itemNum);

        // 如果没有查询结果，直接退出 TODO 还有一点点bug. "共0条记录,1/0页"
        if ($this->pages == 0) {
            return false;
        }

        // 五种页码数值(计算)
        $this->first = 1;
        $this->last = $this->pages;
        $this->page = $this->page < $this->pages ? $this->page : $this->pages;
        $this->next = ($this->page + 1) > $this->last ? $this->last : ($this->page + 1);
        $this->prev = ($this->page - 1) < $this->first ? $this->first : ($this->page - 1);

        // 设置当前页
        $this->setPageBar();

        return $this;
    }

}

