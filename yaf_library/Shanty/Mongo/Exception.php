<?php

/**
 * @category   Shanty
 * @package    Shanty_Mongo
 * @copyright  Shanty Tech Pty Ltd
 * @license    New BSD License
 * @author     Coen Hyde
 */
class Shanty_Mongo_Exception extends Exception {
	public function __construct($message = "", $code = 0x1000, Exception $previous = NULL) {
		parent::__construct ( $message, $code, $previous );
	}
}