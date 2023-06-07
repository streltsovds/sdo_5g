<?php

class HM_Holiday_HolidayService extends HM_Service_Abstract
{
    const CACHE_NAME = 'HM_Holiday_HolidayService';
    private $_holidaysCache = array();
    
    public function createForYear($weekdays)
    {
        $date = Zend_Date::now()->getDate();
        $to = clone $date;
        $to->add(1, Zend_Date::YEAR);

        // Преобразовали дни недели в цифровое значение
        $weekdays = array_intersect(array_keys(HM_Date::getWeekdays()), $weekdays);
        while($date < $to) {
            
            $weekday = $date->get('e');
            $weekday = $weekday ? $weekday - 1 : 6;

            if (array_key_exists($weekday, $weekdays)) {
                $this->getService('Holiday')->insert([
                    'type' => HM_Holiday_HolidayModel::TYPE_PERIODIC,
                    'title' => $date->get('EEEE'),
                    'date' => $date->get('Y-M-d'),
                ]);
            }

            $date->add(1, Zend_Date::DAY);
        }
    }
    
    public function getHolidays()
    {
        $holidays = array();
        foreach($this->fetchAll() as $row) {
            list($date, $time) = explode(' ', $row->date);
            $holidays[] = $date;
        }
        return $holidays;
    }
}