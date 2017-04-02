<?php
class LoginLogModel {

    private $_loginLogDb;

    private $_userModel;

    public function __construct() {
        $this->_loginLogDb = new Admin_LoginLogModel();
        $this->_userModel = new Admin_UserModel();
    }







}
