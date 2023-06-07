<?php
class HM_Unicode {
    /**
     * Встроенный в php
     * @see basename()
     * вырезает русскоязычные буквы из имени файла! см. #36703/2
     *
     * https://prisma-cms.com/topics/php-basename-.-problema-s-kirilliczej.html
     */
    public static function basename($param, $suffix=null, $charset='utf-8')
    {
        $param = str_replace('\\', '/', $param);
        $dirSeparator = '/';

        if ($suffix) {
            $tmpstr = ltrim(mb_substr($param, mb_strrpos($param, $dirSeparator, null, $charset), null, $charset), $dirSeparator);
            if ((mb_strpos($param, $suffix, null, $charset) + mb_strlen($suffix, $charset)) == mb_strlen($param, $charset)) {
                return str_ireplace($suffix, '', $tmpstr);
            } else {
                return ltrim(mb_substr($param, mb_strrpos($param, $dirSeparator, null, $charset), null, $charset), $dirSeparator);
            }
        } else {
            return ltrim(mb_substr($param, mb_strrpos($param, $dirSeparator, null, $charset), null, $charset), $dirSeparator);
        }
    }
}
