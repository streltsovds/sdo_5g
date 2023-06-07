<?php
/**
 * Хелпер для отображения стажа по дате назначения на должность
 * 
 * @author Sevastyanov Cyril
 * @since 2013-01-30
 */
class HM_View_Helper_Experience extends HM_View_Helper_Abstract
{
   /**
    * Формат можно посмотреть тут: http://www.php.net/manual/ru/dateinterval.format.php 
    * 
    * @param int $date
    * @param string $format
    */
   function experience($date, $format = '%y г. %m мес.')
   {
       $start = new DateTime($date);
       $end   = new DateTime();
       $interval = $end->diff($start);
       
       $this->view->experience = $interval->format($format);
       
       return $this->view->render('experience.tpl');
   }
}