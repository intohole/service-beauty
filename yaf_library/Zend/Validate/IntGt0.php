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
 *
 *            (c)
 *
 *
 *            2005-2012
 *
 *
 *            Zend
 *
 *
 *            Technologies
 *
 *
 *            USA
 *
 *
 *            Inc.
 *
 *
 *            (http://www.zend.com)
 * @license
 *          http://framework.zend.com/license/new-bsd
 *
 *
 *          New
 *
 *
 *          BSD
 *
 *
 *          License
 */
class Zend_Validate_IntGt0 extends Zend_Validate_Int {
	const NOT_GT_0 = 'notGt0';

	/**
	 * Constructor
	 * for
	 * the
	 * integer
	 * validator
	 *
	 * @param string|Zend_Config|Zend_Locale $locale
	 */
	public function __construct($locale = null) {
		parent::__construct ( $locale );
		$this->_messageTemplates [self::NOT_GT_0] = "'%value%' appear to be not greater than 0";
	}
	public function isValid($value) {
		if (true == parent::isValid ( $value )) {
			if ($value <= 0) {
				$this->_error ( self::NOT_GT_0 );
			}
		}
		return true;
	}
}
