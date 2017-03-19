<?php

class ComModel_Db_News extends Shanty_Mysql_Table
{
    protected $_dbConfigName = 'cms';
    protected $_schema = 'letv_cms';
    protected $_name = 'news';
    
    protected $_primary = array('news_id');
    
    
    protected $_requirements = array(
        'title' => array(
        ),
        'cat_id' => array(
        ),
        'game_id' => array(
        ),
        'content' => array(
        ),
        'desc' => array(
        ),
        'status' => array(
        ),        
        'update_at' => array(
        ),
        'view_count' => array(
        )
    );
    
}
