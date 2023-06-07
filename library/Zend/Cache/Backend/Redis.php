<?php
/**
 * @see Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache/Backend/ExtendedInterface.php';

/**
 * @see Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';

/**
 * Class Zend_Cache_Backend_Redis
 *
 */
class Zend_Cache_Backend_Redis extends Cm_Cache_Backend_Redis implements Zend_Cache_Backend_ExtendedInterface
{
    public function __construct($options = array())
    {
        parent::__construct($options['redis']);
    }
}
