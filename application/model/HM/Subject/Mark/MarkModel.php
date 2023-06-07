<?php
class HM_Subject_Mark_MarkModel extends HM_Model_Abstract
{
    const MARK_PATTERN = "^[1-9]{1}\d?$|^0$|^100$";

    const MARK_NOT_CONFIRMED = 0;
    const MARK_CONFIRMED = 1;

    const MARK_GOOD = 'сдал';
    const MARK_BAD  = 'не сдал';

    protected $_primaryName = 'cid';

    static public function filterMark($mark)
    {
         if((!preg_match("/".self::MARK_PATTERN."/",$mark)&&$mark>0) || $mark<0 || empty($mark)){
              return $mark === 0 || $mark === "0" ? 0 : -1; // #11421
         }
         return $mark;
    }
}
