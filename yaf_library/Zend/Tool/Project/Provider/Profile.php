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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Profile.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Profile extends Zend_Tool_Project_Provider_Abstract
{

    /**
     * show()
     *
     */
    public function show()
    {
        $this->_loadProfile();

        $profileIterator = $this->_loadedProfile->getIterator();

        foreach ($profileIterator as $profileItem) {
            $this->_registry->getResponse()->appendContent(
                str_repeat('    ', $profileIterator->getDepth()) . $profileItem
            );
        }

    }
}
