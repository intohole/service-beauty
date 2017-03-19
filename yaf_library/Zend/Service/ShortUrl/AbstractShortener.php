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
 * @package    Zend_Service_ShortUrl
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * @see Zend_Service_Abstract
 */

/**
 * @see Zend_Service_ShortUrl_Shortener
 */

/**
 * @category   Zend
 * @package    Zend_Service_ShortUrl
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_ShortUrl_AbstractShortener
    extends Zend_Service_Abstract
    implements Zend_Service_ShortUrl_Shortener
{
    /**
     * Base URI of the service
     *
     * @var string
     */
    protected $_baseUri = null;


    /**
     * Checks whether URL to be shortened is valid
     *
     * @param string $url
     * @throws Zend_Service_ShortUrl_Exception When URL is not valid
     */
    protected function _validateUri($url)
    {
        if (!Zend_Uri::check($url)) {
            throw new Zend_Service_ShortUrl_Exception(sprintf(
                'The url "%s" is not valid and cannot be shortened', $url
            ));
        }
    }

    /**
     * Verifies that the URL has been shortened by this service
     *
     * @throws Zend_Service_ShortUrl_Exception If the URL hasn't been shortened by this service
     * @param string $shortenedUrl
     */
    protected function _verifyBaseUri($shortenedUrl)
    {
        if (strpos($shortenedUrl, $this->_baseUri) !== 0) {
            throw new Zend_Service_ShortUrl_Exception(sprintf(
                'The url "%s" is not valid for this service and the target cannot be resolved',
                $shortenedUrl
            ));
        }
    }
}