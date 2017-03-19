<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SendSms.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * @see Zend_Service_DeveloperGarden_Client_ClientAbstract
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_SendSms_SendSMSResponse
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_SendSms_SendFlashSMSResponse
 */

/**
 * @see Zend_Service_DeveloperGarden_Request_SendSms_SendSMS
 */

/**
 * @see Zend_Service_DeveloperGarden_Request_SendSms_SendFlashSMS
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_SendSms
    extends Zend_Service_DeveloperGarden_Client_ClientAbstract
{
    /**
     * wsdl file
     *
     * @var string
     */
    protected $_wsdlFile = 'https://gateway.developer.telekom.com/p3gw-mod-odg-sms/services/SmsService?wsdl';

    /**
     * wsdl file local
     *
     * @var string
     */
    protected $_wsdlFileLocal = 'Wsdl/SmsService.wsdl';

    /**
     * Response, Request Classmapping
     *
     * @var array
     *
     */
    protected $_classMap = array(
        'sendSMSResponse'      => 'Zend_Service_DeveloperGarden_Response_SendSms_SendSMSResponse',
        'sendFlashSMSResponse' => 'Zend_Service_DeveloperGarden_Response_SendSms_SendFlashSMSResponse'
    );

    /**
     * this function creates the raw sms object that can be used to send an sms
     * or as flash sms
     *
     * @param string $number
     * @param string $message
     * @param string $originator
     * @param integer $account
     *
     * @return Zend_Service_DeveloperGarden_Request_SendSms_SendSMS
     */
    public function createSms($number = null, $message = null, $originator = null, $account = null)
    {
        $request = new Zend_Service_DeveloperGarden_Request_SendSms_SendSMS($this->getEnvironment());
        $request->setNumber($number)
                ->setMessage($message)
                ->setOriginator($originator)
                ->setAccount($account);
        return $request;
    }

    /**
     * this function creates the raw sms object that can be used to send an sms
     * or as flash sms
     *
     * @param string $number
     * @param string $message
     * @param string $originator
     * @param integer $account
     *
     * @return Zend_Service_DeveloperGarden_Request_SendSms_SendFlashSMS
     */
    public function createFlashSms($number = null, $message = null, $originator = null, $account = null)
    {
        $request = new Zend_Service_DeveloperGarden_Request_SendSms_SendFlashSMS($this->getEnvironment());
        $request->setNumber($number)
                ->setMessage($message)
                ->setOriginator($originator)
                ->setAccount($account);
        return $request;
    }

    /**
     * sends an sms with the given parameters
     *
     * @param Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract $sms
     *
     * @return Zend_Service_DeveloperGarden_Response_SendSms_SendSmsAbstract
     */
    public function send(Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract $sms)
    {
        $client = $this->getSoapClient();
        $request = array(
            'request' => $sms
        );
        switch ($sms->getSmsType()) {
            // Sms
            case 1 :
                $response = $client->sendSms($request);
                break;
            // flashSms
            case 2 :
                $response = $client->sendFlashSms($request);
                break;
            default : {
                throw new Zend_Service_DeveloperGarden_Client_Exception('Unknown SMS Type');
            }
        }

        return $response->parse();
    }
}