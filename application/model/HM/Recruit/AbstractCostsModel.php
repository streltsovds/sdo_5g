<?php
class HM_Recruit_AbstractCostsModel extends HM_Model_Abstract
{
    const MONTHS_BEFORE = 13;
    const MONTHS_AFTER  = 12;
   
    static public function getMonths(){
        return array(
            1  => _('январь'),
            2  => _('февраль'),
            3  => _('март'),
            4  => _('апрель'),
            5  => _('май'),
            6  => _('июнь'),
            7  => _('июль'),
            8  => _('август'),
            9  => _('сентябрь'),
            10 => _('октябрь'),
            11 => _('ноябрь'),
            12 => _('декабрь'),
        );
    }
    
    static public function getPeriods(){
        $months = self::getMonths();
        
        $date = new DateTime();
        $date->modify('-'.self::MONTHS_BEFORE.' month');
        $startMonth = $date->format('n');
        $startYear = $date->format('Y');
        
        $periods = array();
        
        for($i = $startMonth; $i <= $startMonth + self::MONTHS_BEFORE + self::MONTHS_AFTER; $i++){
            $monthNumber = ($i - 1) % 12 + 1;
            $year = $startYear + floor($i/12);
            $periods[$monthNumber . '_' . $year] = $months[$monthNumber] . ' ' . $year;
        }
        
        return $periods;
    }
    
    static public function getPeriodsFromTo($fromYear, $toYear = null){
        if($toYear == null){
            $date = new DateTime();
            $toYear  = $date->format('Y');
        }
        
        $months = self::getMonths();
        
        $periods = array();
        if($fromYear){
            for($year = $fromYear; $year <= $toYear; $year++){
                foreach($months as $monthNumber => $monthName) {
                    $periods[$monthNumber . '_' . $year] = $monthName . ' ' . $year;
                }
            }   
        }
        
        return $periods;        
    }
    
    
    
}