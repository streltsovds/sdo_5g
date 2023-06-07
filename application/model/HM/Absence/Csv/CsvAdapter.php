<?php
class HM_Absence_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 0;

    public function getMappingArray()
    {
        return array(
            0  => 'user_external_id',
            1  => 'type',
            2  => 'absence_begin',
            3  => 'absence_end'
        );
    }

}