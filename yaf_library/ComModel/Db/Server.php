<?php

class ComModel_Db_Server extends Shanty_Mysql_Table
{
    protected $_dbConfigName = 'letvPaycenter';
    protected $_schema = 'letv_paycenter';
    protected $_name = 'server';
    
    protected $_primary = array('server_id');
    
    
    protected $_requirements = array(
        'game_id' => array(
                "Required",
                "Validator:Zend_Validate_Int",
        ),
        'server_sn' => array(
                "Required",
                "Validator:Zend_Validate_Int",
        ),
        'server_name' => array(
                'Required',
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
        'server_start_time' => array(
                "Validator:Zend_Validate_Int",
        ),
        'server_state' => array(
                "Validator:Zend_Validate_Int",
        ),
        'server_server_url' => array(
                "Validator:Zend_Validate_StringLength"=>array("min"=>1, "max"=>250),
        ),
        'server_extension' => array(
        ),
        'server_ext_string' => array(
        ),
        'server_ext_string_two' => array(
        ),
        'server_ext_int' => array(
        ),
        'server_ext_int_two' => array(
        ),
        'maintain_start' => array(
        ),
        'maintain_end' => array(
        ),
        'num_level' => array(
        ),
        'ifshow' => array(
        ),
        'rank' => array(
        ),
        'allow_ips' => array(
        ),
        'maintain_url' => array(
        ),
        'ifepay' => array(
        ),
    );
    
}