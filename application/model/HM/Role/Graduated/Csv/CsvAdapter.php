<?php
class HM_Role_Graduated_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1;

    public function getMappingArray()
    {
        return array(
            0 => 'department', // Наименование структурного подразделения
            1 => 'division', // Обособленное подразделение
            2 => 'position', // Наименование профессии (должности) в соответствии со штатным расписанием
            3 => 'user_fio', // ФИО
            4 => 'test_date', // Дата проверки знаний
            5 => 'period', // Срок действия (кол-во месяцев)
            6 => 'subject_code', // Номер курса

        );

    }


    public static function getOrgName()
    {

    }

    public function getCallbacks()
    {

    }


}