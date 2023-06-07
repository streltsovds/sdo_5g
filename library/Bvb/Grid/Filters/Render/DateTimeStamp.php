<?php
class Bvb_Grid_Filters_Render_DateTimeStamp extends Bvb_Grid_Filters_Render_Date
{
    public function transform($date, $key)
    {
        $date = urldecode($date);
        $date = str_replace('-', '.', $date);

        $dateObject = new Zend_Date($date);
        if ($key == 'to') {
            $dateObject->addHour(23)
                       ->addMinute(59)
                       ->addSecond(59);
        }
        return $dateObject->getTimestamp();
    }
}