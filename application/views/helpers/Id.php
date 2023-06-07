<?php
class HM_View_Helper_Id extends Zend_View_Helper_Abstract
{
    static $counters = array();
    static $counter = 0;
    static $delimiter = '-';
    public function id($prefix = 'autogenerated', $useRandomSalt = true)
    {
        if (is_array($prefix)) {
            $prefix = join(self::$delimiter, $prefix);
        } else if (!is_string($prefix)) {
            $prefix = (string)$prefix;
        }
        if (empty($prefix)) {
            $prefix = 'autogenerated';
        }
        if (!isset(self::$counters[$prefix])) {
            $counters[$prefix] = 0;
        }
        if ($useRandomSalt) {
            $prefix .= (self::$delimiter).mt_rand(0, 9999);
        }
        self::$counter++;

        if (isset($counters[$prefix])) {
            self::$counters[$prefix]++;
        } else {
            self::$counters[$prefix] = 1;
        }
        return $prefix.(self::$counters[$prefix]);
    }
}