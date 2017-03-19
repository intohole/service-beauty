<?php

class ComModel_Db_Game extends Shanty_Mysql_Table
{
    protected $_dbConfigName = 'letvPaycenter';
    protected $_schema = 'letv_paycenter';
    protected $_name = 'game';
    
    protected $_primary = array('game_id');
    
    
    protected $_requirements = array(
        'game_name' => array(
                'Required',
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
        'game_en_name' => array(
                'Required',
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
        'game_website' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>0, "max"=>250),
        ),
        'game_pay_pic' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
        'game_order' => array(
                "Validator:Zend_Validate_Int",
        ),
        'game_state' => array(
                "Validator:Zend_Validate_Int",
        ),
        'game_role' => array(
                "Validator:Zend_Validate_Int",
        ),
        'coin_unit' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>20),
        ),
        'coin_rate' => array(
                "Validator:Zend_Validate_Int",
        ),
        'game_type' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>45),
        ),
        'game_big_pic' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
        'game_icon' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
        'game_recommend' => array(
                "Validator:Zend_Validate_Int",
        ),
        'game_small_pic' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
    );
    
}