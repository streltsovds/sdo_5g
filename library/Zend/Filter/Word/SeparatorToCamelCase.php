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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SeparatorToCamelCase.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_PregReplace
 */
require_once 'Zend/Filter/Word/Separator/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Word_SeparatorToCamelCase extends Zend_Filter_Word_Separator_Abstract
{

    public function filter($value)
    {
        // a unicode safe way of converting characters to \x00\x00 notation
        $pregQuotedSeparator = preg_quote($this->_separator, '#');

        if (self::isUnicodeSupportEnabled()) {
            parent::setMatchPattern(array('#('.$pregQuotedSeparator.')(\p{L}{1})#','#(^\p{Ll}{1})#'));
            parent::setReplacement(array("strtoupper('\\2')","strtoupper('\\1')"));
        } else {
            parent::setMatchPattern(array('#('.$pregQuotedSeparator.')([A-Za-z]{1})#','#(^[A-Za-z]{1})#'));
            parent::setReplacement(array("strtoupper('\\2')","strtoupper('\\1')"));
        }

        // С версии PHP 5.5.0, если передается модификатор "\e", вызывается ошибка уровня E_DEPRECATED,
        // с версии PHP 7.0.0 в этом случае выдается E_WARNING и сам модификатор игнорируется,
        // поэтому в данном случае работаем через preg_replace_callback().
        // В оригинале было так:
        //         return parent::filter($value);
        if ($this->_matchPattern == null) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception(get_class($this) . ' does not have a valid MatchPattern set.');
        }

        return preg_replace_callback(
            $this->_matchPattern,
            function ($matches) {
                return strtoupper($matches[0]);
            },
            $value
        );
    }

}
