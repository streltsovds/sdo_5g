<?php


abstract class HM_Index_Abstract
{
    const OUTPUT_ENCODING = 'UTF-8';

    protected $items;
    protected $output;


    abstract public function init();

    public function getOutput()
    {
        return $this->output;
    }

    public static function convertAndFilter($str, $inEncoding = null, $outEncoding = self::OUTPUT_ENCODING)
    {
        if(empty($inEncoding)) {
            $inEncoding = (Zend_Registry::get('config'))->charset;
        }

        $str = preg_replace('/[\x00-\x1F]/', ' ', $str);
        $str = iconv($inEncoding, $outEncoding, $str);

        $str = self::replaceSymbols($str);

        return $str;
    }

    private static function replaceSymbols(string $str)
    {
        $find = ['_', '"', '\''];
        // Заменяем всё на пробелы
        $replace = array_fill(0, count($find) - 1, ' ');

        return str_replace($find, $replace, $str);
    }
}