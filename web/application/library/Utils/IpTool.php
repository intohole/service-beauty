<?php
/**
 * Utils_IpTool
 * IP������
 * @author jianghai
 */

    class Utils_IpTool {


        /**
         * getAddressByIp
         * ͨ��IP����û������ַ
         * @param array $ip
         * @access public
         * @return array
         */
        public function getAddressByIp($ip){
            $url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
            $ch = curl_init($url);
            curl_setopt($ch,CURLOPT_ENCODING ,'utf8');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // ��ȡ���ݷ���
            $location = curl_exec($ch);
            $location = json_decode($location);
            curl_close($ch);
            $loc = "";
            if($location===FALSE) return "";
            if (empty($location->desc)) {
                $loc = $location->province.$location->city.$location->district.$location->isp;
            }else{         $loc = $location->desc;
            }
            return $loc;
        }

        /*
        �������ݸ�ʽ��
        {"code":0,"data":{"ip":"210.75.225.254","country":"\u4e2d\u56fd","area":"\u534e\u5317",
        "region":"\u5317\u4eac\u5e02","city":"\u5317\u4eac\u5e02","county":"","isp":"\u7535\u4fe1",
        "country_id":"86","area_id":"100000","region_id":"110000","city_id":"110000",
        "county_id":"-1","isp_id":"100017"}}
        ����code��ֵ�ĺ���Ϊ��0���ɹ���1��ʧ�ܡ�

        ���ز�����⣺
        code ״̬�룬����Ϊ0���쳣��ʱ��Ϊ��0
        data ��ѯ���Ľ��
        country ����
        country_id ���Ҵ���
        area �������ƣ����ϡ�����...��
        area_id �������
        region ʡ����
        region_id ʡ���
        city ������
        city_id �б��
        county ������
        county_id �ر��
        isp ISP���������ƣ�����/��ͨ/��ͨ/�ƶ�...��
        isp_id ISP�����̱��
        ip ��ѯ��IP��ַ
         */

    }
