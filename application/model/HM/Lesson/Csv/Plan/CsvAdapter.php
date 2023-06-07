<?php
class HM_Lesson_Csv_Plan_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1;

    public function getMappingArray()
    {
        return [
            0 => 'order',
            1 => 'title',
            2 => 'begin',
            3 => 'end'
        ];
    }

    public function getCallbacks()
    {
    }
}