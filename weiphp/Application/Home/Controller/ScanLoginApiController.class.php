<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Controller;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class ScanLoginApiController extends HomeController
{

    function getQrCode()
    {
        $key = I('key');
        if (empty($key)) {
            echo 0;
            exit();
        }
        $map['addon'] = 'ScanLogin';
        $map['extra_text'] = $key;
        $info = M('qr_code')->where($map)
            ->field(true)
            ->find();
        
        $qr_code = $info['qr_code'];
        if ($info && (NOW_TIME - $info['cTime'] > $info['expire_seconds'])) {
            M('qr_code')->where($map)->delete();
            $qr_code = '';
        }
        
        if (! $qr_code) {
            $qr_code = D('Home/QrCode')->add_qr_code('QR_SCENE', 'ScanLogin', 0, 0, $key);
        }
        echo $qr_code;
    }

    function checkLogin()
    {
        $key = I('key');
        $user = S($key);
        if ($user['uid'] > 0) {
            echo json_encode($user);
        } else {
            echo 0;
        }
    }
}
