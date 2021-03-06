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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Int.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 *
 * @see Zend_Validate_Abstract
 */

/**
 *
 * @see Zend_Locale_Format
 */

/**
 *
 * @category Zend
 * @package Zend_Validate
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class Zend_Validate_Boolean extends Zend_Validate_Abstract {
	const NOT_BOOLEAN = 0;

	/**
	 *
	 * @var array
	 */
	protected $_messageTemplates = array (
			self::NOT_BOOLEAN => "Value is not boolean"
	);

	/**
	 * Defined by Zend_Validate_Interface
	 *
	 * Returns true if and only if $value is a valid integer
	 *
	 * @param string|integer $value
	 * @return boolean
	 */
	public function isValid($value) {
		if (is_bool ( $value )) {
			return true;
		} else {
			$this->_error ( self::NOT_BOOLEAN );
			return false;
		}
	}
}