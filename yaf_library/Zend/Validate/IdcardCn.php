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
 * @category
 *           Zend
 * @package
 *          Zend_Validate
 * @copyright
 *            Copyright
 *
 *            (c)
 *
 *            2005-2012
 *
 *            Zend
 *
 *            Technologies
 *
 *            USA
 *
 *            Inc.
 *
 *            (http://www.zend.com)
 * @license
 *          http://framework.zend.com/license/new-bsd
 *
 *          New
 *
 *          BSD
 *
 *          License
 */
class Zend_Validate_IdcardCn extends Zend_Validate_Abstract {
	const INVALID = 'invalid';

	/**
	 *
	 * @var array
	 */
	protected $_messageTemplates = array (
			self::INVALID => ""
	);
	public function isValid($value) {
		$valid = true;
		if (! is_string ( $value )) {
			$valid = false;
		} else if (strlen ( $value ) == 15 && preg_match ( '/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/', $value )) {
			$valid = true;
		} else if (strlen ( $value ) == 18 && preg_match ( '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/', $value )) {
			$valid = true;
		} else {
			$valid = false;
		}
		if ($valid) {
			return true;
		} else {
			$this->_error ( self::INVALID );
			return false;
		}
	}
}
