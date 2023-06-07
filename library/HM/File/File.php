<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 3/20/19
 * Time: 12:33 PM
 */

class HM_File_File
{
    public function detectFileEncoding($filename)
    {
        if ($content = file_get_contents($filename)) {
            $content = iconv(HM_String_Encoding::detectEncoding($content), Zend_Registry::get('config')->charset, $content);
            if (strlen($content)) {
                if (strtolower(Zend_Registry::get('config')->charset) == 'utf-8') {
                    $content = preg_replace('/\xEF\xBB\xBF/', '', $content);
                }
                if (is_writeable($filename)) {
                    file_put_contents($filename, $content);
                }
            }
        }
    }
}